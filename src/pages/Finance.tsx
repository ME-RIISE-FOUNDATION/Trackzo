import { useState } from 'react';
import { TrendingUp, TrendingDown, Plus, Search, Filter, DollarSign, ArrowUpRight, ArrowDownRight } from 'lucide-react';
import {
  AreaChart, Area, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip,
  ResponsiveContainer, Legend
} from 'recharts';
import Modal from '../components/ui/Modal';
import { transactions as initialTx, cashFlowData } from '../data/mockData';
import type { Transaction } from '../types';

const statusStyles: Record<string, string> = {
  paid: 'bg-emerald-100 text-emerald-700',
  pending: 'bg-amber-100 text-amber-700',
  overdue: 'bg-red-100 text-red-700',
};

const tabs = ['All', 'Income', 'Expenses', 'Pending', 'Overdue'];

export default function Finance({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [txs, setTxs] = useState(initialTx);
  const [tab, setTab] = useState('All');
  const [search, setSearch] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState<Partial<Transaction>>({ type: 'income', status: 'paid' });

  const filtered = txs.filter((t) => {
    const ms = t.description.toLowerCase().includes(search.toLowerCase()) || t.category.toLowerCase().includes(search.toLowerCase());
    if (tab === 'All') return ms;
    if (tab === 'Income') return ms && t.type === 'income';
    if (tab === 'Expenses') return ms && t.type === 'expense';
    if (tab === 'Pending') return ms && t.status === 'pending';
    if (tab === 'Overdue') return ms && t.status === 'overdue';
    return ms;
  });

  const totalIncome = txs.filter((t) => t.type === 'income' && t.status === 'paid').reduce((s, t) => s + t.amount, 0);
  const totalExpense = txs.filter((t) => t.type === 'expense').reduce((s, t) => s + t.amount, 0);
  const pending = txs.filter((t) => t.status === 'pending' || t.status === 'overdue').reduce((s, t) => s + t.amount, 0);
  const profit = totalIncome - totalExpense;

  function handleSave() {
    if (!form.description || !form.amount) { onToast('Fill all required fields', 'error'); return; }
    const newT: Transaction = {
      id: `t${Date.now()}`,
      date: new Date().toISOString().slice(0, 10),
      projectId: 'p1',
      category: form.category || 'Other',
      ...form as Transaction,
    };
    setTxs((prev) => [newT, ...prev]);
    onToast('Transaction added', 'success');
    setShowModal(false);
    setForm({ type: 'income', status: 'paid' });
  }

  const fmt = (n: number) => n >= 1000000 ? `$${(n / 1000000).toFixed(2)}M` : `$${(n / 1000).toFixed(1)}K`;

  const F = (field: keyof Transaction, val: string | number) => setForm((f) => ({ ...f, [field]: val }));
  const inp = 'w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all';

  return (
    <div className="p-6 space-y-5">
      {/* KPIs */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: 'Total Income', value: fmt(totalIncome), icon: TrendingUp, color: 'bg-emerald-50', iconColor: 'text-emerald-500', textColor: 'text-emerald-700', arrow: <ArrowUpRight size={14} className="text-emerald-500" /> },
          { label: 'Total Expenses', value: fmt(totalExpense), icon: TrendingDown, color: 'bg-rose-50', iconColor: 'text-rose-500', textColor: 'text-rose-700', arrow: <ArrowDownRight size={14} className="text-rose-500" /> },
          { label: 'Net Profit', value: fmt(profit), icon: DollarSign, color: profit > 0 ? 'bg-blue-50' : 'bg-red-50', iconColor: profit > 0 ? 'text-blue-500' : 'text-red-500', textColor: profit > 0 ? 'text-blue-700' : 'text-red-700', arrow: null },
          { label: 'Receivables', value: fmt(pending), icon: Filter, color: 'bg-amber-50', iconColor: 'text-amber-500', textColor: 'text-amber-700', arrow: null },
        ].map((s) => {
          const Icon = s.icon;
          return (
            <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
              <div className="flex items-center justify-between mb-3">
                <div className={`w-9 h-9 rounded-xl ${s.color} flex items-center justify-center`}>
                  <Icon size={18} className={s.iconColor} />
                </div>
                {s.arrow}
              </div>
              <p className={`text-xl font-bold ${s.textColor}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
              <p className="text-xs text-slate-400 mt-0.5">{s.label}</p>
            </div>
          );
        })}
      </div>

      {/* Cash flow chart */}
      <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>Cash Flow — 2025</h3>
            <p className="text-xs text-slate-400 mt-0.5">Monthly income vs. expenses</p>
          </div>
        </div>
        <ResponsiveContainer width="100%" height={200}>
          <BarChart data={cashFlowData} margin={{ top: 4, right: 4, left: -20, bottom: 0 }} barGap={4}>
            <CartesianGrid strokeDasharray="3 3" stroke="#F1F5F9" />
            <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} />
            <YAxis tick={{ fontSize: 11, fill: '#94A3B8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `$${v / 1000}K`} />
            <Tooltip formatter={(v: number) => [`$${(v / 1000).toFixed(0)}K`, undefined]} contentStyle={{ borderRadius: 12, border: '1px solid #E2E8F0', fontSize: 12 }} />
            <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: 12 }} />
            <Bar dataKey="income" name="Income" fill="#10B981" radius={[4, 4, 0, 0]} />
            <Bar dataKey="expense" name="Expense" fill="#F87171" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>

      {/* Transactions */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div className="px-5 pt-4 pb-3 border-b border-slate-100">
          <div className="flex items-center justify-between gap-3 flex-wrap">
            <div className="flex gap-1">
              {tabs.map((t) => (
                <button key={t} onClick={() => setTab(t)}
                  className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors ${tab === t ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-slate-100'}`}>
                  {t}
                </button>
              ))}
            </div>
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                <Search size={13} className="text-slate-400" />
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search..." className="text-sm outline-none bg-transparent text-slate-700 placeholder-slate-400 w-32" />
              </div>
              <button onClick={() => setShowModal(true)} className="flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <Plus size={13} /> Add
              </button>
            </div>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50/50 border-b border-slate-50">
                <th className="text-left px-5 py-3 text-xs font-semibold text-slate-500">Date</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500">Description</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500">Category</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500">Type</th>
                <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500">Amount</th>
                <th className="text-center px-4 py-3 text-xs font-semibold text-slate-500">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filtered.map((t) => (
                <tr key={t.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-5 py-3.5 text-xs text-slate-400 font-mono">{t.date}</td>
                  <td className="px-4 py-3.5">
                    <div className="flex items-center gap-2">
                      <div className={`w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${t.type === 'income' ? 'bg-emerald-50' : 'bg-rose-50'}`}>
                        {t.type === 'income' ? <TrendingUp size={13} className="text-emerald-500" /> : <TrendingDown size={13} className="text-rose-500" />}
                      </div>
                      <span className="text-slate-700 font-medium text-sm">{t.description}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3.5 text-xs text-slate-500">{t.category}</td>
                  <td className="px-4 py-3.5">
                    <span className={`text-xs font-semibold px-2 py-0.5 rounded-lg capitalize ${t.type === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}`}>
                      {t.type}
                    </span>
                  </td>
                  <td className={`px-4 py-3.5 text-right font-bold font-mono ${t.type === 'income' ? 'text-emerald-600' : 'text-rose-500'}`}>
                    {t.type === 'income' ? '+' : '-'}${t.amount.toLocaleString()}
                  </td>
                  <td className="px-4 py-3.5 text-center">
                    <span className={`text-[11px] font-semibold px-2.5 py-1 rounded-lg capitalize ${statusStyles[t.status]}`}>{t.status}</span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <Modal open={showModal} onClose={() => setShowModal(false)} title="Add Transaction">
        <div className="space-y-4">
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Description *</label><input value={form.description || ''} onChange={(e) => F('description', e.target.value)} className={inp} /></div>
          <div className="grid grid-cols-2 gap-4">
            <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Type</label>
              <select value={form.type || 'income'} onChange={(e) => F('type', e.target.value)} className={inp}>
                <option value="income">Income</option><option value="expense">Expense</option>
              </select>
            </div>
            <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($) *</label>
              <input type="number" value={form.amount || ''} onChange={(e) => F('amount', Number(e.target.value))} className={inp} />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Category</label><input value={form.category || ''} onChange={(e) => F('category', e.target.value)} className={inp} placeholder="e.g. Materials" /></div>
            <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
              <select value={form.status || 'paid'} onChange={(e) => F('status', e.target.value)} className={inp}>
                <option value="paid">Paid</option><option value="pending">Pending</option><option value="overdue">Overdue</option>
              </select>
            </div>
          </div>
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={handleSave} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">Add Transaction</button>
        </div>
      </Modal>
    </div>
  );
}
