import type { InfoCardProps } from '@/types/components/InfoCard';

const InfoCard = ({ title, children, tone = 'default' }: InfoCardProps) => {
  return (
    <div
      className={`rounded-[24px] border p-6 ${
        tone === 'primary'
          ? 'border-[rgba(31,42,102,0.12)] bg-[var(--public-primary-hover)] text-white shadow-[var(--public-shadow)]'
          : 'border-[var(--public-border)] bg-[var(--public-surface-soft)]'
      }`}
    >
      <h3
        className={`text-[1.6rem] font-bold tracking-[-0.04em] sm:text-[1.75rem] ${tone === 'primary' ? 'text-white' : 'text-[var(--public-primary-hover)]'}`}
      >
        {title}
      </h3>
      <div
        className={`mt-5 text-sm leading-7 ${tone === 'primary' ? 'text-white/80' : 'text-[var(--public-text-muted)]'}`}
      >
        {children}
      </div>
    </div>
  );
};

export default InfoCard;
