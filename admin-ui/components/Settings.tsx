'use client';

import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getAdminSettings, updateAdminSettings } from '@/lib/api';
import LoadingSpinner from './LoadingSpinner';

interface Settings {
  app_name?: string;
  app_url?: string;
  app_env?: string;
  app_debug?: boolean;
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
      <div>
        <h2 className="text-2xl font-bold text-gray-900">Admin Settings</h2>
        <p className="mt-1 text-sm text-gray-600">Manage your application settings. Changes are saved to the database.</p>
      </div>

      <form onSubmit={(e) => { e.preventDefault(); handleSave(); }} className="space-y-6 bg-white rounded-lg p-6 shadow">
        {/* Application Name */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Application Name
          </label>
          <p className="mt-0.5 text-xs text-gray-500">The name of your dating application</p>
          <input
            type="text"
            value={settings.app_name || ''}
            onChange={(e) => handleInputChange('app_name', e.target.value)}
            className="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            placeholder="e.g., Dating App Pro"
          />
        </div>

        {/* Application URL */}
        <div>
          <label className="block text-sm font-medium text-gray-700">
            Application URL
          </label>
          <p className="mt-0.5 text-xs text-gray-500">The main URL of your application</p>
          <input
            type="url"
            value={settings.app_url || ''}
            onChange={(e) => handleInputChange('app_url', e.target.value)}
            className="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500"
            placeholder="e.g., https://example.com"
          />
        </div>

        <div className="border-t border-gray-200 pt-6">
          <h3 className="text-sm font-medium text-gray-900 mb-4">System Information (Read-only)</h3>
          
          {/* Environment (Read-only) */}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700">
              Environment
            </label>
            <p className="mt-0.5 text-xs text-gray-500">Current application environment</p>
            <input
              type="text"
              value={settings.app_env || ''}
              disabled
              className="mt-2 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-600 cursor-not-allowed"
            />
          </div>

          {/* Debug Mode (Read-only) */}
          <div>
            <label className="block text-sm font-medium text-gray-700">
              Debug Mode
            </label>
            <p className="mt-0.5 text-xs text-gray-500">Shows detailed error messages and debugging information</p>
            <div className="mt-2 rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-gray-600 font-medium">
              {settings.app_debug ? (
                <span className="text-red-600">🔴 Enabled</span>
              ) : (
                <span className="text-green-600">🟢 Disabled</span>
              )}
            </div>
          </div>
        </div>

        {/* Save Button */}
        <div className="flex gap-3 pt-4">
          <button
            type="submit"
            disabled={saving}
            className="flex-1 rounded-lg bg-blue-600 py-2 px-4 text-white font-medium hover:bg-blue-700 disabled:bg-blue-400 transition-colors"
          >
            {saving ? 'Saving...' : 'Save Settings'}
          </button>
        </div>
      </form>
    </div>
  );
}
