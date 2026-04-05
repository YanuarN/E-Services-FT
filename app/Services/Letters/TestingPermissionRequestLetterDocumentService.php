<?php

namespace App\Services\Letters;

use App\Models\TestingPermissionRequestLetter;
use Illuminate\Database\Eloquent\Model;

class TestingPermissionRequestLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'testing_permission_request';
    }

    protected function modelClass(): string
    {
        return TestingPermissionRequestLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var TestingPermissionRequestLetter $letter */
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
        /** @var TestingPermissionRequestLetter $letter */
        return ['surat-izin-pengujian-alat', $letter->student_name, $letter->nim];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var TestingPermissionRequestLetter $letter */
        return [
            $this->makeVerificationField('Nama Mahasiswa', $letter->student_name),
            $this->makeVerificationField('NIM', $letter->nim),
            $this->makeVerificationField('Program Studi', $letter->study_program),
            $this->makeVerificationField('Nomor Telepon', $letter->phone_number),
            $this->makeVerificationField('Nama Instansi', $letter->company_name),
            $this->makeVerificationField('Alamat Instansi', $letter->company_address),
        ];
    }
}
