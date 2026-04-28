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
- `${tanggal_hijriah}`
- `${tanggal}`
- `${hari}`
- `${bulan}`
- `${tahun}`
- `${status}`
- `${public_token}`
- `${verification_url}`

Placeholder image QR yang didukung di semua surat:

- `${qr_code}`
- `${verification_qr_code}`

Catatan:

- Sisipkan `${qr_code}` atau `${verification_qr_code}` pada template DOCX di area tempat QR ingin ditampilkan
- Service akan mengganti placeholder tersebut menjadi gambar QR code saat dokumen digenerate
- `${verification_url}` bisa dipakai sebagai teks pendamping di bawah QR code bila diperlukan

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
- `${nama_perusahaan}`
- `${alamat_perusahaan}`
- `${anggota_kelompok}`
- `${ujian}`
- `${semester}`
- `${tanggal_ujian}`

### Placeholder tabel anggota

Buat satu baris tabel DOCX yang berisi salah satu anchor berikut:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${m_nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

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
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${m_nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

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

- `${tanggal_kegiatan}`
- `${waktu}`
- `${tempat}`
- `${daftar_mahasiswa}`

### Placeholder tabel mahasiswa

Buat satu baris tabel DOCX yang berisi salah satu anchor berikut:

- `${mahasiswa_no}`
- `${anggota_no}`
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${mahasiswa_no}`
- `${anggota_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${mahasiswa_nim}`
- `${anggota_nim}`
- `${nim}`
- `${m_nim}`
- `${mahasiswa_prodi}`
- `${anggota_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

## 5. Surat Tugas Mahasiswa Individual

Service: `LetterOfAssignmentIndividualDocumentService`
Kode template: `letter_of_assignment_individual`

### Placeholder

- `${nama_mahasiswa}`
- `${nim}`
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
- `${anggota_kelompok}`

Catatan:

- `${nomor_telepon}` dan `${no_hp}` diisi dari input FE `Nomor WhatsApp Aktif` pemohon
- `${anggota_kelompok}` adalah ringkasan teks seluruh anggota kelompok dan bisa kosong bila tidak ada anggota

### Placeholder tabel anggota

Buat satu baris pertama tabel DOCX yang berisi salah satu anchor berikut. Sistem akan clone row tersebut untuk seluruh data anggota. Jika anggota kosong, placeholder tabel akan dikosongkan:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${m_nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

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
- `${anggota_kelompok}`

Catatan:

- `${nomor_telepon}` dan `${no_hp}` diisi dari input FE `Nomor WhatsApp Aktif`
- `${anggota_kelompok}` adalah ringkasan teks seluruh anggota kelompok

### Placeholder tabel anggota

Buat satu baris pertama tabel DOCX yang berisi salah satu anchor berikut. Sistem akan clone row tersebut untuk seluruh data anggota:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${m_nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

## 9. Formulir Peminjaman Ruangan

Service: `RoomUsageRequestDocumentService`
Kode template: `room_usage_request`

### Placeholder

- `${nama_mahasiswa}`
- `${nama}`
- `${nama_peminjam}`
- `${nim}`
- `${program_studi}`
- `${prodi}`
- `${nomor_telepon}`
- `${no_hp}`
- `${no_telp}`
- `${unit}`
- `${kegiatan}`
- `${nama_kegiatan}`
- `${tanggal_penggunaan}`
- `${tanggal_peminjaman}`
- `${tanggal_pengajuan}`
- `${tanggal_permohonan}`
- `${waktu_mulai}`
- `${waktu_selesai}`
- `${waktu_penggunaan}`
- `${waktu}`
- `${pukul}`
- `${tempat_ruang}`
- `${ruang}`
- `${tempat}`
- `${jumlah_peserta}`

## 10. Surat Keterangan Tidak Menerima Beasiswa Lain

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

## 11. Surat Permohonan Izin Pengujian Alat

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
- `${anggota_kelompok}`

Catatan:

- `${nomor_telepon}` dan `${no_hp}` diisi dari input FE `Nomor WhatsApp Aktif`
- `${anggota_kelompok}` adalah ringkasan teks seluruh anggota kelompok

### Placeholder tabel anggota

Buat satu baris pertama tabel DOCX yang berisi salah satu anchor berikut. Sistem akan clone row tersebut untuk seluruh data anggota:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${m_nama}`
- `${m_nim}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

Field yang bisa dipakai dalam satu baris tabel:

- `${anggota_no}`
- `${mahasiswa_no}`
- `${m_no}`
- `${nama_mahasiswa}`
- `${m_nama}`
- `${anggota_nim}`
- `${mahasiswa_nim}`
- `${nim}`
- `${m_nim}`
- `${anggota_prodi}`
- `${mahasiswa_prodi}`
- `${program_studi}`
- `${prodi}`
- `${m_prodi}`
- `${anggota_nomor_telepon}`
- `${anggota_no_hp}`
- `${mahasiswa_nomor_telepon}`
- `${mahasiswa_no_hp}`
- `${m_nomor_telepon}`
- `${m_no_hp}`

## Catatan Penggunaan

- Gunakan placeholder Indonesia yang tercantum di dokumen ini
- Untuk surat kelompok, cukup isi satu baris template tabel, sistem akan clone otomatis untuk semua anggota
- Jika placeholder tabel terlalu panjang untuk kolom sempit (misalnya kolom NO), gunakan alias pendek: `${m_no}`, `${m_nama}`, `${m_nim}`, `${m_prodi}`
- Placeholder umum seperti `${nomor_surat}` dan `${tanggal}` bisa dipakai di header, judul, atau footer surat
- `${nomor_permohonan}` masih diisi dari `letter_number` untuk kompatibilitas template lama, tetapi placeholder yang disarankan adalah `${nomor_surat}`
- Jika nanti ada placeholder baru di service, file ini perlu ikut diperbarui agar tim template tetap sinkron
