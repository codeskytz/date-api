// lib/auth-context.tsx
'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import { getAdminToken, clearAdminToken } from './api';

interface AuthContextType {
  isAuthenticated: boolean;
  token: string | null;
  setToken: (token: string) => void;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [token, setTokenState] = useState<string | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    const savedToken = getAdminToken();
    if (savedToken) {
      setIsAuthenticated(true);
      setTokenState(savedToken);
    }
    setIsLoaded(true);
  }, []);

  const setToken = (newToken: string) => {
    setTokenState(newToken);
    setIsAuthenticated(true);
  };

  const logout = () => {
    clearAdminToken();
    setTokenState(null);
    setIsAuthenticated(false);
  };

  if (!isLoaded) {
    return <div className="flex items-center justify-center h-screen">Loading...</div>;
  }

  return (
    <AuthContext.Provider value={{ isAuthenticated, token, setToken, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
