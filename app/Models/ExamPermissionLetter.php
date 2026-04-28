<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class ExamPermissionLetter extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'status',
        'name',
        'nim',
        'company_name',
        'company_address',
        'group_member',
        'exam',
        'semester',
        'date',
        'letter_number',
        'letter_date',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'group_member' => 'array',
        'date' => 'date',
        'letter_date' => 'date',
    ];
}
