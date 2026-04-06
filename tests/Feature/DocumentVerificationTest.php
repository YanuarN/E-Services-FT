<?php

namespace Tests\Feature;

use App\Models\ExamPermissionLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\LetterTemplate;
use App\Services\Letters\DocumentVerificationService;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_letter_model_generates_public_token_automatically(): void
    {
        $letter = ExamPermissionLetter::query()->create([
            'status' => 'APPROVE',
            'name' => 'Budi Santoso',
            'nim' => '221234567',
            'exam' => 'Seminar Proposal',
            'semester' => '8',
            'date' => '2026-04-10',
            'letter_number' => 'FT/001/IV/2026',
            'letter_date' => '2026-04-09',
            'pdf_path' => 'generated-letters/exam/test.pdf',
        ]);

        $this->assertNotEmpty($letter->public_token);
        $this->assertSame(16, strlen($letter->public_token));
    }

    public function test_document_verification_page_resolves_letter_by_type_and_token(): void
    {
        $letter = ResearchPermissionLetter::query()->create([
            'status' => 'APPROVE',
            'student_name' => 'Siti Rahma',
            'nim' => '221998877',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'company_name' => 'Bappeda Kota Samarinda',
            'company_address' => 'Jl. Kusuma Bangsa No. 10',
            'letter_number' => 'FT/045/IV/2026',
            'letter_date' => '2026-04-04',
            'pdf_path' => 'generated-letters/research_permission/test.pdf',
            'public_token' => 'VERIFYTOKEN12345',
        ]);

        $response = $this->get(route('verification.show', [
            'letterType' => 'research_permission',
            'token' => $letter->public_token,
        ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('DocumentVerification')
            ->where('title', LetterTemplate::LETTER_TYPES['research_permission'])
            ->where('status', 'APPROVE')
            ->where('fields.0.label', 'Nomor Surat')
            ->where('fields.0.value', 'FT/045/IV/2026')
            ->etc());
    }

    public function test_document_verification_returns_not_found_for_invalid_token(): void
    {
        $response = $this->get(route('verification.show', [
            'letterType' => 'exam_permission',
            'token' => 'UNKNOWN123456789',
        ]));

        $response->assertNotFound();
    }

    public function test_qr_code_service_generates_png_file_from_verification_url(): void
    {
        $letter = ExamPermissionLetter::query()->create([
            'status' => 'APPROVE',
            'name' => 'Nadia Putri',
            'nim' => '221223344',
            'exam' => 'Sidang KP',
            'semester' => '6',
            'date' => '2026-04-12',
            'letter_number' => 'FT/090/IV/2026',
            'letter_date' => '2026-04-11',
            'pdf_path' => 'generated-letters/exam/test.pdf',
        ]);

        $verificationUrl = app(DocumentVerificationService::class)
            ->buildVerificationUrl('exam_permission', $letter);

        $path = app(QrCodeService::class)->generateLetterQrCode($verificationUrl);

        $this->assertFileExists($path);
        $this->assertSame('png', strtolower(pathinfo($path, PATHINFO_EXTENSION)));

        @unlink($path);
    }
}
