import type { ReactNode } from 'react';

export type InfoCardProps = {
  title: string;
  children: ReactNode;
  tone?: 'default' | 'primary';
};
