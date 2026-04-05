<?php

namespace App\Services\Letters;

use App\Models\ResearchPermissionLetter;
use Illuminate\Database\Eloquent\Model;

class ResearchPermissionLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'research_permission';
    }

    protected function modelClass(): string
    {
        return ResearchPermissionLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var ResearchPermissionLetter $letter */
        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload(
                $letter->student_name,
                $letter->nim,
                $letter->study_program,
                $letter->phone_number,
            ),
            [
                'nama_instansi' => $letter->company_name,
                'alamat_instansi' => $letter->company_address,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var ResearchPermissionLetter $letter */
        return ['surat-izin-penelitian', $letter->student_name, $letter->nim];
    }
}
