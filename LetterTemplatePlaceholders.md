# Dokumentasi Placeholder Template Surat E-Services-FT

Dokumen ini berisi daftar **placeholder DOCX** untuk seluruh template surat di `E-Services-FT`.
Semua placeholder menggunakan format:

```text
${nama_placeholder}
```

Dokumen ini disusun berdasarkan service pada folder `app/Services/Letters`.

## Aturan Umum

- Template surat harus berupa file `.docx`
- Semua placeholder ditulis dengan format `${placeholder}`
- Placeholder yang tidak memiliki nilai akan diisi string kosong
- PDF akan digenerate dari template Word melalui service universal

## Placeholder Umum

Placeholder ini tersedia di semua surat:

- `${nomor_surat}`
- `${no_surat}`
- `${tanggal_surat}`
- `${tanggal}`
- `${hari}`
- `${bulan}`
- `${tahun}`
- `${status}`
- `${public_token}`

## Placeholder Identitas Mahasiswa

Placeholder ini tersedia pada surat yang memakai helper identitas mahasiswa:

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`

## 1. Surat Izin Mengikuti Ujian

Service: `ExamPermissionLetterDocumentService`
Kode template: `exam_permission`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${nomor_permohonan}`
- `${ujian}`
- `${semester}`
- `${tanggal_ujian}`

## 2. Surat Permohonan Praktek Kerja Nyata

Service: `InternshipLetterDocumentService`
Kode template: `internship`

### Placeholder utama

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_perusahaan}`
- `${alamat_perusahaan}`
- `${anggota_kelompok}`

### Placeholder tabel anggota

Buat satu baris tabel DOCX yang berisi salah satu anchor berikut:

- `${anggota_no}`
- `${mahasiswa_no}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${nama_mahasiswa}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`

## 3. Surat Rekomendasi Magang Mandiri

Service: `InternshipRecommendationLetterDocumentService`
Kode template: `internship_recommendation`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${semester}`
- `${ipk}`
- `${nama_program}`

## 4. Surat Tugas Mahasiswa Kelompok

Service: `LetterOfAssignmentDocumentService`
Kode template: `letter_of_assignment`

### Placeholder utama

- `${nomor_permohonan}`
- `${tanggal_kegiatan}`
- `${waktu}`
- `${tempat}`
- `${daftar_mahasiswa}`

### Placeholder tabel mahasiswa

Buat satu baris tabel DOCX yang berisi salah satu anchor berikut:

- `${mahasiswa_no}`
- `${anggota_no}`

Field yang bisa dipakai dalam satu baris tabel:

- `${mahasiswa_no}`
- `${anggota_no}`
- `${nama_mahasiswa}`
- `${mahasiswa_nim}`
- `${anggota_nim}`
- `${nim}`
- `${mahasiswa_prodi}`
- `${anggota_prodi}`
- `${program_studi}`
- `${prodi}`

## 5. Surat Tugas Mahasiswa Individual

Service: `LetterOfAssignmentIndividualDocumentService`
Kode template: `letter_of_assignment_individual`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${nomor_permohonan}`
- `${departement}`
- `${fakultas}`
- `${alamat}`
- `${penugasan}`
- `${tempat}`
- `${tanggal_kegiatan}`

## 6. Surat Pengantar Pembuatan Paspor

Service: `PassportApplicationLetterDocumentService`
Kode template: `passport_application`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_kegiatan}`
- `${kegiatan}`

## 7. Surat Permohonan Data Untuk Penelitian

Service: `ResearchDataRequestLetterDocumentService`
Kode template: `research_data_request`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_instansi}`
- `${alamat_instansi}`

## 8. Surat Izin Survey Untuk Penelitian

Service: `ResearchPermissionLetterDocumentService`
Kode template: `research_permission`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_instansi}`
- `${alamat_instansi}`

## 9. Surat Keterangan Tidak Menerima Beasiswa Lain

Service: `ScholarshipsStatementLetterDocumentService`
Kode template: `scholarships_statement`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_beasiswa}`
- `${penyedia_beasiswa}`

## 10. Surat Permohonan Izin Pengujian Alat

Service: `TestingPermissionRequestLetterDocumentService`
Kode template: `testing_permission_request`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${nama_instansi}`
- `${alamat_instansi}`

## Catatan Penggunaan

- Gunakan placeholder Indonesia yang tercantum di dokumen ini
- Untuk surat kelompok, cukup buat satu baris template tabel, nanti sistem akan clone otomatis
- Placeholder umum seperti `${nomor_surat}` dan `${tanggal}` bisa dipakai di header, judul, atau footer surat
- Jika nanti ada placeholder baru di service, file ini perlu ikut diperbarui agar tim template tetap sinkron
