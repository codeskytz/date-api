'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { listUsers, banUser, unbanUser, deleteUser } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface User {
  id: string;
  name?: string;
  email: string;
  is_banned?: boolean;
  created_at?: string;
}

export default function UserManagement() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [actionLoading, setActionLoading] = useState<string | null>(null);

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        const data = await listUsers(page, 10);
        setUsers(data.data || []);
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load users');
      } finally {
        setLoading(false);
      }
    };

    fetchUsers();
  }, [page]);

  const handleBan = async (userId: string) => {
    const reason = prompt('Enter ban reason:');
    if (!reason) return;

    setActionLoading(userId);
    try {
      await banUser(userId, reason);
      setUsers(users.map(u => u.id === userId ? { ...u, is_banned: true } : u));
      toast.success('User banned successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to ban user');
    } finally {
      setActionLoading(null);
    }
  };

  const handleUnban = async (userId: string) => {
    setActionLoading(userId);
    try {
      await unbanUser(userId);
      setUsers(users.map(u => u.id === userId ? { ...u, is_banned: false } : u));
      toast.success('User unbanned successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to unban user');
    } finally {
      setActionLoading(null);
    }
  };

  const handleDelete = async (userId: string) => {
    if (!confirm('Are you sure you want to delete this user?')) return;

    setActionLoading(userId);
    try {
      await deleteUser(userId);
      setUsers(users.filter(u => u.id !== userId));
      toast.success('User deleted successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to delete user');
    } finally {
      setActionLoading(null);
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">User Management</h2>

      <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table className="w-full">
          <thead className="border-b border-gray-200 bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Email</th>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map((user, index) => (
              <tr key={`user-${index}-${user.id}`} className="border-b border-gray-200 hover:bg-gray-50">
                <td className="px-6 py-4 text-sm text-gray-900">{user.name || 'N/A'}</td>
                <td className="px-6 py-4 text-sm text-gray-600">{user.email}</td>
                <td className="px-6 py-4 text-sm">
                  <span className={`inline-block px-3 py-1 rounded-full text-xs font-medium ${
                    user.is_banned
                      ? 'bg-red-100 text-red-700'
                      : 'bg-green-100 text-green-700'
                  }`}>
                    {user.is_banned ? 'Banned' : 'Active'}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm space-x-2">
                  {user.is_banned ? (
                    <button
                      onClick={() => handleUnban(user.id)}
                      disabled={actionLoading === user.id}
                      className="rounded bg-green-100 px-2 py-1 text-green-700 hover:bg-green-200 disabled:opacity-50"
                    >
                      Unban
                    </button>
                  ) : (
                    <button
                      onClick={() => handleBan(user.id)}
                      disabled={actionLoading === user.id}
                      className="rounded bg-yellow-100 px-2 py-1 text-yellow-700 hover:bg-yellow-200 disabled:opacity-50"
                    >
                      Ban
                    </button>
                  )}
                  <button
                    onClick={() => handleDelete(user.id)}
                    disabled={actionLoading === user.id}
                    className="rounded bg-red-100 px-2 py-1 text-red-700 hover:bg-red-200 disabled:opacity-50"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {users.length === 0 && (
        <div className="text-center text-gray-600">
          No users found
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
