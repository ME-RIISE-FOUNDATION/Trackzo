import {
  LayoutDashboard, FolderOpen, Users, Calculator, Package,
  ShoppingCart, DollarSign, BarChart2, CalendarDays,
  CreditCard, Settings, ChevronLeft, ChevronRight,
  HardHat, Bell, LogOut
} from 'lucide-react';
import type { NavPage } from '../types';

const navItems: { id: NavPage; label: string; icon: React.FC<{ size?: number; className?: string }> }[] = [
  { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { id: 'projects', label: 'Projects', icon: FolderOpen },
  { id: 'clients', label: 'Clients', icon: Users },
  { id: 'estimation', label: 'Estimation', icon: Calculator },
  { id: 'materials', label: 'Materials', icon: Package },
  { id: 'purchase', label: 'Purchase', icon: ShoppingCart },
  { id: 'finance', label: 'Finance', icon: DollarSign },
  { id: 'reports', label: 'Reports', icon: BarChart2 },
  { id: 'calendar', label: 'Calendar', icon: CalendarDays },
  { id: 'account-tracker', label: 'Account Tracker', icon: CreditCard },
  { id: 'settings', label: 'Settings', icon: Settings },
];

interface SidebarProps {
  active: NavPage;
  onNavigate: (page: NavPage) => void;
  collapsed: boolean;
  onToggle: () => void;
}

export default function Sidebar({ active, onNavigate, collapsed, onToggle }: SidebarProps) {
  return (
    <aside
      className="flex flex-col h-full transition-all duration-300 ease-in-out"
      style={{
        width: collapsed ? 72 : 240,
        background: 'linear-gradient(180deg, #0B1F3A 0%, #0F2B4C 100%)',
        boxShadow: '4px 0 24px rgba(0,0,0,0.18)',
        flexShrink: 0,
      }}
    >
      {/* Logo */}
      <div className="flex items-center px-4 h-16 border-b border-white/10" style={{ minHeight: 64 }}>
        <div className="flex items-center gap-3 overflow-hidden">
          <div className="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-600 flex-shrink-0">
            <HardHat size={20} className="text-white" />
          </div>
          {!collapsed && (
            <div className="flex flex-col leading-none">
              <span className="text-white font-bold text-lg tracking-tight" style={{ fontFamily: 'var(--font-display)' }}>
                Trackzo
              </span>
              <span className="text-blue-300 text-[10px] font-medium tracking-widest uppercase">
                Construction ERP
              </span>
            </div>
          )}
        </div>
        <button
          onClick={onToggle}
          className="ml-auto flex items-center justify-center w-7 h-7 rounded-lg text-blue-300 hover:text-white hover:bg-white/10 transition-colors flex-shrink-0"
        >
          {collapsed ? <ChevronRight size={16} /> : <ChevronLeft size={16} />}
        </button>
      </div>

      {/* Nav */}
      <nav className="flex-1 py-4 overflow-y-auto scrollbar-hide">
        {!collapsed && (
          <p className="px-4 mb-2 text-[10px] font-semibold tracking-widest text-blue-400/60 uppercase">
            Main Menu
          </p>
        )}
        <ul className="space-y-0.5 px-2">
          {navItems.slice(0, 8).map((item) => (
            <NavItem key={item.id} item={item} active={active} onNavigate={onNavigate} collapsed={collapsed} />
          ))}
        </ul>
        {!collapsed && (
          <p className="px-4 mt-4 mb-2 text-[10px] font-semibold tracking-widest text-blue-400/60 uppercase">
            Management
          </p>
        )}
        {collapsed && <div className="my-3 mx-3 border-t border-white/10" />}
        <ul className="space-y-0.5 px-2">
          {navItems.slice(8).map((item) => (
            <NavItem key={item.id} item={item} active={active} onNavigate={onNavigate} collapsed={collapsed} />
          ))}
        </ul>
      </nav>

      {/* User */}
      <div className="border-t border-white/10 p-3">
        <div className={`flex items-center gap-3 p-2 rounded-xl cursor-pointer hover:bg-white/10 transition-colors ${collapsed ? 'justify-center' : ''}`}>
          <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
            JC
          </div>
          {!collapsed && (
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-semibold truncate">James Carter</p>
              <p className="text-blue-300 text-xs truncate">Project Manager</p>
            </div>
          )}
          {!collapsed && (
            <button className="text-blue-300 hover:text-white transition-colors">
              <LogOut size={15} />
            </button>
          )}
        </div>
      </div>
    </aside>
  );
}

function NavItem({ item, active, onNavigate, collapsed }: {
  item: typeof navItems[0];
  active: NavPage;
  onNavigate: (p: NavPage) => void;
  collapsed: boolean;
}) {
  const isActive = active === item.id;
  const Icon = item.icon;
  return (
    <li>
      <button
        onClick={() => onNavigate(item.id)}
        title={collapsed ? item.label : undefined}
        className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 ${
          isActive
            ? 'bg-blue-600 text-white shadow-lg'
            : 'text-blue-200 hover:bg-white/10 hover:text-white'
        } ${collapsed ? 'justify-center' : ''}`}
      >
        <Icon size={18} className="flex-shrink-0" />
        {!collapsed && <span className="truncate">{item.label}</span>}
        {isActive && !collapsed && (
          <span className="ml-auto w-1.5 h-1.5 rounded-full bg-white/80" />
        )}
      </button>
    </li>
  );
}
