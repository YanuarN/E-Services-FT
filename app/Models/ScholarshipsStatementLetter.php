<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class ScholarshipsStatementLetter extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'status',
        'student_name',
        'study_program',
        'nim',
        'scolarship_name',
        'scolarship_provider',
        'phone_number',
        'letter_number',
        'letter_date',
        'public_token',
        'pdf_path',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];
}
