<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterOfAssignmentIndividual extends Model
{
    protected $fillable = [
        'status',
        'name',
        'nim',
        'departement',
        'faculty',
        'address',
        'assignment',
        'place',
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
