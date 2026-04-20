<?php

namespace App\Services\Letters;

use App\Models\ExamPermissionLetter;
use App\Models\InternshipLetter;
use App\Models\InternshipRecommendationLetter;
use App\Models\LetterOfAssignment;
use App\Models\LetterOfAssignmentIndividual;
use App\Models\LetterTemplate;
use App\Models\PassportApplicationLetter;
use App\Models\ResearchDataRequestLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\RoomUsageRequest;
use App\Models\ScholarshipsStatementLetter;
use App\Models\TestingPermissionRequestLetter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentVerificationService
{
    /**
     * @return array<string, array{model: class-string<Model>, service: class-string<UniversalLetterService>}>
     */
    public function definitions(): array
    {
        return [
            'exam_permission' => [
                'model' => ExamPermissionLetter::class,
                'service' => ExamPermissionLetterDocumentService::class,
            ],
            'internship' => [
                'model' => InternshipLetter::class,
                'service' => InternshipLetterDocumentService::class,
            ],
            'internship_recommendation' => [
                'model' => InternshipRecommendationLetter::class,
                'service' => InternshipRecommendationLetterDocumentService::class,
            ],
            'letter_of_assignment' => [
                'model' => LetterOfAssignment::class,
                'service' => LetterOfAssignmentDocumentService::class,
            ],
            'letter_of_assignment_individual' => [
                'model' => LetterOfAssignmentIndividual::class,
                'service' => LetterOfAssignmentIndividualDocumentService::class,
            ],
            'passport_application' => [
                'model' => PassportApplicationLetter::class,
                'service' => PassportApplicationLetterDocumentService::class,
            ],
            'research_data_request' => [
                'model' => ResearchDataRequestLetter::class,
                'service' => ResearchDataRequestLetterDocumentService::class,
            ],
            'research_permission' => [
                'model' => ResearchPermissionLetter::class,
                'service' => ResearchPermissionLetterDocumentService::class,
            ],
            'room_usage_request' => [
                'model' => RoomUsageRequest::class,
                'service' => RoomUsageRequestDocumentService::class,
            ],
            'scholarships_statement' => [
                'model' => ScholarshipsStatementLetter::class,
                'service' => ScholarshipsStatementLetterDocumentService::class,
            ],
            'testing_permission_request' => [
                'model' => TestingPermissionRequestLetter::class,
                'service' => TestingPermissionRequestLetterDocumentService::class,
            ],
        ];
    }

    public function buildVerificationUrl(string $letterType, Model $letter): string
    {
        $definition = $this->definition($letterType);

        if (! $letter instanceof $definition['model']) {
            throw new RuntimeException("Model surat tidak cocok untuk tipe '{$letterType}'.");
        }

        $token = $this->ensurePublicToken($letter);

        return route('verification.show', [
            'letterType' => $letterType,
            'token' => $token,
        ]);
    }

    /**
     * @return array{letter: Model, service: UniversalLetterService}|null
     */
    public function resolve(string $letterType, string $token): ?array
    {
        $definition = $this->definitions()[$letterType] ?? null;

        if (! $definition) {
            return null;
        }

        /** @var Model|null $letter */
        $letter = $definition['model']::query()
            ->where('public_token', $token)
            ->first();

        if (! $letter) {
            return null;
        }

        return [
            'letter' => $letter,
            'service' => app($definition['service']),
        ];
    }

    public function letterLabel(string $letterType): string
    {
        return LetterTemplate::LETTER_TYPES[$letterType]
            ?? Str::headline(str_replace('_', ' ', $letterType));
    }

    public function ensurePublicToken(Model $letter): string
    {
        $existingToken = (string) $letter->getAttribute('public_token');

        if ($existingToken !== '') {
            return $existingToken;
        }

        $token = method_exists($letter, 'generateUniquePublicToken')
            ? $letter::generateUniquePublicToken()
            : Str::upper(Str::random(16));

        $letter->forceFill([
            'public_token' => $token,
        ])->save();

        return $token;
    }

    /**
     * @return array{letter_type: string, model: class-string<Model>, service: class-string<UniversalLetterService>}
     */
    public function definitionForLetter(Model $letter): array
    {
        foreach ($this->definitions() as $letterType => $definition) {
            if ($letter instanceof $definition['model']) {
                return [
                    'letter_type' => $letterType,
                    ...$definition,
                ];
            }
        }

        throw new RuntimeException(sprintf(
            'Model surat %s belum terdaftar untuk workflow dokumen.',
            $letter::class,
        ));
    }

    /**
     * @return array{model: class-string<Model>, service: class-string<UniversalLetterService>}
     */
    private function definition(string $letterType): array
    {
        return $this->definitions()[$letterType] ?? throw new RuntimeException(
            "Tipe surat '{$letterType}' belum terdaftar untuk verifikasi dokumen."
        );
    }
}
