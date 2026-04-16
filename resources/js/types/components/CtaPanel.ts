import type { ReactNode } from 'react';

export type CtaPanelProps = {
  title: string;
  items: string[];
  href: string;
  actionLabel: string;
  aside?: ReactNode;
};
