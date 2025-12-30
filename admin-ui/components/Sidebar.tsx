'use client';

import {
  BarChart3,
  Users,
  FileText,
  AlertTriangle,
  Clock,
  Settings,
  CheckCircle,
} from 'lucide-react';

interface SidebarProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const tabs = [
  { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
  { id: 'users', label: 'Users', icon: Users },
  { id: 'posts', label: 'Posts', icon: FileText },
  { id: 'flagged', label: 'Flagged Content', icon: AlertTriangle },
  { id: 'verification', label: 'Verifications', icon: CheckCircle },
  { id: 'activity', label: 'Activity Log', icon: Clock },
  { id: 'settings', label: 'Settings', icon: Settings },
];

export default function Sidebar({ activeTab, onTabChange }: SidebarProps) {
  return (
    <aside className="w-64 border-r border-gray-200 bg-white">
      <div className="space-y-4 py-4">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          return (
            <button
              key={tab.id}
              onClick={() => onTabChange(tab.id)}
              className={`w-full px-6 py-3 text-left transition-colors flex items-center gap-3 ${
                activeTab === tab.id
                  ? 'border-r-4 border-blue-600 bg-blue-50 font-medium text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50'
              }`}
            >
              <Icon size={18} />
              {tab.label}
            </button>
          );
        })}
      </div>
    </aside>
  );
}