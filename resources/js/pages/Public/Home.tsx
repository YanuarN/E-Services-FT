import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import PageHero from '@/components/PageHero/PageHero';
import SectionHeader from '@/components/SectionHeader/SectionHeader';
import ServiceCard from '@/components/ServiceCard/ServiceCard';
import TimelineStep from '@/components/TimelineStep/TimelineStep';
import { HomeServiceHighlights, HomeSteps } from '@/data/PublicContent';

const Home = () => {
  return (
    <AppLayout currentPath="/" pageTitle="Beranda">
      <PageHero
        title="Layanan Administrasi Digital Fakultas Teknik"
        description="Ajukan surat dan peminjaman ruang secara online. Proses cepat, efisien, dan terima notifikasi status secara langsung via WhatsApp."
        actions={[
          { label: 'Ajukan Surat', href: '/services' },
          { label: 'Pinjam Ruang', href: '/booking', variant: 'secondary' },
        ]}
      />

      <section className="bg-white py-20 sm:py-24">
        <div className="public-container">
          <SectionHeader eyebrow="Katalog Digital" title="Layanan Kami" />

          <div className="mt-14 grid gap-8 lg:grid-cols-2">
            <ServiceCard
              title="Layanan Surat"
              items={HomeServiceHighlights.letters}
              href="/services"
              icon={
                <svg
                  viewBox="0 0 24 24"
                  className="h-7 w-7"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="1.8"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M7 4h7l5 5v11a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M14 4v5h5"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="m10 15 2 2 4-4"
                  />
                </svg>
              }
            />
            <ServiceCard
              title="Pinjam Ruangan"
              items={HomeServiceHighlights.rooms}
              href="/booking"
              icon={
                <svg
                  viewBox="0 0 24 24"
                  className="h-7 w-7"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="1.8"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M4 20V5a1 1 0 0 1 1-1h5v16"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M10 20V8a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v12"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M8 8h.01M8 12h.01M8 16h.01M14 11h.01M14 15h.01M18 11h.01M18 15h.01"
                  />
                </svg>
              }
            />
          </div>
        </div>
      </section>

      <section className="bg-[var(--public-surface-soft)] py-20 sm:py-24">
        <div className="public-container">
          <SectionHeader
            centered
            title="Langkah Pengajuan"
            description="Proses administrasi yang ringkas dan terpantau sepenuhnya dari ponsel Anda."
          />

          <div className="relative mx-auto mt-16 max-w-5xl">
            <div className="timeline-line absolute left-1/2 top-0 hidden h-full w-px -translate-x-1/2 md:block" />
            <div className="space-y-4">
              {HomeSteps.map((step, index) => (
                <TimelineStep
                  key={step.title}
                  index={index + 1}
                  title={step.title}
                  description={step.description}
                  align={index % 2 === 0 ? 'left' : 'right'}
                />
              ))}
            </div>
          </div>
        </div>
      </section>
    </AppLayout>
  );
};

export default Home;
