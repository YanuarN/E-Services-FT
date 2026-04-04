<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    public const LETTER_TYPES = [
        'exam_permission' => 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)',
        'internship' => 'Surat Permohonan Praktek Kerja Nyata (PKN)',
        'internship_recommendation' => 'Surat Rekomendasi Magang Mandiri',
        'letter_of_assignment' => 'Surat Tugas Mahasiswa (Kolektif/Kelompok)',
        'letter_of_assignment_individual' => 'Surat Tugas Mahasiswa (Mandiri/Individual)',
        'passport_application' => 'Surat Pengantar Pembuatan Paspor (Mahasiswa)',
        'research_data_request' => 'Surat Permohonan Data Untuk Penelitian',
        'research_permission' => 'Surat Izin Survey Untuk Penelitian',
        'scholarships_statement' => 'Surat Keterangan Tidak Menerima Beasiswa Lain',
        'testing_permission_request' => 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian',
    ];

    protected $fillable = [
        'letter_type',
        'document_path',
    ];

    protected $casts = [
        'letter_type' => 'string',
    ];

    public function getLetterTypeLabelAttribute(): string
    {
        return self::LETTER_TYPES[$this->letter_type] ?? $this->letter_type;
    }
}
