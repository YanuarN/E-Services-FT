import { Link } from '@inertiajs/react';
import React from 'react';
import type { BreadcrumbProps } from '@/types/components/Breadcrumb';

const Breadcrumb = ({ items }: BreadcrumbProps) => {
  return (
    <nav
      aria-label="Breadcrumb"
      className="flex flex-wrap items-center gap-2 text-sm text-[var(--public-text-muted)]"
    >
      {items.map((item, index) => {
        const isLast = index === items.length - 1;

        return (
          <React.Fragment key={`${item.label}-${index}`}>
            {item.href && !isLast ? (
              <Link
                href={item.href}
                className="transition-colors hover:text-[var(--public-primary)]"
              >
                {item.label}
              </Link>
            ) : (
              <span
                className={
                  isLast ? 'font-medium text-[var(--public-primary-hover)]' : ''
                }
              >
                {item.label}
              </span>
            )}
            {!isLast && <span aria-hidden="true">›</span>}
          </React.Fragment>
        );
      })}
    </nav>
  );
};

export default Breadcrumb;
