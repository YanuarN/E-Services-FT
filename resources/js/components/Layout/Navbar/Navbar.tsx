import { Link } from '@inertiajs/react';
import React, { useMemo, useState } from 'react';

interface NavbarProps {
  currentPath?: string;
}

const NavigationItems = [
  { label: 'Beranda', href: '/' },
  { label: 'Layanan Surat', href: '/services' },
  { label: 'Pinjam Ruang', href: '/booking' },
  { label: 'Panduan & SOP', href: '/guidelines' },
];

const Navbar: React.FC<NavbarProps> = ({ currentPath }) => {
  const [isOpen, setIsOpen] = useState(false);

  const activePath = useMemo(() => currentPath ?? '/', [currentPath]);

  return (
    <header className="sticky top-0 z-50 border-b border-[rgba(31,42,102,0.08)] bg-primary backdrop-blur">
      <nav className="public-container flex min-h-[76px] items-center justify-between gap-6">
        <div className="flex items-center gap-3">
          <Link
            href="/"
            className="text-xl font-bold tracking-[-0.04em] text-[var(--public-background)]"
          >
            E-Service FT UMS
          </Link>
        </div>

        <div className="hidden items-center gap-8 lg:flex">
          {NavigationItems.map((item) => {
            const isActive = activePath === item.href;

            return (
              <Link
                key={item.href}
                href={item.href}
                className={`relative pb-1 text-sm font-medium transition ${
                  isActive
                    ? 'text-[var(--public-background)]'
                    : 'text-[var(--public-background)] hover:text-[var(--public-background)]'
                }`}
              >
                {item.label}
                {isActive ? (
                  <span className="absolute inset-x-0 -bottom-[17px] h-[3px] rounded-full bg-[var(--public-accent)]" />
                ) : null}
              </Link>
            );
          })}
        </div>

        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={() => setIsOpen((value) => !value)}
            className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--public-border)] text-[var(--public-background)] lg:hidden"
            aria-label="Buka navigasi"
            aria-expanded={isOpen}
          >
            <svg
              viewBox="0 0 24 24"
              className="h-5 w-5"
              fill="none"
              stroke="currentColor"
              strokeWidth="1.8"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M4 7h16M4 12h16M4 17h16"
              />
            </svg>
          </button>
        </div>
      </nav>

      {isOpen ? (
        <div className="border-t border-[var(--public-border)] bg-primary lg:hidden">
          <div className="public-container flex flex-col gap-3 py-4">
            {NavigationItems.map((item) => {
              const isActive = activePath === item.href;

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`rounded-2xl px-4 py-3 text-sm font-medium ${
                    isActive
                      ? 'bg-[var(--public-primary-hover)] text-[var(--public-background)]'
                      : 'text-[var(--public-background)]'
                  }`}
                  onClick={() => setIsOpen(false)}
                >
                  {item.label}
                </Link>
              );
            })}
          </div>
        </div>
      ) : null}
    </header>
  );
};

export default Navbar;
