<?php

namespace App\Services\Letters;

use App\Models\PassportApplicationLetter;
use Illuminate\Database\Eloquent\Model;

class PassportApplicationLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'passport_application';
    }

    protected function modelClass(): string
    {
        return PassportApplicationLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var PassportApplicationLetter $letter */
        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload(
                $letter->student_name,
                $letter->nim,
                $letter->study_program,
                $letter->phone_number,
            ),
            [
                'nama_kegiatan' => $letter->event_name,
                'kegiatan' => $letter->event_name,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var PassportApplicationLetter $letter */
        return ['surat-pengantar-paspor', $letter->student_name, $letter->nim];
    }
}
