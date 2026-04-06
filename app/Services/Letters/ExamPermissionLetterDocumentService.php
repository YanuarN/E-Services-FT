<?php

namespace App\Services\Letters;

use App\Models\ExamPermissionLetter;
use Illuminate\Database\Eloquent\Model;

class ExamPermissionLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'exam_permission';
    }

    protected function modelClass(): string
    {
        return ExamPermissionLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var ExamPermissionLetter $letter */
        $examDate = $this->formatDate($letter->date);

        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload($letter->name, $letter->nim),
            [
                'ujian' => $letter->exam,
                'semester' => $letter->semester,
                'tanggal_ujian' => $examDate,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var ExamPermissionLetter $letter */
        return ['surat-izin-ujian', $letter->name, $letter->nim];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var ExamPermissionLetter $letter */
        return [
            $this->makeVerificationField('Nama Mahasiswa', $letter->name),
            $this->makeVerificationField('NIM', $letter->nim),
            $this->makeVerificationField('Ujian', $letter->exam),
            $this->makeVerificationField('Semester', $letter->semester),
            $this->makeVerificationField('Tanggal Ujian', $this->formatDate($letter->date)),
        ];
    }
}
