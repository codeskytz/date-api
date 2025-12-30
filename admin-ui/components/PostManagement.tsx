'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { listPosts, deletePost, flagPost, unflagPost } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface Post {
  id: string;
  title?: string;
  content?: string;
  user_id?: string;
  is_flagged?: boolean;
  created_at?: string;
}

export default function PostManagement() {
  const [posts, setPosts] = useState<Post[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [actionLoading, setActionLoading] = useState<string | null>(null);

  useEffect(() => {
    const fetchPosts = async () => {
      try {
        const data = await listPosts(page, 10);
        setPosts(data.data || []);
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load posts');
      } finally {
        setLoading(false);
      }
    };

    fetchPosts();
  }, [page]);

  const handleFlag = async (postId: string) => {
    const reason = prompt('Enter flag reason:');
    if (!reason) return;

    setActionLoading(postId);
    try {
      await flagPost(postId, reason);
      setPosts(posts.map(p => p.id === postId ? { ...p, is_flagged: true } : p));
      toast.success('Post flagged successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to flag post');
    } finally {
      setActionLoading(null);
    }
  };

  const handleUnflag = async (postId: string) => {
    setActionLoading(postId);
    try {
      await unflagPost(postId);
      setPosts(posts.map(p => p.id === postId ? { ...p, is_flagged: false } : p));
      toast.success('Post unflagged successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to unflag post');
    } finally {
      setActionLoading(null);
    }
  };

  const handleDelete = async (postId: string) => {
    if (!confirm('Are you sure you want to delete this post?')) return;

    setActionLoading(postId);
    try {
      await deletePost(postId);
      setPosts(posts.filter(p => p.id !== postId));
      toast.success('Post deleted successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to delete post');
    } finally {
      setActionLoading(null);
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Post Management</h2>

      <div className="space-y-4">
        {posts.map((post, index) => (
          <div key={`post-${index}-${post.id}`} className="rounded-lg border border-gray-200 bg-white p-4">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <h3 className="font-semibold text-gray-900">{post.title || 'Untitled'}</h3>
                <p className="mt-1 text-sm text-gray-600 line-clamp-2">
                  {post.content || 'No content'}
                </p>
                <div className="mt-2 flex items-center gap-4 text-xs text-gray-500">
                  <span>By User {post.user_id}</span>
                  <span>{new Date(post.created_at || '').toLocaleDateString()}</span>
                </div>
              </div>
              <div className="ml-4 flex flex-col gap-2">
                {post.is_flagged && (
                  <span className="inline-block rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                    Flagged
                  </span>
                )}
              </div>
            </div>
            <div className="mt-4 flex gap-2">
              {post.is_flagged ? (
                <button
                  onClick={() => handleUnflag(post.id)}
                  disabled={actionLoading === post.id}
                  className="rounded bg-green-100 px-3 py-1 text-sm text-green-700 hover:bg-green-200 disabled:opacity-50"
                >
                  Unflag
                </button>
              ) : (
                <button
                  onClick={() => handleFlag(post.id)}
                  disabled={actionLoading === post.id}
                  className="rounded bg-yellow-100 px-3 py-1 text-sm text-yellow-700 hover:bg-yellow-200 disabled:opacity-50"
                >
                  Flag
                </button>
              )}
              <button
                onClick={() => handleDelete(post.id)}
                disabled={actionLoading === post.id}
                className="rounded bg-red-100 px-3 py-1 text-sm text-red-700 hover:bg-red-200 disabled:opacity-50"
              >
                Delete
              </button>
            </div>
          </div>
        ))}
      </div>

      {posts.length === 0 && (
        <div className="text-center text-gray-600">
          No posts found
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
