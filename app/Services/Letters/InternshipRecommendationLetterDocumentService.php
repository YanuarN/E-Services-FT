<?php

namespace App\Services\Letters;

use App\Models\InternshipRecommendationLetter;
use Illuminate\Database\Eloquent\Model;

class InternshipRecommendationLetterDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'internship_recommendation';
    }

    protected function modelClass(): string
    {
        return InternshipRecommendationLetter::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var InternshipRecommendationLetter $letter */
        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload(
                $letter->student_name,
                $letter->nim,
                $letter->study_program,
                $letter->phone_number,
            ),
            [
                'semester' => $letter->semester,
                'ipk' => (string) $letter->ipk,
                'nama_program' => $letter->program_name,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var InternshipRecommendationLetter $letter */
        return ['surat-rekomendasi-magang', $letter->student_name, $letter->nim];
    }
}
