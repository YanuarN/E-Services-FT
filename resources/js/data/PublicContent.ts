export type PublicService = {
  id: number;
  title: string;
  description: string;
};

export const LetterServices: PublicService[] = [
  {
    id: 1,
    title: 'Surat Keterangan Aktif Kuliah',
    description:
      'Untuk keperluan beasiswa, asuransi, atau tunjangan orang tua.',
  },
  {
    id: 2,
    title: 'Surat Izin Penelitian',
    description:
      'Permohonan izin pengambilan data untuk tugas akhir atau riset.',
  },
  {
    id: 3,
    title: 'Surat Pengantar Magang / KP',
    description:
      'Surat resmi fakultas untuk pengajuan Kerja Praktek ke instansi.',
  },
  {
    id: 4,
    title: 'Surat Cuti Akademik',
    description: 'Prosedur permohonan berhenti studi sementara (cuti kuliah).',
  },
  {
    id: 5,
    title: 'Legalisir Dokumen Digital',
    description:
      'Permohonan tanda tangan elektronik untuk ijazah atau transkrip.',
  },
  {
    id: 6,
    title: 'Surat Bebas Pustaka',
    description:
      'Pernyataan tidak memiliki pinjaman buku di perpustakaan fakultas.',
  },
  {
    id: 7,
    title: 'Surat Rekomendasi Beasiswa',
    description:
      'Dikeluarkan oleh pimpinan fakultas untuk mendaftar beasiswa eksternal.',
  },
  {
    id: 8,
    title: 'Transkrip Nilai Sementara',
    description:
      'Laporan hasil studi yang telah ditempuh hingga semester terakhir.',
  },
  {
    id: 9,
    title: 'Perpanjangan Masa Studi',
    description:
      'Khusus untuk mahasiswa yang mendekati batas maksimal semester.',
  },
  {
    id: 10,
    title: 'Surat Dispensasi Kegiatan',
    description:
      'Permohonan izin tidak mengikuti perkuliahan karena delegasi lomba.',
  },
];

export const HomeServiceHighlights = {
  letters: LetterServices.slice(0, 4).map((service) => service.title),
  rooms: [
    'Ruang Seminar G.4.1 (Kapasitas 40)',
    'Laboratorium Komputer Dasar',
    'Ruang Rapat Departemen',
    'Auditorium Mohammad Djazman',
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
  'Ruang Seminar (Gedung F)',
  'Ruang Rapat Dekanat',
  'Auditorium Moh. Djazman',
  'GOR Kampus 2 UMS',
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
