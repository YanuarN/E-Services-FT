<?php

namespace App\Support;

class PublicServiceCatalog
{
    /**
     * @return array<int, array{
     *   key: string,
     *   title: string,
     *   description: string,
     *   fields: array<int, array{
     *     name: string,
     *     label: string,
     *     type: string,
     *     required: bool,
     *     placeholder?: string,
     *     helpText?: string,
     *     rows?: int,
     *     step?: string
     *   }>
     * }>
     */
    public static function services(): array
    {
        return [
            [
                'key' => 'exam_permission',
                'title' => 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)',
                'description' => 'Digunakan untuk pengajuan izin mengikuti ujian bagi mahasiswa kerja praktek.',
                'fields' => [
                    ['name' => 'student_name', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'placeholder' => 'Sesuai data resmi mahasiswa'],
                    ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: D100220001'],
                    ['name' => 'exam', 'label' => 'Jenis Ujian', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Ujian Pendadaran'],
                    ['name' => 'semester', 'label' => 'Semester', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 8'],
                    ['name' => 'exam_date', 'label' => 'Tanggal Ujian', 'type' => 'date', 'required' => true],
                ],
            ],
            [
                'key' => 'internship',
                'title' => 'Surat Permohonan Praktek Kerja Nyata (PKN)',
                'description' => 'Surat resmi fakultas untuk keperluan pengajuan kerja praktek/magang ke instansi.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'company_name', 'label' => 'Nama Instansi', 'type' => 'text', 'required' => true],
                    ['name' => 'company_address', 'label' => 'Alamat Instansi', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                    ['name' => 'group_member', 'label' => 'Daftar Anggota Kelompok', 'type' => 'textarea', 'required' => true, 'rows' => 4, 'helpText' => 'Pisahkan setiap anggota dengan baris baru.'],
                ],
            ],
            [
                'key' => 'internship_recommendation',
                'title' => 'Surat Rekomendasi Magang Mandiri',
                'description' => 'Surat rekomendasi fakultas untuk kebutuhan program magang mandiri.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'semester', 'label' => 'Semester', 'type' => 'text', 'required' => true],
                    ['name' => 'ipk', 'label' => 'IPK', 'type' => 'number', 'required' => true, 'step' => '0.01', 'placeholder' => 'Contoh: 3.45'],
                    ['name' => 'program_name', 'label' => 'Nama Program', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Magang dan Studi Independen'],
                ],
            ],
            [
                'key' => 'letter_of_assignment',
                'title' => 'Surat Tugas Mahasiswa (Kolektif/Kelompok)',
                'description' => 'Surat tugas untuk kegiatan mahasiswa berbentuk kelompok.',
                'fields' => [
                    ['name' => 'activity_date', 'label' => 'Tanggal Kegiatan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 20 Mei 2026'],
                    ['name' => 'activity_time', 'label' => 'Waktu Kegiatan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 08.00 - 12.00 WIB'],
                    ['name' => 'place', 'label' => 'Tempat', 'type' => 'text', 'required' => true],
                    ['name' => 'student_list', 'label' => 'Daftar Mahasiswa', 'type' => 'textarea', 'required' => true, 'rows' => 5, 'helpText' => 'Isi format bebas per baris. Contoh: Nama - NIM - Prodi.'],
                ],
            ],
            [
                'key' => 'letter_of_assignment_individual',
                'title' => 'Surat Tugas Mahasiswa (Mandiri/Individual)',
                'description' => 'Surat tugas untuk kegiatan mahasiswa individual.',
                'fields' => [
                    ['name' => 'student_name', 'label' => 'Nama Mahasiswa', 'type' => 'text', 'required' => true],
                    ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
                    ['name' => 'departement', 'label' => 'Jurusan', 'type' => 'text', 'required' => true],
                    ['name' => 'faculty', 'label' => 'Fakultas', 'type' => 'text', 'required' => true],
                    ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                    ['name' => 'assignment', 'label' => 'Penugasan', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                    ['name' => 'place', 'label' => 'Tempat', 'type' => 'text', 'required' => true],
                    ['name' => 'date', 'label' => 'Tanggal Kegiatan', 'type' => 'date', 'required' => true],
                ],
            ],
            [
                'key' => 'passport_application',
                'title' => 'Surat Pengantar Pembuatan Paspor (Mahasiswa)',
                'description' => 'Surat pengantar untuk kebutuhan pengajuan paspor mahasiswa.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'event_name', 'label' => 'Keperluan', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'key' => 'research_data_request',
                'title' => 'Surat Permohonan Data Untuk Penelitian',
                'description' => 'Surat permohonan pengambilan data penelitian ke instansi terkait.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'company_name', 'label' => 'Instansi Tujuan', 'type' => 'text', 'required' => true],
                    ['name' => 'company_address', 'label' => 'Alamat Instansi', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                ],
            ],
            [
                'key' => 'research_permission',
                'title' => 'Surat Izin Survey Untuk Penelitian',
                'description' => 'Surat izin survey/penelitian untuk instansi tujuan.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'company_name', 'label' => 'Instansi Tujuan', 'type' => 'text', 'required' => true],
                    ['name' => 'company_address', 'label' => 'Alamat Instansi', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                ],
            ],
            [
                'key' => 'scholarships_statement',
                'title' => 'Surat Keterangan Tidak Menerima Beasiswa Lain',
                'description' => 'Surat keterangan status beasiswa untuk syarat administrasi.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'scolarship_name', 'label' => 'Nama Beasiswa', 'type' => 'text', 'required' => true],
                    ['name' => 'scolarship_provider', 'label' => 'Pemberi Beasiswa', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'key' => 'testing_permission_request',
                'title' => 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian',
                'description' => 'Surat permohonan izin pengujian alat penelitian pada instansi tertentu.',
                'fields' => [
                    ...self::studentFields(),
                    ['name' => 'company_name', 'label' => 'Instansi Tujuan', 'type' => 'text', 'required' => true],
                    ['name' => 'company_address', 'label' => 'Alamat Instansi', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                ],
            ],
        ];
    }

    /**
     * @return array{
     *   key: string,
     *   title: string,
     *   description: string,
     *   fields: array<int, array<string, mixed>>
     * }|null
     */
    public static function find(string $letterType): ?array
    {
        foreach (self::services() as $service) {
            if ($service['key'] === $letterType) {
                return $service;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public static function studyPrograms(): array
    {
        return [
            'Teknik Sipil',
            'Teknik Mesin',
            'Teknik Kimia',
            'Teknik Elektro',
            'Teknik Industri',
            'Arsitektur',
            'Informatika',
        ];
    }

    /**
     * @return array<int, array{
     *   name: string,
     *   label: string,
     *   type: string,
     *   required: bool,
     *   placeholder?: string
     * }>
     */
    private static function studentFields(): array
    {
        return [
            ['name' => 'student_name', 'label' => 'Nama Mahasiswa', 'type' => 'text', 'required' => true, 'placeholder' => 'Sesuai data resmi mahasiswa'],
            ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: D100220001'],
            ['name' => 'study_program', 'label' => 'Program Studi', 'type' => 'select_study_program', 'required' => true],
            ['name' => 'phone_number', 'label' => 'Nomor WhatsApp Aktif', 'type' => 'tel', 'required' => true, 'placeholder' => 'Contoh: 081234567890'],
        ];
    }
}
