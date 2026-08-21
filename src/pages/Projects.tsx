import { useState } from 'react';
import { Plus, Search, Filter, MapPin, Calendar, DollarSign, Edit2, Trash2, Eye, TrendingUp, Users } from 'lucide-react';
import Modal from '../components/ui/Modal';
import { projects as initialProjects } from '../data/mockData';
import type { Project } from '../types';

const statusColors: Record<string, string> = {
  active: 'bg-blue-100 text-blue-700 border-blue-200',
  completed: 'bg-emerald-100 text-emerald-700 border-emerald-200',
  'on-hold': 'bg-amber-100 text-amber-700 border-amber-200',
  planning: 'bg-slate-100 text-slate-600 border-slate-200',
};
const statusDots: Record<string, string> = {
  active: 'bg-blue-500', completed: 'bg-emerald-500', 'on-hold': 'bg-amber-500', planning: 'bg-slate-400',
};
const typeColors: Record<string, string> = {
  'Residential High-Rise': 'bg-blue-50 text-blue-600',
  'Commercial Complex': 'bg-purple-50 text-purple-600',
  'Luxury Villa': 'bg-amber-50 text-amber-600',
  Renovation: 'bg-teal-50 text-teal-600',
  Institutional: 'bg-indigo-50 text-indigo-600',
};

const fmt = (n: number) => n >= 1000000 ? `$${(n / 1000000).toFixed(1)}M` : `$${(n / 1000).toFixed(0)}K`;

const defaultForm: Partial<Project> = {
  name: '', clientName: '', siteAddress: '', type: 'Residential High-Rise',
  status: 'planning', budget: 0, area: 0, floors: 0, manager: 'James Carter', description: '', tags: [],
};

export default function Projects({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [projects, setProjects] = useState(initialProjects);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState<string>('all');
  const [showModal, setShowModal] = useState(false);
  const [selected, setSelected] = useState<Project | null>(null);
  const [detail, setDetail] = useState<Project | null>(null);
  const [form, setForm] = useState<Partial<Project>>(defaultForm);

  const filtered = projects.filter((p) => {
    const matchSearch = p.name.toLowerCase().includes(search.toLowerCase()) || p.clientName.toLowerCase().includes(search.toLowerCase());
    const matchFilter = filter === 'all' || p.status === filter;
    return matchSearch && matchFilter;
  });

  function openCreate() { setSelected(null); setForm(defaultForm); setShowModal(true); }
  function openEdit(p: Project) { setSelected(p); setForm(p); setShowModal(true); }

  function handleSave() {
    if (!form.name || !form.clientName) { onToast('Name and client are required', 'error'); return; }
    if (selected) {
      setProjects((prev) => prev.map((p) => p.id === selected.id ? { ...p, ...form } as Project : p));
      onToast('Project updated successfully', 'success');
    } else {
      const newP: Project = {
        id: `p${Date.now()}`, clientId: `c${Date.now()}`, spent: 0, progress: 0,
        startDate: new Date().toISOString().slice(0, 10), endDate: '',
        ...form as Project,
      };
      setProjects((prev) => [newP, ...prev]);
      onToast('Project created successfully', 'success');
    }
    setShowModal(false);
  }

  function handleDelete(id: string) {
    setProjects((prev) => prev.filter((p) => p.id !== id));
    onToast('Project deleted', 'info');
  }

  const F = (field: keyof Project, val: unknown) => setForm((f) => ({ ...f, [field]: val }));

  if (detail) {
    return <ProjectDetail project={detail} onBack={() => setDetail(null)} onEdit={() => { openEdit(detail); setDetail(null); }} />;
  }

  return (
    <div className="p-6 space-y-5">
      {/* Toolbar */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2.5 flex-1">
          <Search size={15} className="text-slate-400" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search projects or clients..." className="flex-1 text-sm outline-none text-slate-700 placeholder-slate-400" />
        </div>
        <div className="flex gap-2">
          <select value={filter} onChange={(e) => setFilter(e.target.value)} className="bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 outline-none cursor-pointer">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="planning">Planning</option>
            <option value="on-hold">On Hold</option>
            <option value="completed">Completed</option>
          </select>
          <button onClick={openCreate} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
            <Plus size={16} /> New Project
          </button>
        </div>
      </div>

      {/* Summary bar */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {(['all', 'active', 'planning', 'on-hold', 'completed'] as const).slice(1).map((s) => {
          const count = projects.filter((p) => p.status === s).length;
          return (
            <button key={s} onClick={() => setFilter(s === filter ? 'all' : s)}
              className={`bg-white rounded-xl p-3 border text-left transition-all ${filter === s ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-100 hover:border-slate-200'}`}>
              <div className="flex items-center gap-2 mb-1">
                <span className={`w-2 h-2 rounded-full ${statusDots[s]}`} />
                <span className="text-xs text-slate-500 capitalize">{s.replace('-', ' ')}</span>
              </div>
              <p className="text-xl font-bold text-slate-900" style={{ fontFamily: 'var(--font-display)' }}>{count}</p>
            </button>
          );
        })}
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        {filtered.map((p) => (
          <div key={p.id} className="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden group">
            <div className="h-2" style={{ background: p.status === 'active' ? '#1D4ED8' : p.status === 'completed' ? '#10B981' : p.status === 'on-hold' ? '#F59E0B' : '#94A3B8' }} />
            <div className="p-5">
              <div className="flex items-start justify-between gap-2 mb-3">
                <div className="flex-1 min-w-0">
                  <span className={`inline-flex text-[11px] font-semibold px-2 py-0.5 rounded-md border mb-2 ${statusColors[p.status]}`}>
                    <span className={`w-1.5 h-1.5 rounded-full ${statusDots[p.status]} mr-1.5 mt-0.5`} />{p.status.replace('-', ' ')}
                  </span>
                  <h3 className="font-bold text-slate-900 text-base leading-tight truncate" style={{ fontFamily: 'var(--font-display)' }}>{p.name}</h3>
                  <p className="text-xs text-slate-500 mt-0.5">{p.clientName}</p>
                </div>
                <span className={`text-xs font-semibold px-2 py-1 rounded-lg whitespace-nowrap ${typeColors[p.type] || 'bg-slate-50 text-slate-600'}`}>{p.type}</span>
              </div>

              <div className="flex items-center gap-1 text-xs text-slate-400 mb-4">
                <MapPin size={11} /><span className="truncate">{p.siteAddress}</span>
              </div>

              <div className="mb-3">
                <div className="flex justify-between text-xs text-slate-500 mb-1">
                  <span>Progress</span><span className="font-semibold text-slate-700">{p.progress}%</span>
                </div>
                <div className="h-1.5 bg-slate-100 rounded-full">
                  <div className="h-1.5 rounded-full transition-all" style={{ width: `${p.progress}%`, background: p.progress >= 80 ? '#10B981' : '#1D4ED8' }} />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3 mb-4">
                <div className="bg-slate-50 rounded-xl p-2.5">
                  <p className="text-[10px] text-slate-400 mb-0.5">Budget</p>
                  <p className="text-sm font-bold text-slate-900">{fmt(p.budget)}</p>
                </div>
                <div className="bg-slate-50 rounded-xl p-2.5">
                  <p className="text-[10px] text-slate-400 mb-0.5">Spent</p>
                  <p className="text-sm font-bold text-slate-900">{fmt(p.spent)}</p>
                </div>
              </div>

              <div className="flex items-center justify-between pt-3 border-t border-slate-100">
                <div className="flex items-center gap-1 text-xs text-slate-400">
                  <Calendar size={11} /><span>{p.startDate} → {p.endDate || 'TBD'}</span>
                </div>
                <div className="flex gap-1">
                  <button onClick={() => setDetail(p)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"><Eye size={14} /></button>
                  <button onClick={() => openEdit(p)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"><Edit2 size={14} /></button>
                  <button onClick={() => handleDelete(p.id)} className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"><Trash2 size={14} /></button>
                </div>
              </div>
            </div>
          </div>
        ))}

        {filtered.length === 0 && (
          <div className="col-span-3 flex flex-col items-center justify-center py-16 text-center">
            <div className="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
              <Search size={24} className="text-slate-400" />
            </div>
            <p className="font-semibold text-slate-700">No projects found</p>
            <p className="text-sm text-slate-400 mt-1">Try a different search or filter</p>
          </div>
        )}
      </div>

      {/* Modal */}
      <Modal open={showModal} onClose={() => setShowModal(false)} title={selected ? 'Edit Project' : 'Create New Project'} size="lg">
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Field label="Project Name *"><input value={form.name || ''} onChange={(e) => F('name', e.target.value)} className={input} placeholder="e.g. Skyline Tower Phase 2" /></Field>
          <Field label="Client Name *"><input value={form.clientName || ''} onChange={(e) => F('clientName', e.target.value)} className={input} placeholder="e.g. Metro Developers Ltd." /></Field>
          <Field label="Site Address" className="sm:col-span-2"><input value={form.siteAddress || ''} onChange={(e) => F('siteAddress', e.target.value)} className={input} placeholder="Full address" /></Field>
          <Field label="Project Type">
            <select value={form.type || ''} onChange={(e) => F('type', e.target.value)} className={input}>
              {['Residential High-Rise', 'Commercial Complex', 'Luxury Villa', 'Renovation', 'Institutional', 'Industrial'].map((t) => <option key={t}>{t}</option>)}
            </select>
          </Field>
          <Field label="Status">
            <select value={form.status || 'planning'} onChange={(e) => F('status', e.target.value)} className={input}>
              <option value="planning">Planning</option>
              <option value="active">Active</option>
              <option value="on-hold">On Hold</option>
              <option value="completed">Completed</option>
            </select>
          </Field>
          <Field label="Budget ($)"><input type="number" value={form.budget || ''} onChange={(e) => F('budget', Number(e.target.value))} className={input} /></Field>
          <Field label="Area (sq.ft)"><input type="number" value={form.area || ''} onChange={(e) => F('area', Number(e.target.value))} className={input} /></Field>
          <Field label="Floors"><input type="number" value={form.floors || ''} onChange={(e) => F('floors', Number(e.target.value))} className={input} /></Field>
          <Field label="Project Manager"><input value={form.manager || ''} onChange={(e) => F('manager', e.target.value)} className={input} /></Field>
          <Field label="Start Date"><input type="date" value={form.startDate || ''} onChange={(e) => F('startDate', e.target.value)} className={input} /></Field>
          <Field label="End Date"><input type="date" value={form.endDate || ''} onChange={(e) => F('endDate', e.target.value)} className={input} /></Field>
          <Field label="Description" className="sm:col-span-2">
            <textarea value={form.description || ''} onChange={(e) => F('description', e.target.value)} className={`${input} h-20 resize-none`} placeholder="Project overview..." />
          </Field>
        </div>
        <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100">
          <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
          <button onClick={handleSave} className="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">{selected ? 'Save Changes' : 'Create Project'}</button>
        </div>
      </Modal>
    </div>
  );
}

function ProjectDetail({ project: p, onBack, onEdit }: { project: Project; onBack: () => void; onEdit: () => void }) {
  const remaining = p.budget - p.spent;
  return (
    <div className="p-6 space-y-5">
      <div className="flex items-center gap-3">
        <button onClick={onBack} className="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-700 transition-colors">←</button>
        <div className="flex-1">
          <h2 className="font-bold text-slate-900 text-lg" style={{ fontFamily: 'var(--font-display)' }}>{p.name}</h2>
          <p className="text-sm text-slate-500">{p.clientName} · {p.type}</p>
        </div>
        <button onClick={onEdit} className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors"><Edit2 size={13} /> Edit</button>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: 'Budget', value: `$${p.budget.toLocaleString()}`, color: 'text-slate-900' },
          { label: 'Spent', value: `$${p.spent.toLocaleString()}`, color: 'text-rose-600' },
          { label: 'Remaining', value: `$${remaining.toLocaleString()}`, color: remaining > 0 ? 'text-emerald-600' : 'text-red-600' },
          { label: 'Progress', value: `${p.progress}%`, color: 'text-blue-600' },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
            <p className="text-xs text-slate-400 mb-1">{s.label}</p>
            <p className={`text-xl font-bold ${s.color}`} style={{ fontFamily: 'var(--font-display)' }}>{s.value}</p>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-3">
          <h4 className="font-bold text-slate-900 text-sm mb-3" style={{ fontFamily: 'var(--font-display)' }}>Project Details</h4>
          {[
            ['Site Address', p.siteAddress],
            ['Area', `${p.area.toLocaleString()} sq.ft`],
            ['Floors', p.floors],
            ['Manager', p.manager],
            ['Start Date', p.startDate],
            ['End Date', p.endDate || 'TBD'],
          ].map(([k, v]) => (
            <div key={String(k)} className="flex justify-between text-sm">
              <span className="text-slate-400">{k}</span>
              <span className="text-slate-700 font-medium text-right max-w-[60%]">{String(v)}</span>
            </div>
          ))}
        </div>
        <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <h4 className="font-bold text-slate-900 text-sm mb-3" style={{ fontFamily: 'var(--font-display)' }}>Description</h4>
          <p className="text-sm text-slate-600 leading-relaxed">{p.description}</p>
          <div className="flex flex-wrap gap-1.5 mt-4">
            {p.tags.map((tag) => (
              <span key={tag} className="text-xs bg-blue-50 text-blue-600 font-medium px-2.5 py-1 rounded-lg">{tag}</span>
            ))}
          </div>
        </div>
      </div>

      <div className="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <h4 className="font-bold text-slate-900 text-sm mb-4" style={{ fontFamily: 'var(--font-display)' }}>Budget Utilization</h4>
        <div className="h-4 bg-slate-100 rounded-full overflow-hidden">
          <div className="h-4 rounded-full transition-all duration-700" style={{ width: `${Math.min(p.progress, 100)}%`, background: 'linear-gradient(90deg, #1D4ED8, #3B82F6)' }} />
        </div>
        <div className="flex justify-between text-xs text-slate-400 mt-2">
          <span>$0</span><span className="text-slate-700 font-semibold">{p.progress}% utilized</span><span>${p.budget.toLocaleString()}</span>
        </div>
      </div>
    </div>
  );
}

function Field({ label, children, className = '' }: { label: string; children: React.ReactNode; className?: string }) {
  return (
    <div className={className}>
      <label className="block text-xs font-semibold text-slate-600 mb-1.5">{label}</label>
      {children}
    </div>
  );
}

const input = 'w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all';
