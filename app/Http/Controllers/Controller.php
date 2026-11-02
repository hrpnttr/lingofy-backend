<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Controller extends BaseController
{
    /**
     * Resolve the public tenant context. The null fallback only applies before
     * the tenant migration is installed, preserving the legacy single-tenant app.
     */
    protected function tenantId(Request $request): ?int
    {
        if (!Schema::hasTable('tenants') || !Schema::hasColumn('students', 'tenant_id')) {
            return null;
        }

        $slug = strtolower(trim((string) $request->header('X-Tenant-Slug', 'lingofy')));
        $tenantId = DB::table('tenants')->where('slug', $slug)->where('is_active', true)->value('id');
        if (!$tenantId) {
            abort(404, 'Organization not found.');
        }

        return (int) $tenantId;
    }

    /**
     * Format paginated output to match Spring Boot Page JSON format.
     */
    protected function formatPage($items, $total, $page, $size)
    {
        $totalPages = (int) ceil($total / $size);
        $numberOfElements = count($items);

        return [
            'content' => $items,
            'totalPages' => $totalPages,
            'totalElements' => $total,
            'number' => $page,
            'size' => $size,
            'numberOfElements' => $numberOfElements,
            'first' => ($page === 0),
            'last' => ($page >= $totalPages - 1),
            'empty' => ($numberOfElements === 0),
        ];
    }
}
