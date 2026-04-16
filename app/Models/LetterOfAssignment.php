<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class LetterOfAssignment extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'status',
        'date',
        'time',
        'place',
        'student_list',
        'letter_number',
        'letter_date',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'student_list' => 'array',
    ];
}
