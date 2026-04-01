<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipLetter extends Model
{
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
        'public_token',
    ];

    protected $casts = [
        'group_member' => 'array',
        'letter_date' => 'date',
    ];
}
