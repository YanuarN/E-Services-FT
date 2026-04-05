<?php

namespace App\Services\Letters;

use App\Models\InternshipLetter;
use Illuminate\Database\Eloquent\Model;

class InternshipLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'internship';
    }

    protected function modelClass(): string
    {
        return InternshipLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var InternshipLetter $letter */
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
                'nama_perusahaan' => $letter->company_name,
                'alamat_perusahaan' => $letter->company_address,
                'anggota_kelompok' => $this->buildPeopleSummary($members),
            ],
        );
    }

    protected function buildRowCollections(Model $letter): array
    {
        /** @var InternshipLetter $letter */
        $rows = $this->buildMemberRows($this->normalizePeople($letter->group_member ?? []));

        return [
            $this->buildRowCollection(
                ['anggota_no', 'mahasiswa_no'],
                $rows,
                [
                    'anggota_no',
                    'mahasiswa_no',
                    'nama_mahasiswa',
                    'anggota_nim',
                    'mahasiswa_nim',
                    'nim',
                    'anggota_prodi',
                    'mahasiswa_prodi',
                    'program_studi',
                    'prodi',
                ],
            ),
        ];
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var InternshipLetter $letter */
        return ['surat-pkn', $letter->student_name, $letter->nim];
    }
}
