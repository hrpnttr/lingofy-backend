<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $table = 'courses';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'level',
        'language',
        'price',
        'durationHours',
        'tags',
        'published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'double',
        'durationHours' => 'integer',
        'published' => 'boolean',
        'tags' => 'array',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
}
