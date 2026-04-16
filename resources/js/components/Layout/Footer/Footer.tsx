import { Link } from '@inertiajs/react';
import React from 'react';

const Footer: React.FC = () => {
  return (
    <footer className="bg-[var(--public-primary-hover)] text-white">
      <div className="public-container grid gap-10 py-14 md:grid-cols-[1.6fr_0.9fr_1fr]">
        {/* Brand */}
        <div>
          <h3 className="text-2xl font-bold tracking-[-0.04em]">
            E-Service FT UMS
          </h3>
          <p className="mt-4 max-w-xs text-sm leading-7 text-white/70">
            Layanan administrasi digital Fakultas Teknik Universitas
            Muhammadiyah Surakarta untuk mempermudah civitas akademika dalam
            pelayanan surat dan sarana prasarana.
          </p>
          <div className="mt-6 flex items-center gap-3">
            <a
              href="https://ft.ums.ac.id"
              target="_blank"
              rel="noreferrer"
              className="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 text-white/70 transition hover:border-white/50 hover:text-white"
              aria-label="Website"
            >
              <svg
                viewBox="0 0 24 24"
                className="h-4 w-4"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.8"
              >
                <circle cx="12" cy="12" r="10" />
                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
              </svg>
            </a>
            <a
              href="mailto:teknik@ums.ac.id"
              className="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 text-white/70 transition hover:border-white/50 hover:text-white"
              aria-label="Email"
            >
              <svg
                viewBox="0 0 24 24"
                className="h-4 w-4"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.8"
              >
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
            </a>
          </div>
        </div>

        {/* Quick Links */}
        <div>
          <h4 className="text-sm font-semibold uppercase tracking-[0.18em] text-white/60">
            Tautan Cepat
          </h4>
          <div className="mt-5 space-y-3 text-sm">
            <Link
              href="/"
              className="block text-white/80 transition hover:text-white"
            >
              Beranda
            </Link>
            <Link
              href="/services"
              className="block text-white/80 transition hover:text-white"
            >
              Layanan Surat
            </Link>
            <Link
              href="/booking"
              className="block font-semibold text-[var(--public-accent)] transition hover:brightness-110"
            >
              Peminjaman Ruang
            </Link>
            <Link
              href="/guidelines"
              className="block text-white/80 transition hover:text-white"
            >
              Panduan &amp; SOP
            </Link>
          </div>
        </div>

        {/* Contact */}
        <div>
          <h4 className="text-sm font-semibold uppercase tracking-[0.18em] text-white/60">
            Hubungi Kami
          </h4>
          <div className="mt-5 space-y-3 text-sm leading-7 text-white/80">
            <p>
              Jl. A. Yani, Pabelan, Kartasura,
              <br />
              Sukoharjo, Jawa Tengah 57162
            </p>
            <p>Email: teknik@ums.ac.id</p>
            <p>Telp: (0271) 717417</p>
          </div>
        </div>
      </div>

      <div className="border-t border-white/10">
        <div className="public-container py-5 text-center text-xs text-white/50">
          <p>© 2024 FT UMS. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
