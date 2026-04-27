import type { PageHeroProps } from '@/types/components/PageHero';
import { Link } from '@inertiajs/react';

const PageHero = ({ title, description, actions }: PageHeroProps) => {
  return (
    <section className="public-hero-bg relative overflow-hidden text-white">
      <div className="public-hero-grid absolute inset-0 opacity-40" />
      <div className="absolute inset-y-0 right-0 hidden w-[32%] bg-gradient-to-l from-black/15 via-white/5 to-transparent lg:block" />
      <div className="public-container relative py-20 sm:py-24 lg:py-28">
        <div className="max-w-3xl">
          <h1 className="max-w-2xl text-5xl font-bold leading-[0.95] tracking-[-0.05em] text-white sm:text-6xl lg:text-[4.6rem]">
            {title}
          </h1>
          <p className="text-white/86 mt-6 max-w-xl text-lg leading-8">
            {description}
          </p>
          <div className="mt-10 flex flex-col gap-4 sm:flex-row">
            {actions.map((action) => (
              <Link
                key={action.href}
                href={action.href}
                className={
                  action.variant === 'secondary'
                    ? 'inline-flex items-center justify-center rounded-xl border border-white/50 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white hover:text-[var(--public-primary-hover)]'
                    : 'inline-flex items-center justify-center rounded-xl bg-[var(--public-accent)] px-6 py-3 text-sm font-semibold text-[var(--public-primary-hover)] transition hover:brightness-95'
                }
              >
                {action.label}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default PageHero;
