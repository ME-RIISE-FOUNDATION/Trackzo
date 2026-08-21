import { useState } from 'react';
import { Download, FileText, BarChart2, TrendingUp, Package, Users, Layers } from 'lucide-react';
import {
  BarChart, Bar, PieChart, Pie, Cell, LineChart, Line,
  XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend
} from 'recharts';
import { budgetVsExpenseData, cashFlowData, projectStatusData, materialCostData } from '../data/mockData';

const reportTypes = [
  { id: 'project', label: 'Project Report', icon: Layers, color: 'bg-blue-50 text-blue-600' },
  { id: 'expense', label: 'Expense Report', icon: TrendingUp, color: 'bg-rose-50 text-rose-600' },
  { id: 'material', label: 'Material Report', icon: Package, color: 'bg-violet-50 text-violet-600' },
  { id: 'labour', label: 'Labour Report', icon: Users, color: 'bg-amber-50 text-amber-600' },
  { id: 'finance', label: 'Finance Report', icon: BarChart2, color: 'bg-emerald-50 text-emerald-600' },
  { id: 'progress', label: 'Progress Report', icon: FileText, color: 'bg-slate-50 text-slate-600' },
];

const PIE_COLORS = ['#1D4ED8', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];

export default function Reports({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [active, setActive] = useState('project');
  const [period, setPeriod] = useState('2025');

  function handleExport(format: string) {
    onToast(`Exporting ${reportTypes.find((r) => r.id === active)?.label} as ${format}...`, 'info');
  }

  return (
    <div className="p-6 space-y-5">
      {/* Report type selector */}
      <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        {reportTypes.map((r) => {
          const Icon = r.icon;
          return (
            <button key={r.id} onClick={() => setActive(r.id)}
              className={`flex flex-col items-center gap-2 p-4 rounded-2xl border transition-all ${
                active === r.id
                  ? 'border-blue-400 bg-blue-600 text-white shadow-lg ring-2 ring-blue-100'
                  : 'border-slate-100 bg-white text-slate-600 hover:border-slate-200 hover:shadow-sm'
              }`}>
              <div className={`w-9 h-9 rounded-xl flex items-center justify-center ${active === r.id ? 'bg-white/20' : r.color}`}>
                <Icon size={18} className={active === r.id ? 'text-white' : ''} />
              </div>
              <span className="text-xs font-semibold text-center leading-tight">{r.label}</span>
            </button>
          );
        })}
      </div>

      {/* Report controls */}
      <div className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4 flex-wrap">
        <h3 className="font-bold text-slate-900 text-sm flex-1" style={{ fontFamily: 'var(--font-display)' }}>
          {reportTypes.find((r) => r.id === active)?.label}
        </h3>
        <select value={period} onChange={(e) => setPeriod(e.target.value)} className="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 outline-none">
          <option value="2025">2025</option>
          <option value="2024">2024</option>
        </select>
        <button onClick={() => handleExport('PDF')} className="flex items-center gap-1.5 px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-xl transition-colors">
          <Download size={13} /> PDF
        </button>
        <button onClick={() => handleExport('Excel')} className="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-colors">
          <Download size={13} /> Excel
        </button>
      </div>

      {/* Report content */}
      {active === 'project' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Project Status Distribution</h4>
            <ResponsiveContainer width="100%" height={220}>
              <PieChart>
                <Pie data={projectStatusData} cx="50%" cy="50%" outerRadius={80} dataKey="value" label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`} labelLine={false} fontSize={11}>
                  {projectStatusData.map((_, i) => <Cell key={i} fill={PIE_COLORS[i]} />)}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Budget vs. Expenses</h4>
            <ResponsiveContainer width="100%" height={220}>
              <BarChart data={budgetVsExpenseData} margin={{ left: -20 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#F1F5F9" />
                <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `$${v / 1000}K`} />
                <Tooltip formatter={(v: number) => [`$${(v / 1000).toFixed(0)}K`]} contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
                <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: 12 }} />
                <Bar dataKey="budget" name="Budget" fill="#1D4ED8" radius={[4, 4, 0, 0]} />
                <Bar dataKey="expense" name="Expense" fill="#EF4444" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
          <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-slate-50">
                <tr>
                  {['Project', 'Client', 'Budget', 'Spent', 'Remaining', 'Progress', 'Status'].map((h) => (
                    <th key={h} className="text-left px-5 py-3 text-xs font-semibold text-slate-500">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50">
                {[
                  ['Skyline Tower Residences', 'Metro Developers', '$4.8M', '$2.94M', '$1.86M', '62%', 'active'],
                  ['Greenfield Commercial Park', 'Apex Realty Group', '$2.6M', '$0.98M', '$1.62M', '38%', 'active'],
                  ['Sunrise Villa Estate', 'Harrison Family Trust', '$920K', '$905K', '$15K', '100%', 'completed'],
                  ['Riverfront Warehouse', 'Urban Loft Holdings', '$1.4M', '$320K', '$1.08M', '23%', 'on-hold'],
                  ['Northgate School', 'City Education Auth.', '$3.2M', '$45K', '$3.155M', '5%', 'planning'],
                ].map(([name, client, budget, spent, rem, prog, status]) => (
                  <tr key={name as string} className="hover:bg-slate-50/50">
                    <td className="px-5 py-3 font-semibold text-slate-800">{String(name)}</td>
                    <td className="px-5 py-3 text-slate-500 text-xs">{String(client)}</td>
                    <td className="px-5 py-3 font-mono text-slate-700">{String(budget)}</td>
                    <td className="px-5 py-3 font-mono text-rose-600">{String(spent)}</td>
                    <td className="px-5 py-3 font-mono text-emerald-600">{String(rem)}</td>
                    <td className="px-5 py-3 font-semibold text-blue-600">{String(prog)}</td>
                    <td className="px-5 py-3"><span className="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 capitalize">{String(status)}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {active === 'finance' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Monthly Cash Flow</h4>
            <ResponsiveContainer width="100%" height={220}>
              <LineChart data={cashFlowData} margin={{ left: -20 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#F1F5F9" />
                <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `$${v / 1000}K`} />
                <Tooltip formatter={(v: number) => [`$${(v / 1000).toFixed(0)}K`]} contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
                <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: 12 }} />
                <Line type="monotone" dataKey="income" name="Income" stroke="#10B981" strokeWidth={2.5} dot={false} />
                <Line type="monotone" dataKey="expense" name="Expense" stroke="#EF4444" strokeWidth={2.5} dot={false} />
              </LineChart>
            </ResponsiveContainer>
          </div>
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>P&L Summary</h4>
            <div className="space-y-3">
              {[
                { label: 'Total Revenue', value: '$1,992,000', color: 'text-emerald-600', bar: 'bg-emerald-500', pct: 100 },
                { label: 'Direct Costs', value: '$722,000', color: 'text-rose-500', bar: 'bg-rose-400', pct: 36 },
                { label: 'Gross Profit', value: '$1,270,000', color: 'text-blue-600', bar: 'bg-blue-500', pct: 64 },
                { label: 'Operating Expenses', value: '$185,000', color: 'text-amber-600', bar: 'bg-amber-400', pct: 9 },
                { label: 'Net Profit', value: '$1,085,000', color: 'text-slate-900', bar: 'bg-slate-700', pct: 54 },
              ].map((row) => (
                <div key={row.label}>
                  <div className="flex justify-between text-sm mb-1">
                    <span className="text-slate-500">{row.label}</span>
                    <span className={`font-bold font-mono ${row.color}`}>{row.value}</span>
                  </div>
                  <div className="h-1.5 bg-slate-100 rounded-full">
                    <div className={`h-1.5 rounded-full ${row.bar}`} style={{ width: `${row.pct}%` }} />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {active === 'material' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Material Cost Breakdown</h4>
            <ResponsiveContainer width="100%" height={240}>
              <PieChart>
                <Pie data={materialCostData} cx="50%" cy="50%" outerRadius={90} dataKey="value"
                  label={({ name, value }) => `${name}: ${value}%`} fontSize={11}>
                  {materialCostData.map((_, i) => <Cell key={i} fill={PIE_COLORS[i]} />)}
                </Pie>
                <Tooltip formatter={(v) => [`${v}%`]} contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Top Material Spend</h4>
            <div className="space-y-3">
              {[
                { name: 'Steel Rebar', spend: '$842,000', pct: 40 },
                { name: 'Portland Cement', spend: '$588,000', pct: 28 },
                { name: 'Concrete Mix', spend: '$315,000', pct: 15 },
                { name: 'Structural Timber', spend: '$252,000', pct: 12 },
                { name: 'Others', spend: '$105,000', pct: 5 },
              ].map((row) => (
                <div key={row.name}>
                  <div className="flex justify-between text-sm mb-1">
                    <span className="text-slate-600 font-medium">{row.name}</span>
                    <span className="font-bold font-mono text-slate-800">{row.spend}</span>
                  </div>
                  <div className="h-1.5 bg-slate-100 rounded-full">
                    <div className="h-1.5 rounded-full bg-blue-500" style={{ width: `${row.pct}%` }} />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {!['project', 'finance', 'material'].includes(active) && (
        <div className="bg-white rounded-2xl p-12 border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center">
          <div className="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
            <BarChart2 size={28} className="text-blue-500" />
          </div>
          <h4 className="font-bold text-slate-900 mb-2" style={{ fontFamily: 'var(--font-display)' }}>
            {reportTypes.find((r) => r.id === active)?.label} Preview
          </h4>
          <p className="text-sm text-slate-400 mb-5">Report data is compiled and ready for export.</p>
          <div className="flex gap-2">
            <button onClick={() => handleExport('PDF')} className="flex items-center gap-1.5 px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition-colors">
              <Download size={14} /> Export PDF
            </button>
            <button onClick={() => handleExport('Excel')} className="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
              <Download size={14} /> Export Excel
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
