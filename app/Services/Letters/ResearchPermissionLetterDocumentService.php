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
        $members = $this->normalizePeople($letter->group_member ?? []);

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
                'anggota_kelompok' => $this->buildPeopleSummary($members),
            ],
        );
    }

    protected function buildRowCollections(Model $letter): array
    {
        /** @var ResearchPermissionLetter $letter */
        return [
            $this->buildMemberRowCollection($this->normalizePeople($letter->group_member ?? [])),
        ];
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var ResearchPermissionLetter $letter */
        return ['surat-izin-penelitian', $letter->student_name, $letter->nim];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var ResearchPermissionLetter $letter */
        $members = $this->normalizePeople($letter->group_member ?? []);

        return [
            $this->makeVerificationField('Nama Mahasiswa', $letter->student_name),
            $this->makeVerificationField('NIM', $letter->nim),
            $this->makeVerificationField('Program Studi', $letter->study_program),
            $this->makeVerificationField('Nomor Telepon', $letter->phone_number),
            $this->makeVerificationField('Nama Instansi', $letter->company_name),
            $this->makeVerificationField('Alamat Instansi', $letter->company_address),
            $this->makeVerificationField('Anggota Kelompok', $this->buildPeopleSummary($members)),
        ];
    }
}
