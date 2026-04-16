import Breadcrumb from '@/components/Breadcrumb/Breadcrumb';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';

const Guidelines = () => {
  return (
    <AppLayout currentPath="/guidelines" pageTitle="Panduan & SOP">
      <section className="border-b border-[var(--public-border)] bg-white py-10 sm:py-12">
        <div className="public-container">
          <Breadcrumb
            items={[
              { label: 'Beranda', href: '/' },
              { label: 'Panduan & SOP' },
            ]}
          />
          <h1 className="mt-5 text-5xl font-bold tracking-[-0.05em] text-[var(--public-primary-hover)]">
            Panduan &amp; SOP
          </h1>
          <p className="mt-5 max-w-2xl text-lg leading-8 text-[var(--public-text-muted)]">
            Ringkasan alur layanan untuk membantu mahasiswa menyiapkan dokumen,
            memilih ruangan, dan memahami proses verifikasi administrasi.
          </p>
        </div>
      </section>

      <section className="py-12 sm:py-16">
        <div className="public-container grid gap-6 lg:grid-cols-3">
          <article className="public-card p-7">
            <p className="text-xs font-semibold uppercase tracking-[0.25em] text-[#9b6b11]">
              01
            </p>
            <h2 className="mt-4 text-3xl font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
              Siapkan Berkas
            </h2>
            <p className="mt-4 text-sm leading-7 text-[var(--public-text-muted)]">
              Pastikan NIM, nama, dan lampiran pendukung sudah sesuai data
              akademik sebelum mengajukan layanan.
            </p>
          </article>

          <article className="public-card p-7">
            <p className="text-xs font-semibold uppercase tracking-[0.25em] text-[#9b6b11]">
              02
            </p>
            <h2 className="mt-4 text-3xl font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
              Ajukan Online
            </h2>
            <p className="mt-4 text-sm leading-7 text-[var(--public-text-muted)]">
              Pilih layanan surat atau peminjaman ruang, lalu isi formulir
              sesuai kebutuhan kegiatan atau administrasi Anda.
            </p>
          </article>

          <article className="public-card p-7">
            <p className="text-xs font-semibold uppercase tracking-[0.25em] text-[#9b6b11]">
              03
            </p>
            <h2 className="mt-4 text-3xl font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
              Pantau Status
            </h2>
            <p className="mt-4 text-sm leading-7 text-[var(--public-text-muted)]">
              Setiap pengajuan akan diverifikasi admin. Notifikasi dan tindak
              lanjut akan dikirimkan melalui kanal komunikasi yang tersedia.
            </p>
          </article>
        </div>
      </section>
    </AppLayout>
  );
};

export default Guidelines;
