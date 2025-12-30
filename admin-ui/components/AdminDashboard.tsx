'use client';

import { useState } from 'react';
import Sidebar from './Sidebar';
import Navbar from './Navbar';
import Dashboard from './Dashboard';
import UserManagement from './UserManagement';
import PostManagement from './PostManagement';
import FlaggedContent from './FlaggedContent';
import ActivityLog from './ActivityLog';
import Settings from './Settings';

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState('dashboard');

  const renderContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return <Dashboard />;
      case 'users':
        return <UserManagement />;
      case 'posts':
        return <PostManagement />;
      case 'flagged':
        return <FlaggedContent />;
      case 'activity':
        return <ActivityLog />;
      case 'settings':
        return <Settings />;
      default:
        return <Dashboard />;
    }
  };

  const getPageTitle = () => {
    const titles: { [key: string]: string } = {
      dashboard: 'Dashboard',
      users: 'User Management',
      posts: 'Post Management',
      flagged: 'Flagged Content',
      activity: 'Activity Log',
      settings: 'Settings',
    };
    return titles[activeTab] || 'Dashboard';
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar title={getPageTitle()} />
      <div className="flex">
        <Sidebar activeTab={activeTab} onTabChange={setActiveTab} />
        <main className="flex-1 p-8">
          {renderContent()}
        </main>
      </div>
    </div>
  );
}
