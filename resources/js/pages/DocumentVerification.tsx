import { Head } from '@inertiajs/react';
import type { DocumentVerificationProps } from '@/types/pages/DocumentVerification';

const statusAppearance: Record<
  string,
  { badge: string; panel: string; title: string; description: string }
> = {
  APPROVE: {
    badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    panel: 'border-emerald-200 bg-emerald-50/80',
    title: 'Dokumen terverifikasi',
    description:
      'Data surat ditemukan dan status surat sudah disetujui di sistem E-Services FT.',
  },
  REJECT: {
    badge: 'bg-rose-100 text-rose-700 ring-rose-200',
    panel: 'border-rose-200 bg-rose-50/80',
    title: 'Dokumen ditemukan dengan status ditolak',
    description:
      'Data surat tercatat di sistem, namun status surat saat ini tidak disetujui.',
  },
  SUBMITTED: {
    badge: 'bg-amber-100 text-amber-700 ring-amber-200',
    panel: 'border-amber-200 bg-amber-50/80',
    title: 'Dokumen ditemukan dan masih diproses',
    description:
      'Data surat tercatat di sistem, tetapi status surat belum final.',
  },
};

export default function DocumentVerification({
  title,
  status,
  fields,
  scannedAt,
}: DocumentVerificationProps) {
  const appearance = statusAppearance[status] ?? {
    badge: 'bg-slate-100 text-slate-700 ring-slate-200',
    panel: 'border-slate-200 bg-white/90',
    title: 'Dokumen ditemukan',
    description: 'Data surat tercatat di sistem verifikasi E-Services FT.',
  };

  return (
    <>
      <Head title="Verifikasi Dokumen" />

      <main className="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(14,116,144,0.18),_transparent_38%),linear-gradient(180deg,_#f8fafc_0%,_#ecfeff_100%)] px-4 py-10 text-slate-900 sm:px-6 lg:px-8">
        <div className="mx-auto max-w-4xl">
          <div className="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.28)] backdrop-blur">
            <div className="border-b border-slate-200/80 bg-[linear-gradient(135deg,_rgba(12,74,110,0.98),_rgba(8,145,178,0.92))] px-6 py-8 text-white sm:px-8">
              <div className="flex flex-wrap items-center gap-3">
                <span
                  className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] ring-1 ${appearance.badge}`}
                >
                  {status}
                </span>
                <span className="text-xs uppercase tracking-[0.22em] text-cyan-100">
                  E-Services FT
                </span>
              </div>

              <h1 className="mt-5 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">
                {appearance.title}
              </h1>

              <p className="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90 sm:text-base">
                {appearance.description}
              </p>
            </div>

            <div className="grid gap-6 px-6 py-6 sm:px-8 lg:grid-cols-[1.1fr_0.9fr]">
              <section
                className={`rounded-3xl border p-5 sm:p-6 ${appearance.panel}`}
              >
                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                  Jenis Surat
                </p>
                <h2 className="mt-3 text-2xl font-semibold leading-tight text-slate-900">
                  {title}
                </h2>
                <p className="mt-4 text-sm leading-6 text-slate-600">
                  Halaman ini muncul dari QR code pada surat dan menampilkan
                  metadata dokumen yang tersimpan di sistem.
                </p>
              </section>

              <section className="rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:p-6">
                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                  Waktu Scan
                </p>
                <p className="mt-3 text-2xl font-semibold text-slate-900">
                  {scannedAt}
                </p>
                <p className="mt-4 text-sm leading-6 text-slate-600">
                  Cocokkan data berikut dengan surat fisik atau PDF yang Anda
                  terima.
                </p>
              </section>
            </div>

            <div className="border-t border-slate-200/80 px-6 py-6 sm:px-8">
              <div className="grid gap-4 sm:grid-cols-2">
                {fields.map((field) => (
                  <article
                    key={`${field.label}-${field.value}`}
                    className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                  >
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                      {field.label}
                    </p>
                    <p className="mt-3 break-words text-base font-medium leading-7 text-slate-900">
                      {field.value}
                    </p>
                  </article>
                ))}
              </div>
            </div>
          </div>
        </div>
      </main>
    </>
  );
}
