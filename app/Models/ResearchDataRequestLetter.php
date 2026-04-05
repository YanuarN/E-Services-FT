<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class ResearchDataRequestLetter extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'status',
        'student_name',
        'nim',
        'study_program',
        'phone_number',
        'company_name',
        'company_address',
        'letter_date',
        'letter_number',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];
}
