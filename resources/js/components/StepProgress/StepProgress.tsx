import type { StepProgressProps } from '@/types/components/StepProgress';

const StepProgress = ({ currentStep, steps }: StepProgressProps) => {
  return (
    <div className="grid gap-4 md:grid-cols-3">
      {steps.map((step) => {
        const isActive = step.id === currentStep;
        const isCompleted = step.id < currentStep;

        return (
          <div
            key={step.id}
            className={`rounded-3xl border px-5 py-5 transition ${
              isActive
                ? 'border-[var(--public-border)] bg-white shadow-[var(--public-shadow-soft)]'
                : 'border-transparent bg-[var(--public-surface-soft)]'
            }`}
          >
            <div className="flex items-center gap-4">
              <span
                className={`flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold ${
                  isActive || isCompleted
                    ? 'bg-[var(--public-primary-hover)] text-white'
                    : 'bg-white text-[var(--public-text-muted)]'
                }`}
              >
                {step.id}
              </span>
              <div>
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--public-text-muted)]">
                  {step.label}
                </p>
                <p
                  className={`mt-1 text-lg font-semibold ${isActive ? 'text-[var(--public-primary-hover)]' : 'text-[var(--public-text-muted)]'}`}
                >
                  {step.title}
                </p>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
};

export default StepProgress;
