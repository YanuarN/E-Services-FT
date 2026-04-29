import LogoUMS from '@/assets/LogoUMS.webp';
import type { DocumentVerificationProps } from '@/types/pages/DocumentVerification';
import { Head } from '@inertiajs/react';

export default function DocumentVerification({
  title,
  status,
  letterNumber,
  letterDate,
  subject,
  studentName,
  documentUrl,
  scannedAt,
  fields = [],
}: DocumentVerificationProps) {
  const normalizedStatus = status.toUpperCase();
  const isApproved = ['APPROVED', 'APPROVE', 'VALID', 'ACTIVE'].includes(
    normalizedStatus,
  );

  const statusLabel = isApproved
    ? 'Surat terverifikasi'
    : normalizedStatus
      ? `Status surat: ${normalizedStatus.replaceAll('_', ' ')}`
      : 'Status surat belum tersedia';

  const detailRows =
    fields.length > 0
      ? fields.filter((field) => field.label !== 'Link Surat')
      : [
          { label: 'Nomor Surat', value: letterNumber },
          { label: 'Tanggal Surat', value: letterDate },
          { label: 'Hal', value: subject },
          { label: 'Nama Mahasiswa', value: studentName },
        ];

  return (
    <>
      <Head title={`${title} | Verifikasi Dokumen`} />

      <main className="relative min-h-screen overflow-hidden bg-[var(--public-primary)]">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.16),transparent_44%)]" />
        <div className="absolute right-[-8%] top-[-16%] h-[520px] w-[520px] rounded-full border border-white/10" />
        <div className="absolute bottom-[-12%] left-[-8%] h-[440px] w-[440px] rounded-full border border-white/10" />
        <div className="absolute inset-y-0 right-0 hidden w-1/3 bg-[linear-gradient(135deg,rgba(255,255,255,0.1),rgba(255,255,255,0))] lg:block" />

        <section className="relative mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10 sm:px-6 sm:py-16">
          <div className="w-full max-w-3xl rounded-[28px] bg-white/95 p-6 shadow-[0_20px_60px_-30px_rgba(31,42,102,0.7)] backdrop-blur sm:p-10">
            <div className="flex flex-col items-center text-center">
              <img src={LogoUMS} alt="Logo UMS" className="h-24 w-24 sm:h-28 sm:w-28" />

              <p className="mt-5 text-xs font-extrabold uppercase tracking-[0.35em] text-[var(--public-primary)] sm:text-sm">
                {title}
              </p>

              <h1 className="mt-4 text-xl font-black uppercase leading-tight text-[var(--public-primary)] sm:text-2xl">
                Berkas Ini Resmi Dikelola Dengan Aplikasi E-Services Fakultas Teknik Universitas Muhammadiyah Surakarta
              </h1>
            </div>

            <div className="mt-8 rounded-2xl border border-slate-200/80 bg-white px-5 py-6 shadow-sm sm:px-6">
              <p className="text-xs font-black uppercase tracking-[0.35em] text-[var(--public-primary)]">
                Dokumen Sign
              </p>

              <div
                className={`mt-4 rounded-md border px-4 py-3 text-sm font-bold ${
                  isApproved
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-amber-200 bg-amber-50 text-amber-700'
                }`}
              >
                {statusLabel}
              </div>

              <div className="mt-5 space-y-4 text-sm text-slate-700">
                {detailRows.map((field) => (
                  <div
                    key={field.label}
                    className="grid gap-2 border-b border-slate-200/80 pb-3 sm:grid-cols-[190px_16px_1fr] sm:items-start"
                  >
                    <span className="font-semibold text-slate-600">
                      {field.label}
                    </span>
                    <span className="hidden text-slate-400 sm:block">:</span>
                    <span className="font-semibold text-slate-900">
                      {field.value}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            <div className="mt-8 flex flex-col items-center justify-center gap-3 text-center">
              {documentUrl ? (
                <a
                  href={documentUrl}
                  className="inline-flex items-center rounded-full border border-[var(--public-primary)]/20 bg-[var(--public-surface-soft)] px-5 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-[var(--public-primary)] transition hover:bg-[var(--public-primary)] hover:text-white"
                >
                  Lihat File Digital
                </a>
              ) : (
                <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-5 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-slate-500">
                  File Digital Belum Tersedia
                </span>
              )}

              <p className="text-xs font-medium text-slate-400">
                Dipindai {scannedAt}
              </p>
            </div>
          </div>
        </section>
      </main>
    </>
  );
}
