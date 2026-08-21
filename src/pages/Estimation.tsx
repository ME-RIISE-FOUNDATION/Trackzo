import { useState } from 'react';
import { Plus, Trash2, FileText, Download, Calculator } from 'lucide-react';
import { defaultEstimationItems } from '../data/mockData';
import type { EstimationItem } from '../types';

const input = 'w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all';

export default function Estimation({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [items, setItems] = useState<EstimationItem[]>(defaultEstimationItems);
  const [projectName, setProjectName] = useState('Skyline Tower Residences');
  const [clientName, setClientName] = useState('Metro Developers Ltd.');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [notes, setNotes] = useState('All prices inclusive of material delivery. Labour costs billed separately.');

  function addItem() {
    setItems((prev) => [...prev, { id: `ei${Date.now()}`, description: '', unit: 'Sq.Ft.', qty: 0, rate: 0, tax: 8, discount: 0 }]);
  }

  function removeItem(id: string) { setItems((prev) => prev.filter((i) => i.id !== id)); }

  function updateItem(id: string, field: keyof EstimationItem, value: string | number) {
    setItems((prev) => prev.map((i) => i.id === id ? { ...i, [field]: value } : i));
  }

  const getSubtotal = (item: EstimationItem) => item.qty * item.rate;
  const getDiscount = (item: EstimationItem) => getSubtotal(item) * (item.discount / 100);
  const getTax = (item: EstimationItem) => (getSubtotal(item) - getDiscount(item)) * (item.tax / 100);
  const getTotal = (item: EstimationItem) => getSubtotal(item) - getDiscount(item) + getTax(item);

  const grandSubtotal = items.reduce((s, i) => s + getSubtotal(i), 0);
  const grandDiscount = items.reduce((s, i) => s + getDiscount(i), 0);
  const grandTax = items.reduce((s, i) => s + getTax(i), 0);
  const grandTotal = items.reduce((s, i) => s + getTotal(i), 0);

  const fmt = (n: number) => `$${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

  return (
    <div className="p-6 space-y-5">
      {/* Header info */}
      <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div className="flex items-center gap-3 mb-4">
          <div className="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
            <Calculator size={18} className="text-white" />
          </div>
          <div>
            <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>New Estimate</h3>
            <p className="text-xs text-slate-400">Fill in project details and line items below</p>
          </div>
          <div className="ml-auto flex gap-2">
            <button onClick={() => onToast('Estimate saved as draft', 'success')} className="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
              <FileText size={14} /> Save Draft
            </button>
            <button onClick={() => onToast('PDF generation would open print dialog in production', 'info')} className="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
              <Download size={14} /> Export PDF
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1.5">Project Name</label>
            <input value={projectName} onChange={(e) => setProjectName(e.target.value)} className={input} />
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1.5">Client Name</label>
            <input value={clientName} onChange={(e) => setClientName(e.target.value)} className={input} />
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-500 mb-1.5">Estimate Date</label>
            <input type="date" value={date} onChange={(e) => setDate(e.target.value)} className={input} />
          </div>
        </div>
      </div>

      {/* Line items */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>Estimation Line Items</h3>
          <button onClick={addItem} className="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
            <Plus size={14} /> Add Item
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50 text-left">
                <th className="px-4 py-3 text-xs font-semibold text-slate-500 w-8">#</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 min-w-[200px]">Description</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-24">Unit</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-20">Qty</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-24">Rate ($)</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-20">Tax (%)</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-24">Disc (%)</th>
                <th className="px-3 py-3 text-xs font-semibold text-slate-500 w-28 text-right">Total</th>
                <th className="px-3 py-3 w-10" />
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {items.map((item, idx) => (
                <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-4 py-2.5 text-xs text-slate-400 font-mono">{idx + 1}</td>
                  <td className="px-3 py-2.5">
                    <input value={item.description} onChange={(e) => updateItem(item.id, 'description', e.target.value)} className={input} placeholder="Item description" />
                  </td>
                  <td className="px-3 py-2.5">
                    <select value={item.unit} onChange={(e) => updateItem(item.id, 'unit', e.target.value)} className={input}>
                      {['Sq.Ft.', 'Sq.Mt.', 'Cubic Yard', 'Cubic Meter', 'Metric Ton', 'Board Ft', 'Bags', 'Nos.', 'L.S.', 'Running Ft'].map((u) => <option key={u}>{u}</option>)}
                    </select>
                  </td>
                  <td className="px-3 py-2.5">
                    <input type="number" value={item.qty} onChange={(e) => updateItem(item.id, 'qty', Number(e.target.value))} className={input} min="0" />
                  </td>
                  <td className="px-3 py-2.5">
                    <input type="number" value={item.rate} onChange={(e) => updateItem(item.id, 'rate', Number(e.target.value))} className={input} min="0" step="0.01" />
                  </td>
                  <td className="px-3 py-2.5">
                    <input type="number" value={item.tax} onChange={(e) => updateItem(item.id, 'tax', Number(e.target.value))} className={input} min="0" max="100" />
                  </td>
                  <td className="px-3 py-2.5">
                    <input type="number" value={item.discount} onChange={(e) => updateItem(item.id, 'discount', Number(e.target.value))} className={input} min="0" max="100" />
                  </td>
                  <td className="px-3 py-2.5 text-right">
                    <span className="font-semibold text-slate-900 font-mono text-xs">{fmt(getTotal(item))}</span>
                  </td>
                  <td className="px-3 py-2.5">
                    <button onClick={() => removeItem(item.id)} className="w-6 h-6 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors">
                      <Trash2 size={13} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Summary */}
      <div className="flex justify-end">
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 w-full max-w-sm space-y-2">
          <h4 className="font-bold text-slate-900 text-sm mb-3" style={{ fontFamily: 'var(--font-display)' }}>Estimate Summary</h4>
          <SummaryRow label="Subtotal" value={fmt(grandSubtotal)} />
          <SummaryRow label="Total Discount" value={`-${fmt(grandDiscount)}`} cls="text-amber-600" />
          <SummaryRow label="Total Tax" value={`+${fmt(grandTax)}`} cls="text-slate-600" />
          <div className="border-t border-slate-100 pt-3 mt-2 flex justify-between items-center">
            <span className="font-bold text-slate-900 text-sm">Grand Total</span>
            <span className="font-bold text-blue-700 text-xl font-mono" style={{ fontFamily: 'var(--font-display)' }}>{fmt(grandTotal)}</span>
          </div>
        </div>
      </div>

      {/* Notes */}
      <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <label className="block text-xs font-semibold text-slate-500 mb-2">Notes & Terms</label>
        <textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={3} className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-600 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-none" />
      </div>
    </div>
  );
}

function SummaryRow({ label, value, cls = 'text-slate-700' }: { label: string; value: string; cls?: string }) {
  return (
    <div className="flex justify-between text-sm">
      <span className="text-slate-400">{label}</span>
      <span className={`font-semibold font-mono ${cls}`}>{value}</span>
    </div>
  );
}
