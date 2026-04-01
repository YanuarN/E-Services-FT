<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPermissionLetter extends Model
{
    protected $fillable = [
        'status',
        'name',
        'nim',
        'exam',
        'semester',
        'date',
        'letter_number',
        'letter_date',
        'number',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'date' => 'date',
        'letter_date' => 'date',
    ];
}
