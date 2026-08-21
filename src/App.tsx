import { useState, useCallback } from 'react';
import Sidebar from './components/Sidebar';
import TopBar from './components/TopBar';
import ToastContainer from './components/ui/Toast';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Projects from './pages/Projects';
import Clients from './pages/Clients';
import Estimation from './pages/Estimation';
import Materials from './pages/Materials';
import Purchase from './pages/Purchase';
import Finance from './pages/Finance';
import Reports from './pages/Reports';
import CalendarPage from './pages/CalendarPage';
import AccountTracker from './pages/AccountTracker';
import Settings from './pages/Settings';
import type { NavPage, Toast } from './types';

const actionLabels: Partial<Record<NavPage, string>> = {
  projects: 'New Project',
  clients: 'Add Client',
  materials: 'Add Material',
  purchase: 'New Order',
  estimation: 'New Estimate',
};

export default function App() {
  const [authed, setAuthed] = useState(false);
  const [page, setPage] = useState<NavPage>('dashboard');
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const [toasts, setToasts] = useState<Toast[]>([]);
  const [showCreateModal, setShowCreateModal] = useState(false);

  const addToast = useCallback((message: string, type: string = 'success') => {
    const id = `t${Date.now()}`;
    setToasts((prev) => [...prev, { id, message, type: type as Toast['type'] }]);
    setTimeout(() => setToasts((prev) => prev.filter((t) => t.id !== id)), 4000);
  }, []);

  const removeToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  if (!authed) {
    return <Login onLogin={() => setAuthed(true)} />;
  }

  const pageProps = { onToast: addToast };

  const renderPage = () => {
    switch (page) {
      case 'dashboard': return <Dashboard />;
      case 'projects': return <Projects {...pageProps} />;
      case 'clients': return <Clients {...pageProps} />;
      case 'estimation': return <Estimation {...pageProps} />;
      case 'materials': return <Materials {...pageProps} />;
      case 'purchase': return <Purchase {...pageProps} />;
      case 'finance': return <Finance {...pageProps} />;
      case 'reports': return <Reports {...pageProps} />;
      case 'calendar': return <CalendarPage />;
      case 'account-tracker': return <AccountTracker {...pageProps} />;
      case 'settings': return <Settings {...pageProps} />;
      default: return <Dashboard />;
    }
  };

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar
        active={page}
        onNavigate={setPage}
        collapsed={sidebarCollapsed}
        onToggle={() => setSidebarCollapsed((c) => !c)}
      />

      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        <TopBar
          page={page}
          onAction={actionLabels[page] ? () => addToast(`Opening create form for ${page}`, 'info') : undefined}
          actionLabel={actionLabels[page]}
        />

        <main className="flex-1 overflow-y-auto">
          {renderPage()}
        </main>
      </div>

      <ToastContainer toasts={toasts} onRemove={removeToast} />
    </div>
  );
}
