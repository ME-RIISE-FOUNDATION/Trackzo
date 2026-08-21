import { Bell, Search, ChevronDown, Plus } from 'lucide-react';
import type { NavPage } from '../types';

const pageLabels: Record<NavPage, string> = {
  dashboard: 'Dashboard',
  projects: 'Projects',
  clients: 'Clients',
  estimation: 'Estimation',
  materials: 'Materials & Inventory',
  purchase: 'Purchase Orders',
  finance: 'Finance',
  reports: 'Reports',
  calendar: 'Calendar',
  'account-tracker': 'Account Tracker',
  settings: 'Settings',
};

interface TopBarProps {
  page: NavPage;
  onAction?: () => void;
  actionLabel?: string;
  notifications?: number;
}

export default function TopBar({ page, onAction, actionLabel, notifications = 3 }: TopBarProps) {
  const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

  return (
    <header className="h-16 bg-white border-b border-slate-200 flex items-center px-6 gap-4 flex-shrink-0" style={{ boxShadow: '0 1px 4px rgba(0,0,0,0.06)' }}>
      {/* Page title */}
      <div className="flex-1 min-w-0">
        <h1 className="text-lg font-bold text-slate-900 truncate" style={{ fontFamily: 'var(--font-display)' }}>
          {pageLabels[page]}
        </h1>
        <p className="text-xs text-slate-400 hidden sm:block">{today}</p>
      </div>

      {/* Search */}
      <div className="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 w-64">
        <Search size={15} className="text-slate-400 flex-shrink-0" />
        <input
          type="text"
          placeholder="Search anything..."
          className="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full"
        />
      </div>

      {/* Notifications */}
      <button className="relative w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
        <Bell size={17} />
        {notifications > 0 && (
          <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white font-bold flex items-center justify-center">
            {notifications}
          </span>
        )}
      </button>

      {/* Action button */}
      {onAction && actionLabel && (
        <button
          onClick={onAction}
          className="hidden sm:flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
        >
          <Plus size={16} />
          {actionLabel}
        </button>
      )}

      {/* User chip */}
      <div className="flex items-center gap-2 cursor-pointer group">
        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
          JC
        </div>
        <ChevronDown size={14} className="text-slate-400 group-hover:text-slate-600 transition-colors hidden sm:block" />
      </div>
    </header>
  );
}
