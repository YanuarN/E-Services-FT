import { Link } from '@inertiajs/react';

import Breadcrumb from '@/components/Breadcrumb/Breadcrumb';
import CtaPanel from '@/components/CtaPanel/CtaPanel';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import { FeaturedRooms } from '@/data/PublicContent';
import type { ServicesProps } from '@/types/pages/Public/ServiceCatalog';

const Services = ({ services }: ServicesProps) => {
  return (
    <AppLayout currentPath="/services" pageTitle="Layanan Surat">
      <section className="border-b border-[var(--public-border)] bg-white py-10 sm:py-12">
        <div className="public-container">
          <Breadcrumb
            items={[{ label: 'Beranda', href: '/' }, { label: 'Layanan' }]}
          />
          <h1 className="mt-5 text-5xl font-bold tracking-[-0.05em] text-[var(--public-primary-hover)]">
            Pilih Layanan
          </h1>
        </div>
      </section>

      <section className="py-12 sm:py-16">
        <div className="public-container space-y-10">
          <div className="overflow-hidden rounded-[24px] border border-[var(--public-border)] bg-white shadow-[var(--public-shadow-soft)]">
            <div className="hidden grid-cols-[90px_1.4fr_2.4fr_130px] bg-[var(--public-surface-soft)] px-6 py-5 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--public-text-muted)] md:grid">
              <span>No</span>
              <span>Nama Layanan</span>
              <span>Keterangan Singkat</span>
              <span className="text-right">Aksi</span>
            </div>

            <div className="divide-y divide-[var(--public-border)]">
              {services.map((service, index) => (
                <div
                  key={service.key}
                  className="grid gap-4 px-6 py-5 md:grid-cols-[90px_1.4fr_2.4fr_130px] md:items-center"
                >
                  <div className="text-sm text-[var(--public-text-muted)]">
                    {String(index + 1).padStart(2, '0')}
                  </div>
                  <div className="text-lg font-semibold text-[var(--public-primary-hover)]">
                    {service.title}
                  </div>
                  <div className="text-sm leading-7 text-[var(--public-text-muted)]">
                    {service.description}
                  </div>
                  <div className="md:text-right">
                    <Link
                      href={`/form/${service.key}`}
                      className="inline-flex items-center gap-2 text-base font-semibold text-[#9b6b11] transition hover:text-[var(--public-primary-hover)]"
                    >
                      Ajukan <span aria-hidden="true">→</span>
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <CtaPanel
            title="Butuh Ruangan untuk Kegiatan Mahasiswa?"
            items={FeaturedRooms}
            href="/booking"
            actionLabel="Lihat Kalender Ketersediaan"
            aside={
              <div className="aspect-[4/5] rounded-[18px] bg-[linear-gradient(135deg,rgba(25,45,122,0.12),rgba(255,255,255,0.65)),linear-gradient(180deg,#cfdcff_0%,#f5f8ff_22%,#e4ecff_22%,#e4ecff_26%,#f9fbff_26%,#f9fbff_100%)] p-5">
                <div className="flex h-full items-end rounded-[16px] border border-white/80 bg-[linear-gradient(180deg,#eef4ff_0%,#dde7ff_100%)] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
                  <div className="grid w-full grid-cols-3 gap-3">
                    {Array.from({ length: 9 }).map((_, index) => (
                      <div
                        key={index}
                        className="h-20 rounded-md bg-white/70 shadow-[0_10px_18px_rgba(31,42,102,0.08)]"
                      />
                    ))}
                  </div>
                </div>
              </div>
            }
          />
        </div>
      </section>
    </AppLayout>
  );
};

export default Services;
