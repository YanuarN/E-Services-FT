import type { KeyboardEvent } from 'react';

const allowedControlKeys = new Set([
  'Backspace',
  'Delete',
  'Tab',
  'ArrowLeft',
  'ArrowRight',
  'Home',
  'End',
]);

export const sanitizeNumericValue = (value: string): string => {
  return value.replace(/\D/g, '');
};

export const preventNonNumericKeydown = (
  event: KeyboardEvent<HTMLInputElement>,
): void => {
  if (
    allowedControlKeys.has(event.key) ||
    event.ctrlKey ||
    event.metaKey
  ) {
    return;
  }

  if (!/^\d$/.test(event.key)) {
    event.preventDefault();
  }
};
