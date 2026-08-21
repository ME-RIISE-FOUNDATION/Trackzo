import { useState } from 'react';
import { HardHat, Eye, EyeOff, LogIn } from 'lucide-react';

interface LoginProps {
  onLogin: () => void;
}

export default function Login({ onLogin }: LoginProps) {
  const [email, setEmail] = useState('james.carter@trackzo.io');
  const [password, setPassword] = useState('password123');
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    if (!email || !password) { setError('Please enter email and password.'); return; }
    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      onLogin();
    }, 1200);
  }

  return (
    <div className="min-h-screen flex" style={{ background: '#F1F5F9' }}>
      {/* Left panel */}
      <div className="hidden lg:flex flex-col justify-between p-10 w-[440px] flex-shrink-0"
        style={{ background: 'linear-gradient(160deg, #0B1F3A 0%, #1D4ED8 100%)' }}>
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
            <HardHat size={22} className="text-white" />
          </div>
          <div>
            <p className="text-white font-bold text-xl tracking-tight" style={{ fontFamily: 'var(--font-display)' }}>Trackzo</p>
            <p className="text-blue-200 text-[11px] tracking-widest uppercase">Construction ERP</p>
          </div>
        </div>

        <div>
          <h2 className="text-white text-3xl font-bold mb-4 leading-tight" style={{ fontFamily: 'var(--font-display)' }}>
            Build Smarter.<br />Track Better.
          </h2>
          <p className="text-blue-200 text-sm leading-relaxed mb-8">
            The all-in-one ERP platform trusted by builders, contractors, and construction companies to manage every project from foundation to handover.
          </p>
          <div className="grid grid-cols-2 gap-3">
            {[
              { label: 'Projects Managed', value: '1,200+' },
              { label: 'Active Users', value: '8,500+' },
              { label: 'Countries', value: '24' },
              { label: 'Uptime SLA', value: '99.9%' },
            ].map((s) => (
              <div key={s.label} className="bg-white/10 rounded-xl p-3">
                <p className="text-white font-bold text-lg" style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
                <p className="text-blue-200 text-xs">{s.label}</p>
              </div>
            ))}
          </div>
        </div>

        <p className="text-blue-300 text-xs">© 2025 Trackzo Inc. All rights reserved.</p>
      </div>

      {/* Right panel */}
      <div className="flex-1 flex items-center justify-center p-6">
        <div className="w-full max-w-md">
          {/* Mobile logo */}
          <div className="flex items-center gap-3 mb-8 lg:hidden">
            <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
              <HardHat size={22} className="text-white" />
            </div>
            <div>
              <p className="font-bold text-xl text-slate-900" style={{ fontFamily: 'var(--font-display)' }}>Trackzo</p>
              <p className="text-slate-400 text-xs">Construction ERP</p>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <h1 className="text-2xl font-bold text-slate-900 mb-1" style={{ fontFamily: 'var(--font-display)' }}>Welcome back</h1>
            <p className="text-slate-400 text-sm mb-6">Sign in to your Trackzo workspace</p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-600 mb-1.5">Email address</label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all"
                  placeholder="you@company.com"
                />
              </div>
              <div>
                <div className="flex justify-between items-center mb-1.5">
                  <label className="block text-xs font-semibold text-slate-600">Password</label>
                  <button type="button" className="text-xs text-blue-600 hover:underline">Forgot password?</button>
                </div>
                <div className="relative">
                  <input
                    type={showPw ? 'text' : 'password'}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full border border-slate-200 rounded-xl px-4 py-3 pr-10 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all"
                    placeholder="••••••••"
                  />
                  <button type="button" onClick={() => setShowPw(!showPw)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                    {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                  </button>
                </div>
              </div>

              {error && (
                <div className="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600">{error}</div>
              )}

              <button
                type="submit"
                disabled={loading}
                className="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-70 text-white font-semibold rounded-xl transition-all text-sm"
              >
                {loading ? (
                  <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                ) : (
                  <LogIn size={16} />
                )}
                {loading ? 'Signing in...' : 'Sign In'}
              </button>
            </form>

            <div className="mt-5 pt-4 border-t border-slate-100">
              <p className="text-xs text-slate-400 text-center">
                Demo credentials are pre-filled. Click <strong className="text-slate-600">Sign In</strong> to continue.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
