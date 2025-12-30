'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { listFlaggedContent, deletePost, unflagPost } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface FlaggedItem {
  id: string;
  post_id?: string;
  title?: string;
  content?: string;
  reason?: string;
  flagged_at?: string;
}

export default function FlaggedContent() {
  const [items, setItems] = useState<FlaggedItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [actionLoading, setActionLoading] = useState<string | null>(null);

  useEffect(() => {
    const fetchFlaggedContent = async () => {
      try {
        const data = await listFlaggedContent(page, 10);
        setItems(data.data || []);
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load flagged content');
      } finally {
        setLoading(false);
      }
    };

    fetchFlaggedContent();
  }, [page]);

  const handleUnflag = async (itemId: string) => {
    setActionLoading(itemId);
    try {
      await unflagPost(itemId);
      setItems(items.filter(i => i.id !== itemId));
      toast.success('Content unflagged successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to unflag content');
    } finally {
      setActionLoading(null);
    }
  };

  const handleDelete = async (itemId: string) => {
    if (!confirm('Are you sure you want to delete this content?')) return;

    setActionLoading(itemId);
    try {
      await deletePost(itemId);
      setItems(items.filter(i => i.id !== itemId));
      toast.success('Content deleted successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to delete content');
    } finally {
      setActionLoading(null);
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Flagged Content</h2>

      <div className="space-y-4">
        {items.map((item, index) => (
          <div key={`flagged-${index}-${item.id}`} className="rounded-lg border-2 border-red-200 bg-red-50 p-4">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <h3 className="font-semibold text-gray-900">{item.title || 'Untitled'}</h3>
                  <span className="inline-block rounded-full bg-red-200 px-2 py-1 text-xs font-medium text-red-700">
                    Flagged
                  </span>
                </div>
                <p className="mt-1 text-sm text-gray-600 line-clamp-2">
                  {item.content || 'No content'}
                </p>
                <div className="mt-2 space-y-1 text-xs text-gray-600">
                  <p><strong>Reason:</strong> {item.reason || 'No reason provided'}</p>
                  <p><strong>Flagged:</strong> {new Date(item.flagged_at || '').toLocaleString()}</p>
                </div>
              </div>
            </div>
            <div className="mt-4 flex gap-2">
              <button
                onClick={() => handleUnflag(item.id)}
                disabled={actionLoading === item.id}
                className="rounded bg-blue-100 px-3 py-1 text-sm text-blue-700 hover:bg-blue-200 disabled:opacity-50"
              >
                Unflag
              </button>
              <button
                onClick={() => handleDelete(item.id)}
                disabled={actionLoading === item.id}
                className="rounded bg-red-100 px-3 py-1 text-sm text-red-700 hover:bg-red-200 disabled:opacity-50"
              >
                Delete
              </button>
            </div>
          </div>
        ))}
      </div>

      {items.length === 0 && (
        <div className="text-center text-gray-600">
          No flagged content
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
