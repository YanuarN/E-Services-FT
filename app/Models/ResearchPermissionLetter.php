<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class ResearchPermissionLetter extends Model
{
    use HasPublicToken;

    protected $table = 'research_permisson_letter';

    protected $fillable = [
        'status',
        'student_name',
        'nim',
        'study_program',
        'phone_number',
        'company_name',
        'company_address',
        'group_member',
        'public_token',
        'letter_number',
        'letter_date',
        'pdf_path',
    ];

    protected $casts = [
        'group_member' => 'array',
        'letter_date' => 'date',
    ];
}
