import type { TimelineStepProps } from '@/types/components/TimelineStep';

const TimelineStep = ({
  index,
  title,
  description,
  align,
}: TimelineStepProps) => {
  return (
    <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-6 py-4">
      <div
        className={`${align === 'left' ? 'text-right' : 'opacity-0'} hidden md:block`}
        aria-hidden={align !== 'left'}
      >
        {align === 'left' ? (
          <>
            <h3 className="text-[2rem] font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
              {title}
            </h3>
            <p className="mt-2 text-sm leading-6 text-[var(--public-text-muted)]">
              {description}
            </p>
          </>
        ) : null}
      </div>

      <div className="relative mx-auto flex h-16 w-16 items-center justify-center rounded-full border-4 border-[#ffe8a1] bg-[var(--public-accent)] text-lg font-bold text-[var(--public-primary-hover)] shadow-[0_8px_18px_rgba(244,196,48,0.28)]">
        {String(index).padStart(2, '0')}
      </div>

      <div
        className={`${align === 'right' ? 'text-left' : 'opacity-0'} hidden md:block`}
        aria-hidden={align !== 'right'}
      >
        {align === 'right' ? (
          <>
            <h3 className="text-[2rem] font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
              {title}
            </h3>
            <p className="mt-2 text-sm leading-6 text-[var(--public-text-muted)]">
              {description}
            </p>
          </>
        ) : null}
      </div>

      <div className="col-span-3 mt-4 rounded-2xl bg-white/75 p-5 text-center shadow-[0_10px_24px_rgba(31,42,102,0.05)] md:hidden">
        <h3 className="text-2xl font-bold tracking-[-0.04em] text-[var(--public-primary-hover)]">
          {title}
        </h3>
        <p className="mt-2 text-sm leading-6 text-[var(--public-text-muted)]">
          {description}
        </p>
      </div>
    </div>
  );
};

export default TimelineStep;
