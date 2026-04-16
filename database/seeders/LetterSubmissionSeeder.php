<?php

namespace Database\Seeders;

use App\Models\ExamPermissionLetter;
use App\Models\InternshipLetter;
use App\Models\InternshipRecommendationLetter;
use App\Models\LetterOfAssignment;
use App\Models\LetterOfAssignmentIndividual;
use App\Models\PassportApplicationLetter;
use App\Models\ResearchDataRequestLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\ScholarshipsStatementLetter;
use App\Models\TestingPermissionRequestLetter;
use Illuminate\Database\Seeder;

class LetterSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedExamPermissionLetters();
        $this->seedInternshipLetters();
        $this->seedInternshipRecommendationLetters();
        $this->seedLetterOfAssignments();
        $this->seedLetterOfAssignmentIndividuals();
        $this->seedPassportApplicationLetters();
        $this->seedResearchDataRequestLetters();
        $this->seedResearchPermissionLetters();
        $this->seedScholarshipsStatementLetters();
        $this->seedTestingPermissionRequestLetters();
    }

    private function seedExamPermissionLetters(): void
    {
        foreach (range(1, 8) as $index) {
            ExamPermissionLetter::query()->create([
                'status' => 'SUBMITTED',
                'name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'exam' => fake()->randomElement(['Seminar Proposal', 'Seminar Hasil', 'Sidang Skripsi', 'Sidang KP']),
                'semester' => (string) fake()->numberBetween(5, 14),
                'date' => fake()->dateTimeBetween('+3 days', '+30 days')->format('Y-m-d'),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedInternshipLetters(): void
    {
        foreach (range(1, 8) as $index) {
            InternshipLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'study_program' => $this->fakeStudyProgram(),
                'phone_number' => $this->fakePhoneNumber(),
                'company_name' => fake()->company(),
                'company_address' => fake('id_ID')->address(),
                'group_member' => [
                    [
                        'nama' => fake('id_ID')->name(),
                        'nim' => $this->fakeNim(),
                        'prodi' => $this->fakeStudyProgram(),
                    ],
                    [
                        'nama' => fake('id_ID')->name(),
                        'nim' => $this->fakeNim(),
                        'prodi' => $this->fakeStudyProgram(),
                    ],
                ],
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedInternshipRecommendationLetters(): void
    {
        foreach (range(1, 8) as $index) {
            InternshipRecommendationLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'study_program' => $this->fakeStudyProgram(),
                'semester' => (string) fake()->numberBetween(5, 10),
                'ipk' => fake()->randomFloat(2, 3.0, 4.0),
                'program_name' => fake()->randomElement(['MSIB', 'Kampus Mengajar', 'Magang Mandiri', 'Studi Independen']),
                'phone_number' => $this->fakePhoneNumber(),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedLetterOfAssignments(): void
    {
        foreach (range(1, 8) as $index) {
            LetterOfAssignment::query()->create([
                'status' => 'SUBMITTED',
                'date' => fake()->randomElement([
                    'Rabu-Jumat, 15-17 Oktober 2025',
                    'Senin-Selasa, 5-6 Mei 2025',
                    'Kamis, 24 Oktober 2025',
                ]),
                'time' => fake()->randomElement([
                    '08.00 WIB s.d. selesai',
                    '09.00 - 12.00 WIB',
                    '13.00 WIB - 15.30 WIB',
                ]),
                'place' => fake()->randomElement(['Ruang Seminar FT', 'Laboratorium Komputer', 'Aula Fakultas', 'Gedung Riset']),
                'student_list' => [
                    [
                        'nama' => fake('id_ID')->name(),
                        'nim' => $this->fakeNim(),
                        'program_studi' => $this->fakeStudyProgram(),
                    ],
                    [
                        'nama' => fake('id_ID')->name(),
                        'nim' => $this->fakeNim(),
                        'program_studi' => $this->fakeStudyProgram(),
                    ],
                    [
                        'nama' => fake('id_ID')->name(),
                        'nim' => $this->fakeNim(),
                        'program_studi' => $this->fakeStudyProgram(),
                    ],
                ],
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedLetterOfAssignmentIndividuals(): void
    {
        foreach (range(1, 8) as $index) {
            LetterOfAssignmentIndividual::query()->create([
                'status' => 'SUBMITTED',
                'name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'departement' => fake()->randomElement(['Teknik Informatika', 'Teknik Industri', 'Teknik Sipil']),
                'faculty' => 'Fakultas Teknik',
                'address' => fake('id_ID')->address(),
                'assignment' => fake('id_ID')->sentence(8),
                'place' => fake()->randomElement(['Dinas Kominfo', 'Bappeda', 'PT Teknologi Nusantara', 'Laboratorium FT']),
                'date' => fake()->dateTimeBetween('+3 days', '+25 days')->format('Y-m-d'),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedPassportApplicationLetters(): void
    {
        foreach (range(1, 8) as $index) {
            PassportApplicationLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'study_program' => $this->fakeStudyProgram(),
                'nim' => $this->fakeNim(),
                'phone_number' => $this->fakePhoneNumber(),
                'event_name' => fake()->randomElement(['Student Exchange Malaysia', 'Kompetisi Robotik Jepang', 'Konferensi Internasional', 'Short Course Singapore']),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedResearchDataRequestLetters(): void
    {
        foreach (range(1, 8) as $index) {
            ResearchDataRequestLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'study_program' => $this->fakeStudyProgram(),
                'phone_number' => $this->fakePhoneNumber(),
                'company_name' => fake()->company(),
                'company_address' => fake('id_ID')->address(),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedResearchPermissionLetters(): void
    {
        foreach (range(1, 8) as $index) {
            ResearchPermissionLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'study_program' => $this->fakeStudyProgram(),
                'phone_number' => $this->fakePhoneNumber(),
                'company_name' => fake()->company(),
                'company_address' => fake('id_ID')->address(),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedScholarshipsStatementLetters(): void
    {
        foreach (range(1, 8) as $index) {
            ScholarshipsStatementLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'study_program' => $this->fakeStudyProgram(),
                'nim' => $this->fakeNim(),
                'scolarship_name' => fake()->randomElement(['KIP Kuliah', 'Beasiswa Unggulan', 'Beasiswa Prestasi', 'Beasiswa Daerah']),
                'scolarship_provider' => fake()->randomElement(['Kemendikbud', 'Pemprov Kalimantan Timur', 'Yayasan Pendidikan', 'Perusahaan Mitra']),
                'phone_number' => $this->fakePhoneNumber(),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function seedTestingPermissionRequestLetters(): void
    {
        foreach (range(1, 8) as $index) {
            TestingPermissionRequestLetter::query()->create([
                'status' => 'SUBMITTED',
                'student_name' => fake('id_ID')->name(),
                'nim' => $this->fakeNim(),
                'study_program' => $this->fakeStudyProgram(),
                'phone_number' => $this->fakePhoneNumber(),
                'company_name' => fake()->company(),
                'company_address' => fake('id_ID')->address(),
                'letter_number' => null,
                'letter_date' => null,
                'pdf_path' => null,
            ]);
        }
    }

    private function fakeNim(): string
    {
        return '22' . fake()->numerify('########');
    }

    private function fakePhoneNumber(): string
    {
        return '08' . fake()->numerify('##########');
    }

    private function fakeStudyProgram(): string
    {
        return fake()->randomElement([
            'Teknik Informatika',
            'Teknik Sipil',
            'Teknik Industri',
            'Arsitektur',
            'Perencanaan Wilayah dan Kota',
        ]);
    }
}
