<?php

namespace App\Services\Letters;

use App\Models\LetterOfAssignmentIndividual;
use Illuminate\Database\Eloquent\Model;

class LetterOfAssignmentIndividualDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'letter_of_assignment_individual';
    }

    protected function modelClass(): string
    {
        return LetterOfAssignmentIndividual::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var LetterOfAssignmentIndividual $letter */
        $assignmentDate = $this->formatDate($letter->date);

        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload($letter->name, $letter->nim),
            [
                'nomor_permohonan' => $letter->number,
                'departement' => $letter->departement,
                'fakultas' => $letter->faculty,
                'alamat' => $letter->address,
                'penugasan' => $letter->assignment,
                'tempat' => $letter->place,
                'tanggal_kegiatan' => $assignmentDate,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var LetterOfAssignmentIndividual $letter */
        return ['surat-tugas-individual', $letter->name, $letter->nim];
    }
}
