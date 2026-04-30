<?php

namespace App\Services\Letters;

use App\Models\LetterOfAssignment;
use Illuminate\Database\Eloquent\Model;

class LetterOfAssignmentDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'letter_of_assignment';
    }

    protected function modelClass(): string
    {
        return LetterOfAssignment::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var LetterOfAssignment $letter */
        $students = $this->normalizePeople($letter->student_list ?? []);

        return array_merge(
            $this->baseLetterPayload($letter),
            [
                'activity' => (string) ($letter->activity ?? ''),
                'kegiatan' => (string) ($letter->activity ?? ''),
                'assigment' => (string) ($letter->assigment ?? ''),
                'sebagai' => (string) ($letter->assigment ?? ''),
                'penugasan' => (string) ($letter->assigment ?? ''),
                'tanggal_kegiatan' => (string) ($letter->date ?? ''),
                'waktu' => (string) ($letter->time ?? ''),
                'tempat' => $letter->place,
                'daftar_mahasiswa' => $this->buildPeopleSummary($students),
            ],
        );
    }

    protected function buildRowCollections(Model $letter): array
    {
        /** @var LetterOfAssignment $letter */
        $rows = $this->buildMemberRows($this->normalizePeople($letter->student_list ?? []));

        return [
            $this->buildRowCollection(
                ['mahasiswa_no', 'anggota_no', 'm_no', 'm_nama', 'm_nim', 'm_prodi'],
                $rows,
                [
                    'mahasiswa_no',
                    'anggota_no',
                    'm_no',
                    'nama_mahasiswa',
                    'm_nama',
                    'mahasiswa_nim',
                    'nim',
                    'm_nim',
                    'mahasiswa_prodi',
                    'anggota_prodi',
                    'program_studi',
                    'prodi',
                    'm_prodi',
                ],
            ),
        ];
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var LetterOfAssignment $letter */
        return ['surat-tugas-kelompok', $letter->id, $letter->letter_number ?: 'draft'];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var LetterOfAssignment $letter */
        $students = $this->normalizePeople($letter->student_list ?? []);

        return [
            $this->makeVerificationField('Kegiatan', $letter->activity),
            $this->makeVerificationField('Sebagai', $letter->assigment),
            $this->makeVerificationField('Tanggal Kegiatan', $letter->date),
            $this->makeVerificationField('Waktu', $letter->time),
            $this->makeVerificationField('Tempat', $letter->place),
            $this->makeVerificationField('Daftar Mahasiswa', $this->buildPeopleSummary($students)),
        ];
    }
}
