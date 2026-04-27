import type { CtaPanelProps } from '@/types/components/CtaPanel';
import { Link } from '@inertiajs/react';

const CtaPanel = ({
  title,
  items,
  href,
  actionLabel,
  aside,
}: CtaPanelProps) => {
  return (
    <section className="public-card-soft overflow-hidden border-[var(--public-border-strong)] px-8 py-10 lg:px-12">
      <div className="grid items-center gap-10 lg:grid-cols-[1.3fr_0.9fr]">
        <div>
          <h2 className="max-w-xl text-[2.6rem] font-bold leading-tight tracking-[-0.05em] text-[var(--public-primary-hover)]">
            {title}
          </h2>
          <div className="mt-8 grid gap-5 sm:grid-cols-2">
            {items.map((item) => (
              <div
                key={item}
                className="flex items-center gap-3 text-lg text-[var(--public-primary-hover)]"
              >
                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-sm shadow-[var(--public-shadow-soft)]">
                  •
                </span>
                <span>{item}</span>
              </div>
            ))}
          </div>
          <Link
            href={href}
            className="mt-10 inline-flex items-center justify-center rounded-2xl bg-[var(--public-accent)] px-8 py-4 text-base font-semibold text-[var(--public-primary-hover)] transition hover:brightness-95"
          >
            {actionLabel}
          </Link>
        </div>

        <div className="relative overflow-hidden rounded-[24px] border border-white/80 bg-gradient-to-br from-white to-[#d7e3ff] p-6 shadow-[var(--public-shadow-soft)]">
          {aside ?? (
            <div className="aspect-[4/5] rounded-[18px] bg-gradient-to-br from-[#b8c8ff] via-white to-[#dbe5ff]" />
          )}
        </div>
      </div>
    </section>
  );
};

export default CtaPanel;
