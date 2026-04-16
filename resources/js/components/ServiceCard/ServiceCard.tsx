import { Link } from '@inertiajs/react';
import type { ServiceCardProps } from '@/types/components/ServiceCard';

const ServiceCard = ({ title, items, href, icon }: ServiceCardProps) => {
  return (
    <div className="public-card p-8">
      <div className="flex items-start justify-between gap-6">
        <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--public-surface-soft)] text-[var(--public-primary-hover)]">
          {icon}
        </div>
        <Link
          href={href}
          className="text-sm font-semibold text-[var(--public-primary-hover)] transition hover:text-[#9b6b11]"
        >
          Lihat semua layanan →
        </Link>
      </div>

      <h3 className="mt-7 text-[2rem] font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
        {title}
      </h3>

      <ul className="mt-6 space-y-4">
        {items.map((item) => (
          <li
            key={item}
            className="flex items-start gap-3 text-sm leading-6 text-[var(--public-text-muted)]"
          >
            <span className="mt-2 h-2.5 w-2.5 rounded-full bg-[#a86a00]" />
            <span>{item}</span>
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ServiceCard;
