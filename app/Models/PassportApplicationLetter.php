<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class PassportApplicationLetter extends Model
{
    use HasPublicToken;

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
