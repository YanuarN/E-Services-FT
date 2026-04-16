export type Step = {
  id: number;
  label: string;
  title: string;
};

export type StepProgressProps = {
  currentStep: number;
  steps: Step[];
};
