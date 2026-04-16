import type { SectionHeaderProps } from '@/types/components/SectionHeader';

const SectionHeader = ({
  eyebrow,
  title,
  description,
  centered = false,
}: SectionHeaderProps) => {
  const alignment = centered ? 'mx-auto text-center' : '';

  return (
    <div className={`max-w-3xl ${alignment}`}>
      {eyebrow ? (
        <p className="mb-4 text-xs font-semibold uppercase tracking-[0.35em] text-[#9b6b11]">
          {eyebrow}
        </p>
      ) : null}
      <h2 className="public-section-title">{title}</h2>
      {description ? (
        <p className="mt-4 text-base leading-7 text-[var(--public-text-muted)]">
          {description}
        </p>
      ) : null}
    </div>
  );
};

export default SectionHeader;
