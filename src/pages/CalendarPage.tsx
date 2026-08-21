import { useState } from 'react';
import { ChevronLeft, ChevronRight, Calendar } from 'lucide-react';
import { calendarEvents } from '../data/mockData';

const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const typeStyles: Record<string, string> = {
  meeting: 'bg-purple-500',
  deadline: 'bg-red-500',
  inspection: 'bg-blue-500',
  delivery: 'bg-amber-500',
  task: 'bg-emerald-500',
};

const typeBadge: Record<string, string> = {
  meeting: 'bg-purple-100 text-purple-700',
  deadline: 'bg-red-100 text-red-700',
  inspection: 'bg-blue-100 text-blue-700',
  delivery: 'bg-amber-100 text-amber-700',
  task: 'bg-emerald-100 text-emerald-700',
};

export default function CalendarPage() {
  const [current, setCurrent] = useState(new Date(2025, 7, 1)); // August 2025
  const [selectedDate, setSelectedDate] = useState<string | null>(null);

  const year = current.getFullYear();
  const month = current.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const prev = () => setCurrent(new Date(year, month - 1, 1));
  const next = () => setCurrent(new Date(year, month + 1, 1));

  const getDateStr = (day: number) => `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  const getEventsFor = (day: number) => calendarEvents.filter((e) => e.date === getDateStr(day));

  const selectedEvents = selectedDate ? calendarEvents.filter((e) => e.date === selectedDate) : calendarEvents;

  const upcomingEvents = calendarEvents
    .filter((e) => e.date >= new Date().toISOString().slice(0, 10))
    .sort((a, b) => a.date.localeCompare(b.date))
    .slice(0, 5);

  return (
    <div className="p-6 space-y-5">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {/* Calendar */}
        <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          {/* Header */}
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-lg font-bold text-slate-900" style={{ fontFamily: 'var(--font-display)' }}>
              {MONTHS[month]} {year}
            </h2>
            <div className="flex gap-1">
              <button onClick={prev} className="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"><ChevronLeft size={16} /></button>
              <button onClick={next} className="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"><ChevronRight size={16} /></button>
            </div>
          </div>

          {/* Days header */}
          <div className="grid grid-cols-7 mb-2">
            {DAYS.map((d) => (
              <div key={d} className="text-center text-xs font-semibold text-slate-400 py-1">{d}</div>
            ))}
          </div>

          {/* Grid */}
          <div className="grid grid-cols-7 gap-0.5">
            {Array.from({ length: firstDay }).map((_, i) => <div key={`e${i}`} className="h-20 rounded-xl" />)}
            {Array.from({ length: daysInMonth }).map((_, i) => {
              const day = i + 1;
              const dateStr = getDateStr(day);
              const events = getEventsFor(day);
              const today = dateStr === new Date().toISOString().slice(0, 10);
              const sel = dateStr === selectedDate;
              return (
                <div
                  key={day}
                  onClick={() => setSelectedDate(sel ? null : dateStr)}
                  className={`h-20 rounded-xl p-1.5 cursor-pointer transition-all border ${
                    sel ? 'border-blue-300 bg-blue-50 ring-2 ring-blue-100' : today ? 'border-blue-200 bg-blue-50' : 'border-transparent hover:bg-slate-50'
                  }`}
                >
                  <div className={`text-xs font-bold mb-1 w-5 h-5 flex items-center justify-center rounded-full ${today ? 'bg-blue-600 text-white' : 'text-slate-700'}`}>
                    {day}
                  </div>
                  <div className="space-y-0.5 overflow-hidden">
                    {events.slice(0, 2).map((e) => (
                      <div key={e.id} className={`text-[10px] font-medium text-white px-1 py-0.5 rounded ${e.color} truncate`}>
                        {e.title.split(' ').slice(0, 3).join(' ')}
                      </div>
                    ))}
                    {events.length > 2 && <div className="text-[10px] text-slate-400 pl-1">+{events.length - 2} more</div>}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-4">
          {/* Type legend */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h4 className="font-bold text-slate-900 text-sm mb-3" style={{ fontFamily: 'var(--font-display)' }}>Event Types</h4>
            <div className="space-y-2">
              {Object.entries(typeStyles).map(([type, cls]) => (
                <div key={type} className="flex items-center gap-2">
                  <div className={`w-3 h-3 rounded-full ${cls}`} />
                  <span className="text-xs text-slate-600 capitalize">{type}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Upcoming events */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h4 className="font-bold text-slate-900 text-sm mb-3" style={{ fontFamily: 'var(--font-display)' }}>
              {selectedDate ? `Events on ${selectedDate}` : 'Upcoming Events'}
            </h4>
            <div className="space-y-2">
              {(selectedDate ? selectedEvents : upcomingEvents).map((e) => (
                <div key={e.id} className="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                  <div className={`w-2 h-2 rounded-full flex-shrink-0 mt-1.5 ${typeStyles[e.type]}`} />
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-slate-800 truncate">{e.title}</p>
                    <div className="flex items-center gap-1.5 mt-0.5">
                      <span className="text-[11px] text-slate-400">{e.date}</span>
                      {e.time && <span className="text-[11px] text-slate-400">· {e.time}</span>}
                    </div>
                    <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded capitalize ${typeBadge[e.type]}`}>{e.type}</span>
                  </div>
                </div>
              ))}
              {selectedDate && selectedEvents.length === 0 && (
                <div className="text-center py-4 text-sm text-slate-400">
                  <Calendar size={20} className="mx-auto mb-1 text-slate-300" />
                  No events on this date
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
