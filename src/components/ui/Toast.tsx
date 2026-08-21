import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-react';
import type { Toast as ToastType } from '../../types';

interface ToastContainerProps {
  toasts: ToastType[];
  onRemove: (id: string) => void;
}

const icons = {
  success: { Icon: CheckCircle, cls: 'text-emerald-500' },
  error: { Icon: XCircle, cls: 'text-red-500' },
  warning: { Icon: AlertTriangle, cls: 'text-amber-500' },
  info: { Icon: Info, cls: 'text-blue-500' },
};

const borders = {
  success: 'border-emerald-200 bg-emerald-50',
  error: 'border-red-200 bg-red-50',
  warning: 'border-amber-200 bg-amber-50',
  info: 'border-blue-200 bg-blue-50',
};

export default function ToastContainer({ toasts, onRemove }: ToastContainerProps) {
  return (
    <div className="fixed bottom-6 right-6 z-[100] flex flex-col gap-2 w-80">
      {toasts.map((t) => {
        const { Icon, cls } = icons[t.type];
        return (
          <div
            key={t.id}
            className={`flex items-start gap-3 p-3 rounded-xl border shadow-lg ${borders[t.type]}`}
            style={{ animation: 'toastIn 0.3s ease-out' }}
          >
            <Icon size={18} className={`${cls} flex-shrink-0 mt-0.5`} />
            <p className="flex-1 text-sm text-slate-700 font-medium">{t.message}</p>
            <button onClick={() => onRemove(t.id)} className="text-slate-400 hover:text-slate-600">
              <X size={14} />
            </button>
          </div>
        );
      })}
      <style>{`@keyframes toastIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }`}</style>
    </div>
  );
}
