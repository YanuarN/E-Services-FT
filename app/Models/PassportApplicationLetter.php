<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassportApplicationLetter extends Model
{
    protected $fillable = [
        'status',
        'student_name',
        'study_program',
        'nim',
        'phone_number',
        'event_name',
        'letter_number',
        'letter_date',
        'public_token',
        'pdf_path',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];
}
