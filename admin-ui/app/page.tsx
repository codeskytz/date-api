'use client';

import { useAuth } from '@/lib/auth-context';
import LoginPage from '@/components/LoginPage';
import AdminDashboard from '@/components/AdminDashboard';

export default function Home() {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <LoginPage />;
  }

  return <AdminDashboard />;
}
