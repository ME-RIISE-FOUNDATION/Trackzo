import {
  AreaChart, Area, BarChart, Bar, PieChart, Pie, Cell, Tooltip,
  ResponsiveContainer, XAxis, YAxis, CartesianGrid, Legend
} from 'recharts';
import { TrendingUp, TrendingDown, FolderOpen, DollarSign, Package, Users, AlertCircle, ArrowUpRight } from 'lucide-react';
import { projects, transactions, budgetVsExpenseData, cashFlowData, projectStatusData } from '../data/mockData';

const fmt = (n: number) => n >= 1000000
  ? `$${(n / 1000000).toFixed(1)}M`
  : n >= 1000
  ? `$${(n / 1000).toFixed(0)}K`
  : `$${n}`;

const statusColors: Record<string, string> = {
  active: 'bg-blue-100 text-blue-700',
  completed: 'bg-emerald-100 text-emerald-700',
  'on-hold': 'bg-amber-100 text-amber-700',
  planning: 'bg-slate-100 text-slate-600',
};

const PIE_COLORS = ['#1D4ED8', '#10B981', '#F59E0B', '#EF4444'];

const stats = [
  { label: 'Total Projects', value: '5', sub: '2 active', icon: FolderOpen, color: 'bg-blue-50', iconColor: 'text-blue-600', trend: '+1 this month' },
  { label: 'Total Budget', value: '$12.9M', sub: 'Across all projects', icon: DollarSign, color: 'bg-emerald-50', iconColor: 'text-emerald-600', trend: '+18% YoY' },
  { label: 'Total Expenses', value: '$5.2M', sub: 'This fiscal year', icon: TrendingDown, color: 'bg-rose-50', iconColor: 'text-rose-600', trend: '40.3% of budget' },
  { label: 'Material Cost', value: '$2.1M', sub: 'Procurement spend', icon: Package, color: 'bg-violet-50', iconColor: 'text-violet-600', trend: '38% of expenses' },
  { label: 'Labour Cost', value: '$1.8M', sub: 'Wages + subcontractors', icon: Users, color: 'bg-amber-50', iconColor: 'text-amber-600', trend: '34% of expenses' },
  { label: 'Pending Payments', value: '$320K', sub: '3 invoices overdue', icon: AlertCircle, color: 'bg-orange-50', iconColor: 'text-orange-600', trend: 'Requires attention' },
];

export default function Dashboard() {
  const totalBudget = projects.reduce((s, p) => s + p.budget, 0);
  const totalSpent = projects.reduce((s, p) => s + p.spent, 0);
  const overallProgress = Math.round(totalSpent / totalBudget * 100);

  return (
    <div className="p-6 space-y-6">
      {/* Stats grid */}
      <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        {stats.map((s) => {
          const Icon = s.icon;
          return (
            <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
              <div className={`w-9 h-9 rounded-xl ${s.color} flex items-center justify-center mb-3`}>
                <Icon size={18} className={s.iconColor} />
              </div>
              <p className="text-2xl font-bold text-slate-900 mb-0.5" style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
              <p className="text-xs text-slate-500 mb-1">{s.label}</p>
              <p className="text-[11px] text-slate-400">{s.trend}</p>
            </div>
          );
        })}
      </div>

      {/* Charts row */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {/* Budget vs Expense */}
        <div className="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>Budget vs. Expenses</h3>
              <p className="text-xs text-slate-400 mt-0.5">Monthly comparison — all projects</p>
            </div>
            <span className="text-xs bg-blue-50 text-blue-600 font-semibold px-2.5 py-1 rounded-lg">2025</span>
          </div>
          <ResponsiveContainer width="100%" height={220}>
            <AreaChart data={budgetVsExpenseData} margin={{ top: 4, right: 4, left: -20, bottom: 0 }}>
              <defs>
                <linearGradient id="budgetGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#1D4ED8" stopOpacity={0.15} />
                  <stop offset="95%" stopColor="#1D4ED8" stopOpacity={0} />
                </linearGradient>
                <linearGradient id="expenseGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#EF4444" stopOpacity={0.12} />
                  <stop offset="95%" stopColor="#EF4444" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" stroke="#F1F5F9" />
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `$${v / 1000}K`} />
              <Tooltip formatter={(v: number) => [`$${(v / 1000).toFixed(0)}K`, undefined]} contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
              <Area type="monotone" dataKey="budget" name="Budget" stroke="#1D4ED8" strokeWidth={2} fill="url(#budgetGrad)" dot={false} />
              <Area type="monotone" dataKey="expense" name="Expense" stroke="#EF4444" strokeWidth={2} fill="url(#expenseGrad)" dot={false} />
              <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: 12 }} />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        {/* Project Status pie */}
        <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <h3 className="font-bold text-slate-900 text-sm mb-1" style={{ fontFamily: 'var(--font-display)' }}>Project Status</h3>
          <p className="text-xs text-slate-400 mb-4">All 5 projects</p>
          <ResponsiveContainer width="100%" height={160}>
            <PieChart>
              <Pie data={projectStatusData} cx="50%" cy="50%" innerRadius={48} outerRadius={72} paddingAngle={3} dataKey="value" stroke="none">
                {projectStatusData.map((_, i) => <Cell key={i} fill={PIE_COLORS[i]} />)}
              </Pie>
              <Tooltip contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
            </PieChart>
          </ResponsiveContainer>
          <div className="grid grid-cols-2 gap-1.5 mt-2">
            {projectStatusData.map((d, i) => (
              <div key={d.name} className="flex items-center gap-1.5">
                <div className="w-2.5 h-2.5 rounded-full flex-shrink-0" style={{ background: PIE_COLORS[i] }} />
                <span className="text-xs text-slate-500">{d.name}: <strong className="text-slate-700">{d.value}</strong></span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Bottom row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {/* Recent Projects */}
        <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>Recent Projects</h3>
            <button className="text-xs text-blue-600 font-semibold flex items-center gap-0.5 hover:underline">View all <ArrowUpRight size={12} /></button>
          </div>
          <div className="space-y-3">
            {projects.slice(0, 4).map((p) => (
              <div key={p.id} className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                  <FolderOpen size={16} />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-slate-800 truncate">{p.name}</p>
                  <div className="flex items-center gap-2 mt-0.5">
                    <div className="flex-1 bg-slate-100 rounded-full h-1.5">
                      <div className="h-1.5 rounded-full bg-blue-500 transition-all" style={{ width: `${p.progress}%` }} />
                    </div>
                    <span className="text-[11px] text-slate-500 whitespace-nowrap">{p.progress}%</span>
                  </div>
                </div>
                <span className={`text-[11px] font-semibold px-2 py-0.5 rounded-lg whitespace-nowrap ${statusColors[p.status]}`}>
                  {p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Transactions */}
        <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>Recent Transactions</h3>
            <button className="text-xs text-blue-600 font-semibold flex items-center gap-0.5 hover:underline">View all <ArrowUpRight size={12} /></button>
          </div>
          <div className="space-y-3">
            {transactions.slice(0, 5).map((t) => (
              <div key={t.id} className="flex items-center gap-3">
                <div className={`w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 ${t.type === 'income' ? 'bg-emerald-50' : 'bg-rose-50'}`}>
                  {t.type === 'income' ? <TrendingUp size={15} className="text-emerald-600" /> : <TrendingDown size={15} className="text-rose-500" />}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-slate-800 truncate">{t.description}</p>
                  <p className="text-[11px] text-slate-400">{t.date} · {t.category}</p>
                </div>
                <span className={`text-sm font-bold whitespace-nowrap ${t.type === 'income' ? 'text-emerald-600' : 'text-rose-500'}`}>
                  {t.type === 'income' ? '+' : '-'}{fmt(t.amount)}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Overall Progress */}
      <div className="bg-gradient-to-r from-navy-800 to-blue-600 rounded-2xl p-5 text-white flex items-center gap-6"
        style={{ background: 'linear-gradient(135deg, #0F2B4C 0%, #1D4ED8 100%)' }}>
        <div className="flex-1">
          <p className="text-sm font-semibold text-blue-200 mb-1">Overall Portfolio Progress</p>
          <p className="text-3xl font-bold mb-3" style={{ fontFamily: 'var(--font-display)' }}>{overallProgress}%</p>
          <div className="bg-white/20 rounded-full h-2">
            <div className="h-2 rounded-full bg-white transition-all" style={{ width: `${overallProgress}%` }} />
          </div>
          <p className="text-xs text-blue-200 mt-2">{fmt(totalSpent)} spent of {fmt(totalBudget)} total budget</p>
        </div>
        <div className="hidden sm:grid grid-cols-2 gap-4 text-center">
          <div>
            <p className="text-2xl font-bold">{fmt(totalBudget - totalSpent)}</p>
            <p className="text-xs text-blue-200">Remaining</p>
          </div>
          <div>
            <p className="text-2xl font-bold">5</p>
            <p className="text-xs text-blue-200">Projects</p>
          </div>
        </div>
      </div>
    </div>
  );
}
