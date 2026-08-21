import { useState } from 'react';
import { Plus, Search, Mail, Phone, MapPin, Building2, Edit2, Trash2, TrendingUp } from 'lucide-react';
import Modal from '../components/ui/Modal';
import { clients as initialClients } from '../data/mockData';
import type { Client } from '../types';

const input = 'w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all';

const avatarColors = [
  'from-blue-400 to-blue-600', 'from-purple-400 to-purple-600',
  'from-emerald-400 to-emerald-600', 'from-amber-400 to-amber-600',
  'from-rose-400 to-rose-600', 'from-teal-400 to-teal-600',
];

export default function Clients({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [clients, setClients] = useState(initialClients);
  const [search, setSearch] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<Client | null>(null);
  const [form, setForm] = useState<Partial<Client>>({});

  const filtered = clients.filter((c) =>
    c.name.toLowerCase().includes(search.toLowerCase()) ||
    c.company.toLowerCase().includes(search.toLowerCase()) ||
    c.email.toLowerCase().includes(search.toLowerCase())
  );

  function openCreate() { setSelected(null); setForm({ status: 'active' }); setShowModal(true); }
  function openEdit(c: Client) { setSelected(c); setForm(c); setShowModal(true); }

  function handleSave() {
    if (!form.name || !form.email) { onToast('Name and email are required', 'error'); return; }
    if (selected) {
      setClients((prev) => prev.map((c) => c.id === selected.id ? { ...c, ...form } as Client : c));
      onToast('Client updated', 'success');
    } else {
      const newC: Client = {
        id: `c${Date.now()}`, totalProjects: 0, totalValue: 0,
        joinedDate: new Date().toISOString().slice(0, 10),
        avatar: (form.name || 'CL').split(' ').map((w) => w[0]).join('').slice(0, 2).toUpperCase(),
        status: 'active', ...form as Client,
      };
      setClients((prev) => [newC, ...prev]);
      onToast('Client added', 'success');
    }
    setShowModal(false);
  }

  function handleDelete(id: string) {
    setClients((prev) => prev.filter((c) => c.id !== id));
    onToast('Client removed', 'info');
  }

  const F = (field: keyof Client, val: string) => setForm((f) => ({ ...f, [field]: val }));

  return (
    <div className="p-6 space-y-5">
      {/* Summary */}
      <div className="grid grid-cols-3 gap-4">
        {[
          { label: 'Total Clients', value: clients.length, color: 'text-slate-900' },
          { label: 'Active', value: clients.filter((c) => c.status === 'active').length, color: 'text-emerald-600' },
          { label: 'Total Value', value: `$${(clients.reduce((s, c) => s + c.totalValue, 0) / 1000000).toFixed(1)}M`, color: 'text-blue-600' },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p className={`text-2xl font-bold ${s.color}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
            <p className="text-xs text-slate-400 mt-0.5">{s.label}</p>
          </div>
        ))}
      </div>

      {/* Toolbar */}
      <div className="flex gap-3">
        <div className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2.5 flex-1">
          <Search size={15} className="text-slate-400" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search clients..." className="flex-1 text-sm outline-none text-slate-700 placeholder-slate-400" />
        </div>
        <button onClick={openCreate} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
          <Plus size={16} /> Add Client
        </button>
      </div>

      {/* Client cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        {filtered.map((c, i) => (
          <div key={c.id} className="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all p-5">
            <div className="flex items-start gap-3 mb-4">
              <div className={`w-11 h-11 rounded-2xl bg-gradient-to-br ${avatarColors[i % avatarColors.length]} flex items-center justify-center text-white text-sm font-bold flex-shrink-0`}>
                {c.avatar}
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-bold text-slate-900 truncate" style={{ fontFamily: 'var(--font-display)' }}>{c.name}</h3>
                <div className="flex items-center gap-1 text-xs text-slate-500 mt-0.5">
                  <Building2 size={11} /><span className="truncate">{c.company}</span>
                </div>
              </div>
              <span className={`text-[11px] font-semibold px-2 py-0.5 rounded-lg ${c.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                {c.status}
              </span>
            </div>

            <div className="space-y-1.5 mb-4">
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <Mail size={11} className="text-slate-400" /><span className="truncate">{c.email}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <Phone size={11} className="text-slate-400" /><span>{c.phone}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <MapPin size={11} className="text-slate-400" /><span className="truncate">{c.city}</span>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2 mb-4">
              <div className="bg-slate-50 rounded-xl p-2.5 text-center">
                <p className="text-base font-bold text-slate-900">{c.totalProjects}</p>
                <p className="text-[10px] text-slate-400">Projects</p>
              </div>
              <div className="bg-blue-50 rounded-xl p-2.5 text-center">
                <p className="text-base font-bold text-blue-700">${(c.totalValue / 1000).toFixed(0)}K</p>
                <p className="text-[10px] text-blue-400">Total Value</p>
              </div>
            </div>

            <div className="flex items-center justify-between pt-3 border-t border-slate-100">
              <span className="text-xs text-slate-400">Since {c.joinedDate}</span>
              <div className="flex gap-1">
                <button onClick={() => openEdit(c)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"><Edit2 size={14} /></button>
                <button onClick={() => handleDelete(c.id)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"><Trash2 size={14} /></button>
              </div>
            </div>
          </div>
        ))}
      </div>

      <Modal open={showModal} onClose={() => setShowModal(false)} title={selected ? 'Edit Client' : 'Add New Client'}>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Full Name *</label><input value={form.name || ''} onChange={(e) => F('name', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Company</label><input value={form.company || ''} onChange={(e) => F('company', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Email *</label><input type="email" value={form.email || ''} onChange={(e) => F('email', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label><input value={form.phone || ''} onChange={(e) => F('phone', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Address</label><input value={form.address || ''} onChange={(e) => F('address', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">City</label><input value={form.city || ''} onChange={(e) => F('city', e.target.value)} className={input} /></div>
          <div><label className="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
            <select value={form.status || 'active'} onChange={(e) => F('status', e.target.value)} className={input}>
              <option value="active">Active</option><option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={handleSave} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">{selected ? 'Save Changes' : 'Add Client'}</button>
        </div>
      </Modal>
    </div>
  );
}
