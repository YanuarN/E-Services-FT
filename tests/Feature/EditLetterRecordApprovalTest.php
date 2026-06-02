<?php

namespace Tests\Feature;

use App\Filament\Resources\ResearchPermissionLetters\Pages\EditResearchPermissionLetter;
use App\Models\LetterTemplate;
use App\Models\ResearchPermissionLetter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditLetterRecordApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_from_edit_save_generates_pdf_and_redirects_to_whatsapp(): void
    {
        Storage::fake('local');

        config([
            'services.pdf_converter.driver' => 'http',
            'services.pdf_converter.url' => 'https://converter.test/convert',
        ]);

        Http::fake([
            'https://converter.test/*' => Http::response('%PDF-1.4 fake', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        $this->createTemplate('letter-templates/research-permission.docx');

        LetterTemplate::query()->create([
            'letter_type' => 'research_permission',
            'document_path' => 'letter-templates/research-permission.docx',
        ]);

        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]));

        $record = ResearchPermissionLetter::query()->create([
            'student_name' => 'Siti Rahma',
            'nim' => '221234567',
            'study_program' => 'Teknik Sipil',
            'phone_number' => '081234567890',
            'company_name' => 'PT Riset Nusantara',
            'company_address' => 'Jl. Penelitian No. 1',
            'status' => 'SUBMITTED',
        ]);

        $this->actingAs($user);

        Livewire::test(EditResearchPermissionLetter::class, ['record' => $record->getKey()])
            ->fillForm([
                'student_name' => 'Siti Rahma',
                'nim' => '221234567',
                'study_program' => 'Teknik Sipil',
                'phone_number' => '081234567890',
                'company_name' => 'PT Riset Nusantara',
                'company_address' => 'Jl. Penelitian No. 1',
                'group_member' => [],
                'status' => 'APPROVE',
                'letter_number' => 'FT/001/VI/2026',
                'letter_date' => '2026-06-02',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirectContains('https://wa.me/6281234567890');

        Http::assertSentCount(1);

        $record->refresh();

        $this->assertSame('APPROVE', $record->status);
        $this->assertNotEmpty($record->pdf_path);
        Storage::disk('local')->assertExists($record->pdf_path);
    }

    private function createTemplate(string $path): void
    {
        $fullPath = Storage::disk('local')->path($path);
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('${nama_mahasiswa}');
        $section->addText('${nomor_surat}');
        $section->addText('${verification_url}');

        IOFactory::createWriter($phpWord, 'Word2007')->save($fullPath);
    }
}
