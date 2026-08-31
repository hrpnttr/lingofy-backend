<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizzResult extends Model
{
    protected $table = 'quizz_results';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'studentId',
        'quiz_id',
        'answers',
        'score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'answers' => 'array',
        'score' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
}
