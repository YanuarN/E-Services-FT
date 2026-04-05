<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class InternshipLetter extends Model
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
        'group_member',
        'letter_date',
        'letter_number',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'group_member' => 'array',
        'letter_date' => 'date',
    ];
}
