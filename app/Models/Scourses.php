<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scourses extends Model
{
    protected $table = 'scourses';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'studentId',
        'contents',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'contents' => 'array',
        'createdAt' => 'datetime',
    ];

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;
}
