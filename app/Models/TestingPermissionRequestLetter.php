<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestingPermissionRequestLetter extends Model
{
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
