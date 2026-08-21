import { useState } from 'react';
import { Landmark, CreditCard, Wallet, TrendingUp, TrendingDown, Plus, ArrowUpRight } from 'lucide-react';
import { accounts, transactions } from '../data/mockData';
import Modal from '../components/ui/Modal';

const typeIcons: Record<string, React.FC<{ size?: number; className?: string }>> = {
  bank: Landmark,
  cash: Wallet,
  credit: CreditCard,
};

const typeColors: Record<string, string> = {
  bank: 'bg-blue-50 text-blue-600',
  cash: 'bg-emerald-50 text-emerald-600',
  credit: 'bg-violet-50 text-violet-600',
};

const accountGradients: string[] = [
  'from-blue-600 to-blue-800',
  'from-slate-600 to-slate-800',
  'from-violet-600 to-violet-800',
  'from-rose-600 to-rose-800',
];

export default function AccountTracker({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<string | null>(null);

  const totalBalance = accounts.filter((a) => a.type !== 'credit').reduce((s, a) => s + a.balance, 0);
  const totalCredit = accounts.filter((a) => a.type === 'credit').reduce((s, a) => s + Math.abs(a.balance), 0);

  const selectedTxs = selected
    ? transactions.filter((t) => t.projectId === selected)
    : transactions.slice(0, 8);

  return (
    <div className="p-6 space-y-5">
      {/* Summary */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {[
          { label: 'Total Cash & Bank', value: `$${(totalBalance / 1000).toFixed(1)}K`, sub: `${accounts.filter((a) => a.type !== 'credit').length} accounts`, color: 'text-slate-900', bg: 'bg-white' },
          { label: 'Outstanding Credit', value: `$${(totalCredit / 1000).toFixed(1)}K`, sub: 'Credit card balances', color: 'text-rose-600', bg: 'bg-white' },
          { label: 'Net Position', value: `$${((totalBalance - totalCredit) / 1000).toFixed(1)}K`, sub: 'Cash − Liabilities', color: 'text-emerald-600', bg: 'bg-white' },
        ].map((s) => (
          <div key={s.label} className={`${s.bg} rounded-2xl p-5 border border-slate-100 shadow-sm`}>
            <p className="text-xs text-slate-400 mb-1">{s.sub}</p>
            <p className={`text-2xl font-bold ${s.color}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
            <p className="text-sm text-slate-500 mt-0.5">{s.label}</p>
          </div>
        ))}
      </div>

      {/* Account cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {accounts.map((acc, i) => {
          const Icon = typeIcons[acc.type];
          const isNeg = acc.balance < 0;
          return (
            <div key={acc.id}
              className={`rounded-2xl p-5 bg-gradient-to-br ${accountGradients[i % accountGradients.length]} text-white shadow-lg relative overflow-hidden cursor-pointer hover:scale-[1.02] transition-transform`}
              onClick={() => setSelected(selected === acc.id ? null : acc.id)}
            >
              <div className="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/5" />
              <div className="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white/5" />
              <div className="flex items-start justify-between mb-6">
                <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                  <Icon size={18} />
                </div>
                <span className="text-[10px] font-semibold uppercase tracking-wider bg-white/20 px-2 py-1 rounded-lg">{acc.type}</span>
              </div>
              <p className="text-xs text-white/70 mb-1">{acc.name}</p>
              <p className={`text-2xl font-bold mb-1`} style={{ fontFamily: 'var(--font-display)' }}>
                {isNeg ? '-' : ''}${Math.abs(acc.balance).toLocaleString()}
              </p>
              <p className="text-[11px] text-white/60">Last: {acc.lastTransaction}</p>
            </div>
          );
        })}
      </div>

      {/* Transactions */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>
            {selected ? `Transactions — Account ${selected.toUpperCase()}` : 'All Recent Transactions'}
          </h3>
          <button onClick={() => setShowModal(true)} className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-colors">
            <Plus size={13} /> New Transfer
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50/50">
                <th className="text-left px-5 py-3 text-xs font-semibold text-slate-500">Date</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500">Description</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500">Category</th>
                <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500">Amount</th>
                <th className="text-center px-4 py-3 text-xs font-semibold text-slate-500">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {selectedTxs.map((t) => (
                <tr key={t.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-5 py-3 text-xs text-slate-400 font-mono">{t.date}</td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2">
                      <div className={`w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${t.type === 'income' ? 'bg-emerald-50' : 'bg-rose-50'}`}>
                        {t.type === 'income' ? <TrendingUp size={13} className="text-emerald-500" /> : <TrendingDown size={13} className="text-rose-500" />}
                      </div>
                      <span className="text-slate-700 font-medium text-sm truncate max-w-[200px]">{t.description}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3 text-xs text-slate-500">{t.category}</td>
                  <td className={`px-4 py-3 text-right font-bold font-mono ${t.type === 'income' ? 'text-emerald-600' : 'text-rose-500'}`}>
                    {t.type === 'income' ? '+' : '-'}${t.amount.toLocaleString()}
                  </td>
                  <td className="px-4 py-3 text-center">
                    <span className={`text-[11px] font-semibold px-2 py-0.5 rounded-lg capitalize ${
                      t.status === 'paid' ? 'bg-emerald-100 text-emerald-700' :
                      t.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                      'bg-red-100 text-red-700'
                    }`}>{t.status}</span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <Modal open={showModal} onClose={() => setShowModal(false)} title="New Bank Transfer">
        <div className="space-y-4">
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">From Account</label>
            <select className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 outline-none">
              {accounts.map((a) => <option key={a.id}>{a.name}</option>)}
            </select>
          </div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">To Account</label>
            <select className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 outline-none">
              {accounts.map((a) => <option key={a.id}>{a.name}</option>)}
            </select>
          </div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Amount ($)</label>
            <input type="number" placeholder="0.00" className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400" />
          </div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Reference / Notes</label>
            <input placeholder="e.g. Site expenses transfer" className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400" />
          </div>
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={() => { onToast('Transfer initiated successfully', 'success'); setShowModal(false); }} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">Initiate Transfer</button>
        </div>
      </Modal>
    </div>
  );
}
