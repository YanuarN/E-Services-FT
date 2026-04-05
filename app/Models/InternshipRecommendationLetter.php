<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class InternshipRecommendationLetter extends Model
{
    use HasPublicToken;

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
