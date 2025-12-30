'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getAdminSettings, updateAdminSettings } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface Settings {
  site_name?: string;
  site_url?: string;
  max_upload_size?: number;
  maintenance_mode?: boolean;
  email_notifications?: boolean;
  [key: string]: any;
}

export default function Settings() {
  const [settings, setSettings] = useState<Settings | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const data = await getAdminSettings();
        setSettings(data);
      } catch (err) {
        toast.error(err instanceof Error ? err.message : 'Failed to load settings');
      } finally {
        setLoading(false);
      }
    };

    fetchSettings();
  }, []);

  const handleInputChange = (key: string, value: any) => {
    setSettings(prev => prev ? { ...prev, [key]: value } : null);
  };

  const handleSave = async () => {
    if (!settings) return;

    setSaving(true);

    try {
      // Only send fields that are editable and allowed by the backend
      const allowedSettings = {
        app_name: settings.app_name,
        app_url: settings.app_url,
      };
      await updateAdminSettings(allowedSettings);
      toast.success('Settings updated successfully');
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed to save settings');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <LoadingSpinner />;

  if (!settings) {
    return (
      <div className="text-center text-gray-600">
        Failed to load settings
      </div>
    );
  }

  return (
    <div className="max-w-2xl space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Admin Settings</h2>

      <form onSubmit={(e) => { e.preventDefault(); handleSave(); }} className="space-y-6">
        {/* Site Name */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Site Name
          </label>
          <input
            type="text"
            value={settings.app_name || ''}
            onChange={(e) => handleInputChange('app_name', e.target.value)}
            className="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500"
          />
        </div>

        {/* Site URL */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Site URL
          </label>
          <input
            type="url"
            value={settings.app_url || ''}
            onChange={(e) => handleInputChange('app_url', e.target.value)}
            className="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500"
          />
        </div>

        {/* Environment (Read-only) */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Environment
          </label>
          <input
            type="text"
            value={settings.app_env || ''}
            disabled
            className="mt-2 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-600 cursor-not-allowed"
          />
          <p className="mt-1 text-xs text-gray-500">This value is read-only</p>
        </div>

        {/* Debug Mode (Read-only) */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Debug Mode
          </label>
          <div className="mt-2 rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-gray-600">
            {settings.app_debug ? 'Enabled' : 'Disabled'}
          </div>
          <p className="mt-1 text-xs text-gray-500">This value is read-only</p>
        </div>

        {/* Save Button */}
        <button
          type="submit"
          disabled={saving}
          className="w-full rounded-lg bg-blue-600 py-2 px-4 text-white font-medium hover:bg-blue-700 disabled:bg-blue-400 transition-colors"
        >
          {saving ? 'Saving...' : 'Save Settings'}
        </button>
      </form>
    </div>
  );
}
