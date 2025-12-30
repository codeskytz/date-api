// lib/api.ts

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
const API_VERSION = process.env.NEXT_PUBLIC_API_VERSION || 'v1';

let adminToken: string | null = null;

export const setAdminToken = (token: string) => {
  adminToken = token;
  if (typeof window !== 'undefined') {
    localStorage.setItem('adminToken', token);
  }
};

export const getAdminToken = () => {
  if (adminToken) return adminToken;
  if (typeof window !== 'undefined') {
    adminToken = localStorage.getItem('adminToken');
  }
  return adminToken;
};

export const clearAdminToken = () => {
  adminToken = null;
  if (typeof window !== 'undefined') {
    localStorage.removeItem('adminToken');
  }
};

const getHeaders = () => {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  const token = getAdminToken();
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return headers;
};

const apiCall = async (
  endpoint: string,
  method: string = 'GET',
  body?: Record<string, any>
) => {
  const url = `${API_BASE_URL}/api/${API_VERSION}${endpoint}`;
  
  const response = await fetch(url, {
    method,
    headers: getHeaders(),
    body: body ? JSON.stringify(body) : undefined,
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || `HTTP ${response.status}`);
  }

  return data;
};

// Admin Auth
export const adminLogin = async (email: string, password: string) => {
  const response = await apiCall('/admin/login', 'POST', { email, password });
  if (response.token) {
    setAdminToken(response.token);
  }
  return response;
};

// Dashboard
export const getAdminDashboard = () => apiCall('/admin/dashboard');

// Statistics
export const getAdminStatistics = () => apiCall('/admin/statistics');

// Users
export const listUsers = (page: number = 1, perPage: number = 15) =>
  apiCall(`/admin/users?page=${page}&per_page=${perPage}`);

export const getUser = (userId: number) => apiCall(`/admin/users/${userId}`);

export const updateUser = (userId: number, data: Record<string, any>) =>
  apiCall(`/admin/users/${userId}`, 'PUT', data);

export const banUser = (userId: number, reason: string) =>
  apiCall(`/admin/users/${userId}/ban`, 'POST', { reason });

export const unbanUser = (userId: number) =>
  apiCall(`/admin/users/${userId}/unban`, 'POST');

export const deleteUser = (userId: number) =>
  apiCall(`/admin/users/${userId}`, 'DELETE');

// Posts
export const listPosts = (page: number = 1, perPage: number = 15) =>
  apiCall(`/admin/posts?page=${page}&per_page=${perPage}`);

export const getPost = (postId: number) => apiCall(`/admin/posts/${postId}`);

export const deletePost = (postId: number) =>
  apiCall(`/admin/posts/${postId}`, 'DELETE');

export const flagPost = (postId: number, reason: string) =>
  apiCall(`/admin/posts/${postId}/flag`, 'POST', { reason });

export const unflagPost = (postId: number) =>
  apiCall(`/admin/posts/${postId}/unflag`, 'POST');

// Flagged Content
export const listFlaggedContent = (page: number = 1, perPage: number = 15) =>
  apiCall(`/admin/flagged-content?page=${page}&per_page=${perPage}`);

// Activity Log
export const getActivityLog = (page: number = 1, perPage: number = 15) =>
  apiCall(`/admin/activity-log?page=${page}&per_page=${perPage}`);

// Settings
export const getAdminSettings = () => apiCall('/admin/settings');

export const updateAdminSettings = (settings: Record<string, any>) =>
  apiCall('/admin/settings', 'PUT', settings);
