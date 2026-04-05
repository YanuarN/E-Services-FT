<?php

namespace App\Services\Letters;

use App\Models\ScholarshipsStatementLetter;
use Illuminate\Database\Eloquent\Model;

class ScholarshipsStatementLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'scholarships_statement';
    }

    protected function modelClass(): string
    {
        return ScholarshipsStatementLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var ScholarshipsStatementLetter $letter */
        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload(
                $letter->student_name,
                $letter->nim,
                $letter->study_program,
                $letter->phone_number,
            ),
            [
                'nama_beasiswa' => $letter->scolarship_name,
                'penyedia_beasiswa' => $letter->scolarship_provider,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var ScholarshipsStatementLetter $letter */
        return ['surat-keterangan-beasiswa', $letter->student_name, $letter->nim];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var ScholarshipsStatementLetter $letter */
        return [
            $this->makeVerificationField('Nama Mahasiswa', $letter->student_name),
            $this->makeVerificationField('NIM', $letter->nim),
            $this->makeVerificationField('Program Studi', $letter->study_program),
            $this->makeVerificationField('Nomor Telepon', $letter->phone_number),
            $this->makeVerificationField('Nama Beasiswa', $letter->scolarship_name),
            $this->makeVerificationField('Penyedia Beasiswa', $letter->scolarship_provider),
        ];
    }
}
