import { useState } from 'react';
import { User, Building2, Bell, Shield, Palette, Save } from 'lucide-react';

const tabs = [
  { id: 'profile', label: 'Profile', icon: User },
  { id: 'company', label: 'Company', icon: Building2 },
  { id: 'notifications', label: 'Notifications', icon: Bell },
  { id: 'security', label: 'Security', icon: Shield },
  { id: 'appearance', label: 'Appearance', icon: Palette },
];

const inp = 'w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all';

export default function Settings({ onToast }: { onToast: (msg: string, type?: string) => void }) {
  const [tab, setTab] = useState('profile');
  const [notifs, setNotifs] = useState({
    email: true, sms: false, projectUpdates: true, lowStock: true, paymentDue: true, dailyReport: false,
  });

  return (
    <div className="p-6">
      <div className="flex gap-5 flex-col lg:flex-row">
        {/* Sidebar */}
        <div className="lg:w-48 flex-shrink-0">
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-2">
            {tabs.map((t) => {
              const Icon = t.icon;
              return (
                <button key={t.id} onClick={() => setTab(t.id)}
                  className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all mb-0.5 ${
                    tab === t.id ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'
                  }`}>
                  <Icon size={16} />
                  {t.label}
                </button>
              );
            })}
          </div>
        </div>

        {/* Content */}
        <div className="flex-1 min-w-0">
          {tab === 'profile' && (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
              <h3 className="font-bold text-slate-900 text-base" style={{ fontFamily: 'var(--font-display)' }}>Profile Information</h3>

              <div className="flex items-center gap-4">
                <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xl font-bold">JC</div>
                <div>
                  <p className="text-sm font-semibold text-slate-700">James Carter</p>
                  <p className="text-xs text-slate-400">Project Manager · Admin</p>
                  <button className="text-xs text-blue-600 mt-1 hover:underline">Change Photo</button>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {[['First Name', 'James'], ['Last Name', 'Carter'], ['Email', 'james.carter@trackzo.io'], ['Phone', '+1 212-555-0101'], ['Role', 'Project Manager'], ['Department', 'Operations']].map(([label, val]) => (
                  <div key={String(label)}>
                    <label className="block text-xs font-semibold text-slate-500 mb-1.5">{label}</label>
                    <input defaultValue={String(val)} className={inp} />
                  </div>
                ))}
              </div>

              <button onClick={() => onToast('Profile saved successfully', 'success')} className="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <Save size={15} /> Save Profile
              </button>
            </div>
          )}

          {tab === 'company' && (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
              <h3 className="font-bold text-slate-900 text-base" style={{ fontFamily: 'var(--font-display)' }}>Company Details</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {[
                  ['Company Name', 'Carter & Associates Construction'],
                  ['Registration No.', 'NY-CON-2019-8821'],
                  ['Tax ID (EIN)', '12-3456789'],
                  ['License No.', 'NY-GC-4421-B'],
                  ['Address', '1400 Broadway Suite 2100'],
                  ['City', 'New York, NY 10018'],
                  ['Phone', '+1 212-555-0100'],
                  ['Website', 'www.carterassoc.construction'],
                ].map(([label, val]) => (
                  <div key={String(label)}>
                    <label className="block text-xs font-semibold text-slate-500 mb-1.5">{label}</label>
                    <input defaultValue={String(val)} className={inp} />
                  </div>
                ))}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 mb-1.5">Company Description</label>
                <textarea rows={3} defaultValue="Full-service general contractor specializing in luxury residential and commercial construction across the New York tri-state area." className={`${inp} resize-none`} />
              </div>
              <button onClick={() => onToast('Company info updated', 'success')} className="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <Save size={15} /> Save Company Info
              </button>
            </div>
          )}

          {tab === 'notifications' && (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
              <h3 className="font-bold text-slate-900 text-base" style={{ fontFamily: 'var(--font-display)' }}>Notification Preferences</h3>
              <div className="space-y-1">
                {[
                  { key: 'email' as const, label: 'Email Notifications', desc: 'Receive alerts via email' },
                  { key: 'sms' as const, label: 'SMS Notifications', desc: 'Receive alerts via SMS' },
                  { key: 'projectUpdates' as const, label: 'Project Updates', desc: 'Status changes, milestones' },
                  { key: 'lowStock' as const, label: 'Low Stock Alerts', desc: 'When materials drop below minimum' },
                  { key: 'paymentDue' as const, label: 'Payment Reminders', desc: 'Invoices due and overdue alerts' },
                  { key: 'dailyReport' as const, label: 'Daily Summary Report', desc: 'End-of-day activity digest' },
                ].map(({ key, label, desc }) => (
                  <div key={key} className="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                    <div>
                      <p className="text-sm font-semibold text-slate-700">{label}</p>
                      <p className="text-xs text-slate-400">{desc}</p>
                    </div>
                    <button
                      onClick={() => setNotifs((n) => ({ ...n, [key]: !n[key] }))}
                      className={`relative w-10 h-5 rounded-full transition-colors ${notifs[key] ? 'bg-blue-600' : 'bg-slate-200'}`}
                    >
                      <span className={`absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all ${notifs[key] ? 'left-5' : 'left-0.5'}`} />
                    </button>
                  </div>
                ))}
              </div>
              <button onClick={() => onToast('Notification preferences saved', 'success')} className="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <Save size={15} /> Save Preferences
              </button>
            </div>
          )}

          {tab === 'security' && (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
              <h3 className="font-bold text-slate-900 text-base" style={{ fontFamily: 'var(--font-display)' }}>Security Settings</h3>
              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1.5">Current Password</label>
                  <input type="password" className={inp} placeholder="••••••••" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1.5">New Password</label>
                  <input type="password" className={inp} placeholder="Min. 8 characters" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1.5">Confirm New Password</label>
                  <input type="password" className={inp} placeholder="Repeat new password" />
                </div>
              </div>
              <div className="bg-blue-50 rounded-xl p-4">
                <p className="text-sm font-semibold text-blue-800 mb-1">Two-Factor Authentication</p>
                <p className="text-xs text-blue-600 mb-3">Add an extra layer of security to your account.</p>
                <button className="text-xs font-semibold text-white bg-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">Enable 2FA</button>
              </div>
              <button onClick={() => onToast('Password updated successfully', 'success')} className="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <Save size={15} /> Update Password
              </button>
            </div>
          )}

          {tab === 'appearance' && (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
              <h3 className="font-bold text-slate-900 text-base" style={{ fontFamily: 'var(--font-display)' }}>Appearance</h3>
              <div>
                <p className="text-xs font-semibold text-slate-500 mb-3">Theme</p>
                <div className="grid grid-cols-3 gap-3">
                  {[
                    { label: 'Light', preview: 'bg-white border-2 border-blue-500' },
                    { label: 'Dark', preview: 'bg-slate-800' },
                    { label: 'System', preview: 'bg-gradient-to-r from-white to-slate-800' },
                  ].map(({ label, preview }) => (
                    <button key={label} onClick={() => onToast(`Theme set to ${label} (demo)`, 'info')}
                      className={`p-3 rounded-xl border ${label === 'Light' ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200 hover:border-slate-300'} flex flex-col items-center gap-2`}>
                      <div className={`w-full h-12 rounded-lg ${preview}`} />
                      <span className="text-xs font-semibold text-slate-600">{label}</span>
                    </button>
                  ))}
                </div>
              </div>
              <div>
                <p className="text-xs font-semibold text-slate-500 mb-3">Sidebar Style</p>
                <div className="flex gap-2">
                  {['Compact', 'Full'].map((s) => (
                    <button key={s} onClick={() => onToast(`Sidebar set to ${s} (demo)`, 'info')}
                      className={`px-4 py-2 text-sm font-semibold rounded-xl border transition-colors ${s === 'Full' ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'}`}>
                      {s}
                    </button>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
