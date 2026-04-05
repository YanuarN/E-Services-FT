<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipRecommendationLetter extends Model
{
    protected $fillable = [
        'status',
        'student_name',
        'nim',
        'study_program',
        'semester',
        'ipk',
        'program_name',
        'phone_number',
        'letter_date',
        'letter_number',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];
}
