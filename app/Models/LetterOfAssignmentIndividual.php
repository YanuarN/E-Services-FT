<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class LetterOfAssignmentIndividual extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'status',
        'name',
        'nim',
        'phone_number',
        'departement',
        'faculty',
        'address',
        'assignment',
        'place',
        'date',
        'letter_number',
        'letter_date',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'date' => 'date',
        'letter_date' => 'date',
    ];
}
