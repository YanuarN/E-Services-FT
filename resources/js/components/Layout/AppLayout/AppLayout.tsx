import { Head } from '@inertiajs/react';
import React, { ReactNode } from 'react';
import Footer from '../Footer/Footer';
import Navbar from '../Navbar/Navbar';

interface AppLayoutProps {
  children: ReactNode;
  currentPath?: string;
  pageTitle?: string;
}

const AppLayout: React.FC<AppLayoutProps> = ({
  children,
  currentPath,
  pageTitle,
}) => {
  return (
    <div className="public-shell flex flex-col">
      {pageTitle ? <Head title={pageTitle} /> : null}
      <Navbar currentPath={currentPath} />
      <main className="flex-grow">{children}</main>
      <Footer />
    </div>
  );
};

export default AppLayout;
