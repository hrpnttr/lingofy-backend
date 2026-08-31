<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
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
