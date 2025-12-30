'use client';

import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Ban, Flag, CircleDot } from 'lucide-react';
import toast from 'react-hot-toast';
import { getActivityLog } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface ActivityEntry {
  id: string;
  action: string;
  admin_id?: string;
  target_type?: string;
  target_id?: string;
  details?: string;
  created_at?: string;
}

export default function ActivityLog() {
  const [activities, setActivities] = useState<ActivityEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  useEffect(() => {
    const fetchActivityLog = async () => {
      try {
        const data = await getActivityLog(page, 20);
        setActivities(data.data || []);
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load activity log');
      } finally {
        setLoading(false);
      }
    };

    fetchActivityLog();
  }, [page]);

  if (loading) return <LoadingSpinner />;

  const getActionIcon = (action: string) => {
    switch (action?.toLowerCase()) {
      case 'create':
        return { Icon: Plus, color: 'bg-green-100 text-green-700' };
      case 'update':
        return { Icon: Edit2, color: 'bg-blue-100 text-blue-700' };
      case 'delete':
        return { Icon: Trash2, color: 'bg-red-100 text-red-700' };
      case 'ban':
        return { Icon: Ban, color: 'bg-yellow-100 text-yellow-700' };
      case 'flag':
        return { Icon: Flag, color: 'bg-purple-100 text-purple-700' };
      default:
        return { Icon: CircleDot, color: 'bg-gray-100 text-gray-700' };
    }
  };

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Activity Log</h2>

      <div className="space-y-3">
        {activities.map((activity, index) => (
          <div key={`activity-${index}-${activity.id}`} className="rounded-lg border border-gray-200 bg-white p-4">
            <div className="flex items-center justify-between">
              <div className="flex-1">
                <div className="flex items-center gap-3">
                  {(() => {
                    const { Icon, color } = getActionIcon(activity.action);
                    return (
                      <div className={`inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ${color}`}>
                        <Icon size={14} />
                        {activity.action}
                      </div>
                    );
                  })()}
                  <span className="text-sm text-gray-600">
                    Admin {activity.admin_id || 'Unknown'}
                  </span>
                </div>
                <p className="mt-2 text-sm text-gray-700">
                  {activity.target_type && activity.target_id && (
                    <span>
                      <strong>{activity.target_type}</strong> #{activity.target_id}
                    </span>
                  )}
                </p>
                {activity.details && (
                  <p className="mt-1 text-sm text-gray-600">
                    {activity.details}
                  </p>
                )}
              </div>
              <span className="text-xs text-gray-500">
                {new Date(activity.created_at || '').toLocaleString()}
              </span>
            </div>
          </div>
        ))}
      </div>

      {activities.length === 0 && (
        <div className="text-center text-gray-600">
          No activity log entries
        </div>
      )}

      <div className="flex items-center justify-between">
        <button
          onClick={() => setPage(p => Math.max(1, p - 1))}
          disabled={page === 1}
          className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:bg-gray-400"
        >
          Previous
        </button>
        <span className="text-gray-600">Page {page}</span>
        <button
          onClick={() => setPage(p => p + 1)}
          className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
        >
          Next
        </button>
      </div>
    </div>
  );
}
