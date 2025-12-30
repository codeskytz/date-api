'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { CheckCircle, XCircle, Clock, Eye, ChevronRight, X, Download } from 'lucide-react';
import LoadingSpinner from './LoadingSpinner';

interface VerificationRequest {
  id: number;
  request_id: number;
  user_id: number;
  type: 'identity' | 'business' | 'creator';
  status: 'pending' | 'approved' | 'rejected' | 'cancelled';
  full_name: string;
  document_type: string;
  submitted_at: string;
  reviewed_at: string | null;
  rejection_reason: string | null;
  document_front?: string;
  document_back?: string;
  selfie?: string;
  user?: {
    id: number;
    username: string;
    name: string;
    email: string;
  };
}

interface PaginationData {
  total: number;
  page: number;
  limit: number;
  pages: number;
}

type FilterStatus = 'pending' | 'approved' | 'rejected' | 'all';

export default function VerificationManagement() {
  const [requests, setRequests] = useState<VerificationRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedRequest, setSelectedRequest] = useState<VerificationRequest | null>(null);
  const [detailLoading, setDetailLoading] = useState(false);
  const [pagination, setPagination] = useState<PaginationData>({ total: 0, page: 1, limit: 20, pages: 1 });
  const [rejectionReason, setRejectionReason] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [filterStatus, setFilterStatus] = useState<FilterStatus>('pending');
  const [previewDoc, setPreviewDoc] = useState<{ url: string; title: string } | null>(null);

  const token = localStorage.getItem('admin_token');

  useEffect(() => {
    fetchRequests();
  }, [currentPage, filterStatus]);

  const fetchRequests = async () => {
    try {
      setLoading(true);
      let endpoint = 'http://localhost:8000/api/v1/admin/verification';

      if (filterStatus !== 'all') {
        endpoint += `/${filterStatus}`;
      } else {
        endpoint += '/all';
      }

      endpoint += `?page=${currentPage}&limit=20`;

      const response = await fetch(endpoint, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to fetch verification requests');

      const data = await response.json();
      setRequests(data.data || []);
      setPagination(data.pagination || { total: 0, page: 1, limit: 20, pages: 1 });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to load verification requests');
    } finally {
      setLoading(false);
    }
  };

  const fetchRequestDetails = async (requestId: number) => {
    try {
      setDetailLoading(true);
      const response = await fetch(`http://localhost:8000/api/v1/admin/verification/${requestId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to fetch request details');

      const data = await response.json();
      setSelectedRequest(data.data || data);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to load request details');
    } finally {
      setDetailLoading(false);
    }
  };

  const handleApprove = async () => {
    if (!selectedRequest) return;

    try {
      setActionLoading(true);
      const response = await fetch(`http://localhost:8000/api/v1/admin/verification/${selectedRequest.id}/approve`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({}),
      });

      if (!response.ok) throw new Error('Failed to approve verification');

      toast.success('Verification approved successfully');
      setSelectedRequest(null);
      fetchRequests();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to approve verification');
    } finally {
      setActionLoading(false);
    }
  };

  const handleReject = async () => {
    if (!selectedRequest || !rejectionReason.trim()) {
      toast.error('Please provide a rejection reason');
      return;
    }

    try {
      setActionLoading(true);
      const response = await fetch(`http://localhost:8000/api/v1/admin/verification/${selectedRequest.id}/reject`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: rejectionReason }),
      });

      if (!response.ok) throw new Error('Failed to reject verification');

      toast.success('Verification rejected successfully');
      setSelectedRequest(null);
      setRejectionReason('');
      fetchRequests();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to reject verification');
    } finally {
      setActionLoading(false);
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'identity':
        return 'bg-blue-100 text-blue-800';
      case 'business':
        return 'bg-green-100 text-green-800';
      case 'creator':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending':
        return <Clock className="text-yellow-500" size={18} />;
      case 'approved':
        return <CheckCircle className="text-green-500" size={18} />;
      case 'rejected':
        return <XCircle className="text-red-500" size={18} />;
      default:
        return null;
    }
  };

  if (loading && !selectedRequest) return <LoadingSpinner />;

  if (selectedRequest) {
    return (
      <div className="space-y-6">
        <button
          onClick={() => {
            setSelectedRequest(null);
            setRejectionReason('');
          }}
          className="text-blue-600 hover:text-blue-800 flex items-center gap-2"
        >
          ← Back to Requests
        </button>

        <div className="bg-white rounded-lg shadow-lg p-6">
          {detailLoading ? (
            <LoadingSpinner />
          ) : (
            <>
              <div className="grid grid-cols-2 gap-6 mb-8">
                <div>
                  <p className="text-gray-600 text-sm">User Information</p>
                  <p className="text-xl font-bold mt-2">{selectedRequest.user?.name || 'N/A'}</p>
                  <p className="text-gray-600">@{selectedRequest.user?.username}</p>
                  <p className="text-gray-600 text-sm mt-2">{selectedRequest.user?.email}</p>
                </div>

                <div>
                  <p className="text-gray-600 text-sm">Verification Details</p>
                  <div className="mt-2 space-y-2">
                    <p className="text-sm">
                      <span className="font-semibold">Type:</span>{' '}
                      <span className={`inline-block px-3 py-1 rounded text-xs font-semibold ${getTypeColor(selectedRequest.type)}`}>
                        {selectedRequest.type.toUpperCase()}
                      </span>
                    </p>
                    <p className="text-sm">
                      <span className="font-semibold">Full Name:</span> {selectedRequest.full_name}
                    </p>
                    <p className="text-sm">
                      <span className="font-semibold">Document Type:</span> {selectedRequest.document_type}
                    </p>
                  </div>
                </div>
              </div>

              <div className="border-t pt-6 mb-6">
                <p className="text-gray-900 text-sm font-semibold mb-4">Documents</p>
                <div className="grid grid-cols-3 gap-4">
                  {selectedRequest.document_front && (
                    <div className="border border-gray-200 rounded-lg p-3">
                      <p className="text-xs text-gray-700 font-semibold mb-3">Front Document</p>
                      <button
                        onClick={() => setPreviewDoc({ url: selectedRequest.document_front!, title: 'Front Document' })}
                        className="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold text-sm mb-2"
                      >
                        <Eye size={16} /> Preview
                      </button>
                      <a
                        href={selectedRequest.document_front}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-semibold text-sm"
                      >
                        <Download size={16} /> Download
                      </a>
                    </div>
                  )}
                  {selectedRequest.document_back && (
                    <div className="border border-gray-200 rounded-lg p-3">
                      <p className="text-xs text-gray-700 font-semibold mb-3">Back Document</p>
                      <button
                        onClick={() => setPreviewDoc({ url: selectedRequest.document_back!, title: 'Back Document' })}
                        className="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold text-sm mb-2"
                      >
                        <Eye size={16} /> Preview
                      </button>
                      <a
                        href={selectedRequest.document_back}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-semibold text-sm"
                      >
                        <Download size={16} /> Download
                      </a>
                    </div>
                  )}
                  {selectedRequest.selfie && (
                    <div className="border border-gray-200 rounded-lg p-3">
                      <p className="text-xs text-gray-700 font-semibold mb-3">Selfie</p>
                      <button
                        onClick={() => setPreviewDoc({ url: selectedRequest.selfie!, title: 'Selfie' })}
                        className="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold text-sm mb-2"
                      >
                        <Eye size={16} /> Preview
                      </button>
                      <a
                        href={selectedRequest.selfie}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-semibold text-sm"
                      >
                        <Download size={16} /> Download
                      </a>
                    </div>
                  )}
                </div>
              </div>

              {selectedRequest.status === 'pending' && (
                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                  <p className="text-yellow-800 font-semibold">Decision Required</p>
                  <p className="text-yellow-700 text-sm mt-1">
                    This verification request is pending review. Please approve or reject it below.
                  </p>
                </div>
              )}

              {selectedRequest.status === 'approved' && (
                <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                  <p className="text-green-800 font-semibold">✓ Approved</p>
                  <p className="text-green-700 text-sm mt-1">
                    This verification was approved on {new Date(selectedRequest.reviewed_at || '').toLocaleDateString()}
                  </p>
                </div>
              )}

              {selectedRequest.status === 'rejected' && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                  <p className="text-red-800 font-semibold">✗ Rejected</p>
                  <p className="text-red-900 font-medium text-sm mt-1">
                    <strong>Reason:</strong> {selectedRequest.rejection_reason}
                  </p>
                </div>
              )}

              {selectedRequest.status === 'pending' && (
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Rejection Reason (if rejecting)
                    </label>
                    <textarea
                      value={rejectionReason}
                      onChange={(e) => setRejectionReason(e.target.value)}
                      placeholder="Enter reason for rejection..."
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      rows={3}
                    />
                  </div>

                  <div className="flex gap-3">
                    <button
                      onClick={handleApprove}
                      disabled={actionLoading}
                      className="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                    >
                      <CheckCircle size={18} />
                      Approve Verification
                    </button>

                    <button
                      onClick={handleReject}
                      disabled={actionLoading || !rejectionReason.trim()}
                      className="flex-1 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                    >
                      <XCircle size={18} />
                      Reject Verification
                    </button>
                  </div>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-900">Profile Verification Management</h2>
        <div className="text-sm text-gray-600">
          Total: <span className="font-bold text-blue-600">{pagination.total}</span>
        </div>
      </div>

      {/* Filter Tabs */}
      <div className="bg-white rounded-lg shadow border border-gray-200">
        <div className="flex gap-0">
          {[
            { id: 'pending' as FilterStatus, label: 'Pending', icon: '⏱️' },
            { id: 'approved' as FilterStatus, label: 'Approved', icon: '✓' },
            { id: 'rejected' as FilterStatus, label: 'Rejected', icon: '✗' },
            { id: 'all' as FilterStatus, label: 'All History', icon: '📋' },
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => {
                setFilterStatus(tab.id);
                setCurrentPage(1);
              }}
              className={`flex-1 px-6 py-4 font-semibold transition-colors border-b-2 ${
                filterStatus === tab.id
                  ? 'border-blue-600 bg-blue-50 text-blue-600'
                  : 'border-transparent text-gray-700 hover:bg-gray-50'
              }`}
            >
              <span className="mr-2">{tab.icon}</span>
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {requests.length === 0 ? (
        <div className="bg-white rounded-lg shadow p-8 text-center">
          <Clock size={48} className="mx-auto text-gray-400 mb-4" />
          <p className="text-gray-600">No {filterStatus === 'all' ? 'verification' : filterStatus} verification requests</p>
        </div>
      ) : (
        <>
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">User</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">
                    {filterStatus === 'pending' ? 'Submitted' : 'Reviewed'}
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Docs</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {requests.map((request) => (
                  <tr key={request.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4">
                      <div>
                        <p className="font-semibold text-gray-900">{request.user?.name || 'Unknown'}</p>
                        <p className="text-sm text-gray-600">@{request.user?.username}</p>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className={`inline-block px-3 py-1 rounded text-xs font-semibold ${getTypeColor(request.type)}`}>
                        {request.type.toUpperCase()}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-2">
                        {getStatusIcon(request.status)}
                        <span className="text-sm font-semibold capitalize text-gray-700">{request.status}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600">
                      {filterStatus === 'pending'
                        ? `${new Date(request.submitted_at).toLocaleDateString()} ${new Date(request.submitted_at).toLocaleTimeString()}`
                        : `${new Date(request.reviewed_at || request.submitted_at).toLocaleDateString()} ${new Date(request.reviewed_at || request.submitted_at).toLocaleTimeString()}`}
                    </td>
                    <td className="px-6 py-4">
                      {(request.document_front || request.document_back || request.selfie) ? (
                        <span className="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">
                          <Eye size={14} /> {[request.document_front, request.document_back, request.selfie].filter(Boolean).length}
                        </span>
                      ) : (
                        <span className="text-gray-400 text-xs">—</span>
                      )}
                    </td>
                    <td className="px-6 py-4">
                      <button
                        onClick={() => fetchRequestDetails(request.id)}
                        className="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 text-sm"
                      >
                        Review <ChevronRight size={16} />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {pagination.pages > 1 && (
            <div className="flex justify-center gap-2">
              {Array.from({ length: pagination.pages }, (_, i) => i + 1).map((page) => (
                <button
                  key={page}
                  onClick={() => setCurrentPage(page)}
                  className={`px-4 py-2 rounded-lg transition-colors ${
                    currentPage === page
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                  }`}
                >
                  {page}
                </button>
              ))}
            </div>
          )}
        </>
      )}

      {/* Document Preview Modal */}
      {previewDoc && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-2xl max-w-4xl max-h-screen overflow-auto">
            <div className="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
              <h3 className="text-lg font-bold text-gray-900">{previewDoc.title}</h3>
              <button
                onClick={() => setPreviewDoc(null)}
                className="text-gray-500 hover:text-gray-700 p-1"
              >
                <X size={24} />
              </button>
            </div>
            
            <div className="p-6">
              {previewDoc.url.toLowerCase().endsWith('.pdf') ? (
                <iframe
                  src={previewDoc.url}
                  className="w-full h-96 border border-gray-300 rounded-lg"
                  title={previewDoc.title}
                />
              ) : (
                <img
                  src={previewDoc.url}
                  alt={previewDoc.title}
                  className="w-full max-h-96 object-contain rounded-lg border border-gray-300"
                />
              )}
              
              <div className="mt-4 flex gap-3">
                <a
                  href={previewDoc.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <Download size={18} />
                  Download Document
                </a>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
