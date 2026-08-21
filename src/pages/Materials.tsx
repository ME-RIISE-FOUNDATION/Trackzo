import { useState } from 'react';
import { Search, AlertTriangle, Plus, Edit2, Trash2, Package } from 'lucide-react';
import Modal from '../components/ui/Modal';
import { materials as initialMaterials } from '../data/mockData';
import type { Material } from '../types';

const input = 'w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all';

const categories = ['All', 'Cement', 'Steel', 'Aggregate', 'Masonry', 'Timber', 'Plumbing', 'Electrical'];

export default function Materials({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [materials, setMaterials] = useState(initialMaterials);
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('All');
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<Material | null>(null);
  const [form, setForm] = useState<Partial<Material>>({});

  const filtered = materials.filter((m) => {
    const ms = m.name.toLowerCase().includes(search.toLowerCase()) || m.supplier.toLowerCase().includes(search.toLowerCase());
    const mc = category === 'All' || m.category === category;
    return ms && mc;
  });

  const lowStock = materials.filter((m) => m.stock <= m.minStock);

  function stockLevel(m: Material): 'critical' | 'low' | 'ok' {
    const ratio = m.stock / m.minStock;
    if (ratio <= 1) return 'critical';
    if (ratio <= 1.5) return 'low';
    return 'ok';
  }

  const levelStyles = {
    critical: { bar: 'bg-red-500', badge: 'bg-red-100 text-red-700', label: 'Critical' },
    low: { bar: 'bg-amber-500', badge: 'bg-amber-100 text-amber-700', label: 'Low' },
    ok: { bar: 'bg-emerald-500', badge: 'bg-emerald-100 text-emerald-700', label: 'In Stock' },
  };

  function openCreate() { setSelected(null); setForm({ category: 'Cement', unit: 'Bags', tax: 8, minStock: 50 }); setShowModal(true); }
  function openEdit(m: Material) { setSelected(m); setForm(m); setShowModal(true); }

  function handleSave() {
    if (!form.name) { onToast('Material name required', 'error'); return; }
    if (selected) {
      setMaterials((prev) => prev.map((m) => m.id === selected.id ? { ...m, ...form } as Material : m));
      onToast('Material updated', 'success');
    } else {
      setMaterials((prev) => [{ id: `m${Date.now()}`, lastUpdated: new Date().toISOString().slice(0, 10), ...form } as Material, ...prev]);
      onToast('Material added', 'success');
    }
    setShowModal(false);
  }

  const F = (field: keyof Material, val: string | number) => setForm((f) => ({ ...f, [field]: val }));

  return (
    <div className="p-6 space-y-5">
      {/* Low stock alerts */}
      {lowStock.length > 0 && (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
          <AlertTriangle size={18} className="text-amber-500 flex-shrink-0 mt-0.5" />
          <div>
            <p className="text-sm font-semibold text-amber-800">Low Stock Alert — {lowStock.length} item{lowStock.length > 1 ? 's' : ''} need restocking</p>
            <p className="text-xs text-amber-600 mt-0.5">{lowStock.map((m) => m.name).join(' · ')}</p>
          </div>
        </div>
      )}

      {/* Summary */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: 'Total Items', value: materials.length, color: 'text-slate-900' },
          { label: 'In Stock', value: materials.filter((m) => m.stock > m.minStock).length, color: 'text-emerald-600' },
          { label: 'Low / Critical', value: lowStock.length, color: 'text-red-600' },
          { label: 'Est. Value', value: `$${(materials.reduce((s, m) => s + m.stock * m.rate, 0) / 1000).toFixed(0)}K`, color: 'text-blue-600' },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p className={`text-xl font-bold ${s.color}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
            <p className="text-xs text-slate-400 mt-0.5">{s.label}</p>
          </div>
        ))}
      </div>

      {/* Toolbar */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2.5 flex-1">
          <Search size={15} className="text-slate-400" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search materials or suppliers..." className="flex-1 text-sm outline-none text-slate-700 placeholder-slate-400" />
        </div>
        <div className="flex gap-2 flex-wrap">
          {categories.map((c) => (
            <button key={c} onClick={() => setCategory(c)}
              className={`px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${category === c ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'}`}>
              {c}
            </button>
          ))}
        </div>
        <button onClick={openCreate} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
          <Plus size={16} /> Add Material
        </button>
      </div>

      {/* Table */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-100">
                <th className="text-left px-5 py-3.5 text-xs font-semibold text-slate-500">Material</th>
                <th className="text-left px-4 py-3.5 text-xs font-semibold text-slate-500">Category</th>
                <th className="text-left px-4 py-3.5 text-xs font-semibold text-slate-500">Supplier</th>
                <th className="text-right px-4 py-3.5 text-xs font-semibold text-slate-500">Stock</th>
                <th className="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 min-w-[120px]">Level</th>
                <th className="text-right px-4 py-3.5 text-xs font-semibold text-slate-500">Rate</th>
                <th className="text-right px-4 py-3.5 text-xs font-semibold text-slate-500">Value</th>
                <th className="px-4 py-3.5 text-xs font-semibold text-slate-500 text-center">Status</th>
                <th className="px-4 py-3.5 w-20" />
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {filtered.map((m) => {
                const level = stockLevel(m);
                const style = levelStyles[level];
                const pct = Math.min((m.stock / (m.minStock * 2)) * 100, 100);
                return (
                  <tr key={m.id} className="hover:bg-slate-50/50 transition-colors">
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                          <Package size={14} className="text-blue-500" />
                        </div>
                        <div>
                          <p className="font-semibold text-slate-800 text-sm">{m.name}</p>
                          <p className="text-[11px] text-slate-400">{m.unit}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3.5">
                      <span className="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-lg font-medium">{m.category}</span>
                    </td>
                    <td className="px-4 py-3.5 text-sm text-slate-600">{m.supplier}</td>
                    <td className="px-4 py-3.5 text-right">
                      <span className={`font-bold font-mono text-sm ${level === 'critical' ? 'text-red-600' : level === 'low' ? 'text-amber-600' : 'text-slate-800'}`}>{m.stock.toLocaleString()}</span>
                    </td>
                    <td className="px-4 py-3.5 min-w-[120px]">
                      <div className="h-1.5 bg-slate-100 rounded-full mb-1">
                        <div className={`h-1.5 rounded-full transition-all ${style.bar}`} style={{ width: `${pct}%` }} />
                      </div>
                      <span className="text-[10px] text-slate-400">Min: {m.minStock}</span>
                    </td>
                    <td className="px-4 py-3.5 text-right text-sm font-mono font-semibold text-slate-700">${m.rate.toFixed(2)}</td>
                    <td className="px-4 py-3.5 text-right text-sm font-mono font-semibold text-slate-700">${(m.stock * m.rate).toLocaleString('en-US', { maximumFractionDigits: 0 })}</td>
                    <td className="px-4 py-3.5 text-center">
                      <span className={`text-[11px] font-semibold px-2.5 py-1 rounded-lg ${style.badge}`}>{style.label}</span>
                    </td>
                    <td className="px-4 py-3.5">
                      <div className="flex justify-end gap-1">
                        <button onClick={() => openEdit(m)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"><Edit2 size={13} /></button>
                        <button onClick={() => { setMaterials((prev) => prev.filter((x) => x.id !== m.id)); onToast('Deleted', 'info'); }} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"><Trash2 size={13} /></button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      <Modal open={showModal} onClose={() => setShowModal(false)} title={selected ? 'Edit Material' : 'Add Material'} size="lg">
        <div className="grid grid-cols-2 gap-4">
          {[
            { label: 'Material Name', field: 'name' as const, span: 2 },
            { label: 'Category', field: 'category' as const },
            { label: 'Unit', field: 'unit' as const },
            { label: 'Current Stock', field: 'stock' as const, type: 'number' },
            { label: 'Minimum Stock', field: 'minStock' as const, type: 'number' },
            { label: 'Rate per Unit ($)', field: 'rate' as const, type: 'number' },
            { label: 'Supplier', field: 'supplier' as const },
          ].map(({ label, field, span, type }) => (
            <div key={field} className={span === 2 ? 'col-span-2' : ''}>
              <label className="block text-xs font-semibold text-slate-600 mb-1.5">{label}</label>
              {field === 'category' ? (
                <select value={(form as Record<string, string | number>)[field] as string || ''} onChange={(e) => F(field, e.target.value)} className={input}>
                  {['Cement', 'Steel', 'Aggregate', 'Masonry', 'Timber', 'Plumbing', 'Electrical', 'Finishing', 'Other'].map((c) => <option key={c}>{c}</option>)}
                </select>
              ) : (
                <input type={type || 'text'} value={(form as Record<string, string | number>)[field] as string || ''} onChange={(e) => F(field, type === 'number' ? Number(e.target.value) : e.target.value)} className={input} />
              )}
            </div>
          ))}
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={handleSave} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">{selected ? 'Save Changes' : 'Add Material'}</button>
        </div>
      </Modal>
    </div>
  );
}
