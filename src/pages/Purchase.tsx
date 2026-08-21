import { useState } from 'react';
import { Plus, Search, ShoppingCart, CheckCircle, Clock, Truck, XCircle, Eye } from 'lucide-react';
import Modal from '../components/ui/Modal';
import { purchaseOrders as initialOrders } from '../data/mockData';
import type { PurchaseOrder } from '../types';

const statusConfig: Record<string, { icon: React.FC<{ size?: number; className?: string }>; cls: string; label: string }> = {
  pending: { icon: Clock, cls: 'bg-amber-100 text-amber-700', label: 'Pending' },
  approved: { icon: CheckCircle, cls: 'bg-blue-100 text-blue-700', label: 'Approved' },
  delivered: { icon: Truck, cls: 'bg-emerald-100 text-emerald-700', label: 'Delivered' },
  cancelled: { icon: XCircle, cls: 'bg-red-100 text-red-700', label: 'Cancelled' },
};

export default function Purchase({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [orders, setOrders] = useState(initialOrders);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('all');
  const [showModal, setShowModal] = useState(false);
  const [detail, setDetail] = useState<PurchaseOrder | null>(null);
  const [form, setForm] = useState({ supplier: '', item: '', qty: '', rate: '', expectedDate: '', projectId: 'p1' });

  const filtered = orders.filter((o) => {
    const ms = o.supplier.toLowerCase().includes(search.toLowerCase());
    const mf = filter === 'all' || o.status === filter;
    return ms && mf;
  });

  function handleCreate() {
    if (!form.supplier || !form.item) { onToast('Supplier and item required', 'error'); return; }
    const qty = Number(form.qty);
    const rate = Number(form.rate);
    const newO: PurchaseOrder = {
      id: `po${Date.now()}`, supplier: form.supplier,
      items: [{ name: form.item, qty, rate }],
      total: qty * rate,
      status: 'pending',
      date: new Date().toISOString().slice(0, 10),
      expectedDate: form.expectedDate,
      projectId: form.projectId,
    };
    setOrders((prev) => [newO, ...prev]);
    onToast('Purchase order created', 'success');
    setShowModal(false);
    setForm({ supplier: '', item: '', qty: '', rate: '', expectedDate: '', projectId: 'p1' });
  }

  function updateStatus(id: string, status: PurchaseOrder['status']) {
    setOrders((prev) => prev.map((o) => o.id === id ? { ...o, status } : o));
    onToast(`Order marked as ${status}`, 'success');
  }

  const totals = {
    all: orders.length,
    pending: orders.filter((o) => o.status === 'pending').length,
    approved: orders.filter((o) => o.status === 'approved').length,
    delivered: orders.filter((o) => o.status === 'delivered').length,
  };

  return (
    <div className="p-6 space-y-5">
      {/* Summary */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: 'Total Orders', value: totals.all, color: 'text-slate-900' },
          { label: 'Pending', value: totals.pending, color: 'text-amber-600' },
          { label: 'Approved', value: totals.approved, color: 'text-blue-600' },
          { label: 'Total Value', value: `$${(orders.reduce((s, o) => s + o.total, 0) / 1000).toFixed(0)}K`, color: 'text-emerald-600' },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p className={`text-2xl font-bold ${s.color}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
            <p className="text-xs text-slate-400 mt-0.5">{s.label}</p>
          </div>
        ))}
      </div>

      {/* Toolbar */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2.5 flex-1">
          <Search size={15} className="text-slate-400" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search suppliers..." className="flex-1 text-sm outline-none text-slate-700 placeholder-slate-400" />
        </div>
        <select value={filter} onChange={(e) => setFilter(e.target.value)} className="bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 outline-none">
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <button onClick={() => setShowModal(true)} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
          <Plus size={16} /> New Order
        </button>
      </div>

      {/* Orders list */}
      <div className="space-y-3">
        {filtered.map((order) => {
          const cfg = statusConfig[order.status];
          const Icon = cfg.icon;
          return (
            <div key={order.id} className="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
              <div className="flex items-start gap-4">
                <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                  <ShoppingCart size={18} className="text-blue-500" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 flex-wrap mb-1">
                    <h3 className="font-bold text-slate-900 text-sm" style={{ fontFamily: 'var(--font-display)' }}>{order.supplier}</h3>
                    <span className="text-[11px] text-slate-400 font-mono">{order.id.toUpperCase()}</span>
                    <span className={`text-[11px] font-semibold px-2 py-0.5 rounded-lg flex items-center gap-1 ${cfg.cls}`}>
                      <Icon size={10} />{cfg.label}
                    </span>
                  </div>
                  <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                    <span>Order date: {order.date}</span>
                    <span>Expected: {order.expectedDate}</span>
                    <span>Project: {order.projectId.toUpperCase()}</span>
                  </div>
                  <div className="mt-2 flex flex-wrap gap-1.5">
                    {order.items.map((item, i) => (
                      <span key={i} className="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                        {item.name} × {item.qty}
                      </span>
                    ))}
                  </div>
                </div>
                <div className="flex flex-col items-end gap-2 flex-shrink-0">
                  <span className="text-lg font-bold text-slate-900 font-mono" style={{ fontFamily: 'var(--font-display)' }}>
                    ${order.total.toLocaleString()}
                  </span>
                  <div className="flex gap-1">
                    <button onClick={() => setDetail(order)} className="text-xs font-semibold text-blue-600 hover:text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-1">
                      <Eye size={12} /> View
                    </button>
                    {order.status === 'pending' && (
                      <button onClick={() => updateStatus(order.id, 'approved')} className="text-xs font-semibold text-emerald-600 hover:text-emerald-700 px-2 py-1 rounded-lg hover:bg-emerald-50 transition-colors">
                        Approve
                      </button>
                    )}
                    {order.status === 'approved' && (
                      <button onClick={() => updateStatus(order.id, 'delivered')} className="text-xs font-semibold text-blue-600 hover:text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                        Mark Delivered
                      </button>
                    )}
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Create modal */}
      <Modal open={showModal} onClose={() => setShowModal(false)} title="Create Purchase Order">
        <div className="space-y-4">
          {[
            { label: 'Supplier Name', key: 'supplier' as const, placeholder: 'e.g. Atlas Cement Co.' },
            { label: 'Item Description', key: 'item' as const, placeholder: 'e.g. Portland Cement OPC 53' },
          ].map(({ label, key, placeholder }) => (
            <div key={key}>
              <label className="block text-xs font-semibold text-slate-600 mb-1.5">{label}</label>
              <input value={form[key]} onChange={(e) => setForm((f) => ({ ...f, [key]: e.target.value }))} placeholder={placeholder}
                className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400" />
            </div>
          ))}
          <div className="grid grid-cols-2 gap-4">
            {[{ label: 'Quantity', key: 'qty' as const }, { label: 'Rate ($)', key: 'rate' as const }].map(({ label, key }) => (
              <div key={key}>
                <label className="block text-xs font-semibold text-slate-600 mb-1.5">{label}</label>
                <input type="number" value={form[key]} onChange={(e) => setForm((f) => ({ ...f, [key]: e.target.value }))}
                  className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400" />
              </div>
            ))}
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-600 mb-1.5">Expected Delivery Date</label>
            <input type="date" value={form.expectedDate} onChange={(e) => setForm((f) => ({ ...f, expectedDate: e.target.value }))}
              className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400" />
          </div>
          {form.qty && form.rate && (
            <div className="bg-blue-50 rounded-xl p-3 flex justify-between items-center">
              <span className="text-sm text-blue-600 font-medium">Estimated Total</span>
              <span className="text-lg font-bold text-blue-700 font-mono">${(Number(form.qty) * Number(form.rate)).toLocaleString()}</span>
            </div>
          )}
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={handleCreate} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">Create Order</button>
        </div>
      </Modal>

      {/* Detail modal */}
      {detail && (
        <Modal open={!!detail} onClose={() => setDetail(null)} title={`Purchase Order — ${detail.id.toUpperCase()}`}>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-3">
              {[['Supplier', detail.supplier], ['Status', detail.status], ['Order Date', detail.date], ['Expected Date', detail.expectedDate]].map(([k, v]) => (
                <div key={String(k)} className="bg-slate-50 rounded-xl p-3">
                  <p className="text-xs text-slate-400 mb-1">{k}</p>
                  <p className="text-sm font-semibold text-slate-800 capitalize">{String(v)}</p>
                </div>
              ))}
            </div>
            <table className="w-full text-sm border border-slate-100 rounded-xl overflow-hidden">
              <thead className="bg-slate-50">
                <tr>
                  <th className="text-left px-4 py-2.5 text-xs font-semibold text-slate-500">Item</th>
                  <th className="text-right px-4 py-2.5 text-xs font-semibold text-slate-500">Qty</th>
                  <th className="text-right px-4 py-2.5 text-xs font-semibold text-slate-500">Rate</th>
                  <th className="text-right px-4 py-2.5 text-xs font-semibold text-slate-500">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50">
                {detail.items.map((item, i) => (
                  <tr key={i}>
                    <td className="px-4 py-3 text-slate-700">{item.name}</td>
                    <td className="px-4 py-3 text-right text-slate-600">{item.qty}</td>
                    <td className="px-4 py-3 text-right text-slate-600">${item.rate}</td>
                    <td className="px-4 py-3 text-right font-bold text-slate-900">${(item.qty * item.rate).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot className="bg-slate-50">
                <tr>
                  <td colSpan={3} className="px-4 py-2.5 text-sm font-bold text-slate-600 text-right">Grand Total</td>
                  <td className="px-4 py-2.5 text-right font-bold text-blue-700">${detail.total.toLocaleString()}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </Modal>
      )}
    </div>
  );
}
