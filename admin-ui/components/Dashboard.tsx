'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getAdminStatistics } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface Statistics {
  total_users?: number;
  total_posts?: number;
  total_flagged_content?: number;
  recent_activity?: number;
}

export default function Dashboard() {
  const [stats, setStats] = useState<Statistics | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await getAdminStatistics();
        // API returns { status, period, data: { ... } }
        setStats(response.data || {});
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load statistics');
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  if (loading) return <LoadingSpinner />;

  const statCards = [
    { label: 'Total Users', value: stats?.total_users || 0, color: 'bg-blue-500' },
    { label: 'Total Posts', value: stats?.total_posts || 0, color: 'bg-green-500' },
    { label: 'Flagged Content', value: stats?.total_flagged_content || 0, color: 'bg-red-500' },
    { label: 'Active Users', value: stats?.active_users || 0, color: 'bg-purple-500' },
  ];

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Dashboard</h2>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {statCards.map((card, index) => (
          <div key={`stat-${index}`} className="rounded-lg bg-white p-6 shadow">
            <p className="text-sm text-gray-600">{card.label}</p>
            <div className="mt-2 flex items-end gap-2">
              <div className={`h-12 w-1 rounded ${card.color}`}></div>
              <p className="text-3xl font-bold text-gray-900">{card.value}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
