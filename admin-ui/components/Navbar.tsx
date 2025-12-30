'use client';

import { useAuth } from '@/lib/auth-context';

interface NavbarProps {
  title: string;
}

export default function Navbar({ title }: NavbarProps) {
  const { logout } = useAuth();

  return (
    <nav className="border-b border-gray-200 bg-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 justify-between">
          <div className="flex items-center">
            <h1 className="text-xl font-bold text-gray-900">{title}</h1>
          </div>
          <div className="flex items-center gap-4">
            <button
              onClick={logout}
              className="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 transition-colors"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
}
