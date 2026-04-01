<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterOfAssignment extends Model
{
    protected $fillable = [
        'status',
        'date',
        'time',
        'place',
        'student_list',
        'letter_number',
        'letter_date',
        'number',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'date' => 'date',
        'letter_date' => 'date',
        'student_list' => 'array',
    ];
}
