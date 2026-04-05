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
        $assignmentDate = $this->formatDate($letter->date);

        return array_merge(
            $this->baseLetterPayload($letter),
            [
                'nomor_permohonan' => $letter->number,
                'tanggal_kegiatan' => $assignmentDate,
                'waktu' => $this->formatTime($letter->time),
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
                ['mahasiswa_no', 'anggota_no'],
                $rows,
                [
                    'mahasiswa_no',
                    'anggota_no',
                    'nama_mahasiswa',
                    'mahasiswa_nim',
                    'nim',
                    'mahasiswa_prodi',
                    'anggota_prodi',
                    'program_studi',
                    'prodi',
                ],
            ),
        ];
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var LetterOfAssignment $letter */
        return ['surat-tugas-kelompok', $letter->id, $letter->number];
    }
}
