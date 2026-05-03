export type PublicService = {
  id: number;
  title: string;
  description: string;
};

export const LetterServices: PublicService[] = [
  {
    id: 1,
    title: 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)',
    description:
      'Pengajuan izin mengikuti ujian bagi mahasiswa kerja praktek.',
  },
  {
    id: 2,
    title: 'Surat Permohonan Praktek Kerja Nyata (PKN)',
    description:
      'Surat resmi fakultas untuk pengajuan kerja praktek atau magang ke instansi.',
  },
  {
    id: 3,
    title: 'Surat Rekomendasi Magang Mandiri',
    description:
      'Surat rekomendasi fakultas untuk kebutuhan program magang mandiri.',
  },
  {
    id: 4,
    title: 'Surat Tugas Mahasiswa (Kolektif/Kelompok)',
    description: 'Surat tugas untuk kegiatan mahasiswa berbentuk kelompok.',
  },
  {
    id: 5,
    title: 'Surat Tugas Mahasiswa (Mandiri/Individual)',
    description: 'Surat tugas untuk kegiatan mahasiswa individual.',
  },
  {
    id: 6,
    title: 'Surat Pengantar Pembuatan Paspor (Mahasiswa)',
    description:
      'Surat pengantar untuk kebutuhan pengajuan paspor mahasiswa.',
  },
  {
    id: 7,
    title: 'Surat Permohonan Data Untuk Penelitian',
    description:
      'Surat permohonan pengambilan data penelitian ke instansi terkait.',
  },
  {
    id: 8,
    title: 'Surat Izin Survey Untuk Penelitian',
    description: 'Surat izin survey atau penelitian untuk instansi tujuan.',
  },
  {
    id: 9,
    title: 'Surat Keterangan Tidak Menerima Beasiswa Lain',
    description:
      'Surat keterangan status beasiswa untuk syarat administrasi.',
  },
  {
    id: 10,
    title: 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian',
    description:
      'Surat permohonan izin pengujian alat penelitian pada instansi tertentu.',
  },
];

export const HomeServiceHighlights = {
  letters: LetterServices.slice(0, 4).map((service) => service.title),
  rooms: [
    'H.4.1 (Kapasitas 60)',
    'Hall H (Kapasitas 100)',
    'F.1.2 (Kapasitas 50)',
    'F.2.4 (Kapasitas 100)',
  ],
};

export const HomeSteps = [
  {
    title: 'Pilih Layanan',
    description:
      'Tentukan jenis surat atau ruangan yang ingin Anda ajukan pada menu layanan.',
  },
  {
    title: 'Isi Formulir',
    description:
      'Lengkapi data diri dan lampirkan dokumen pendukung sesuai persyaratan yang diminta.',
  },
  {
    title: 'Konfirmasi via WhatsApp',
    description:
      'Setelah mengirim formulir, Anda akan mendapatkan pesan konfirmasi pengajuan berhasil.',
  },
  {
    title: 'Terima Notifikasi Status',
    description:
      'Admin akan memproses pengajuan Anda. Notifikasi setiap tahapan akan dikirim otomatis.',
  },
];

export const FeaturedRooms = [
  'H.4.1',
  'Hall H',
  'F.1.2',
  'F.2.4',
];

export const BookingSteps = [
  { id: 1, label: 'Langkah 1', title: 'Pilih Tanggal' },
  { id: 2, label: 'Langkah 2', title: 'Pilih Waktu' },
  { id: 3, label: 'Langkah 3', title: 'Isi Data' },
];

export const PreviewTimeSlots = [
  { time: '08.00-09.00', disabled: false },
  { time: '09.00-10.00', disabled: false },
  { time: '10.00-11.00', disabled: true },
  { time: '11.00-12.00', disabled: false },
];
