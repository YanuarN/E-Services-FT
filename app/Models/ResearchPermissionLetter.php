<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchPermissionLetter extends Model
{
    protected $table = 'research_permisson_letter';

    protected $fillable = [
        'status',
        'student_name',
        'nim',
        'study_program',
        'phone_number',
        'company_name',
        'company_address',
        'public_token',
        'letter_number',
        'letter_date',
        'pdf_path',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];
}
