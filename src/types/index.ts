export type NavPage =
  | 'dashboard'
  | 'projects'
  | 'clients'
  | 'estimation'
  | 'materials'
  | 'purchase'
  | 'finance'
  | 'reports'
  | 'calendar'
  | 'account-tracker'
  | 'settings';

export interface Project {
  id: string;
  name: string;
  clientId: string;
  clientName: string;
  siteAddress: string;
  type: string;
  status: 'planning' | 'active' | 'on-hold' | 'completed';
  budget: number;
  spent: number;
  progress: number;
  startDate: string;
  endDate: string;
  area: number;
  floors: number;
  description: string;
  manager: string;
  tags: string[];
}

export interface Client {
  id: string;
  name: string;
  company: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  totalProjects: number;
  totalValue: number;
  status: 'active' | 'inactive';
  joinedDate: string;
  avatar: string;
}

export interface EstimationItem {
  id: string;
  description: string;
  unit: string;
  qty: number;
  rate: number;
  tax: number;
  discount: number;
}

export interface Material {
  id: string;
  name: string;
  category: string;
  unit: string;
  stock: number;
  minStock: number;
  rate: number;
  supplier: string;
  lastUpdated: string;
}

export interface PurchaseOrder {
  id: string;
  supplier: string;
  items: { name: string; qty: number; rate: number }[];
  total: number;
  status: 'pending' | 'approved' | 'delivered' | 'cancelled';
  date: string;
  expectedDate: string;
  projectId: string;
}

export interface Transaction {
  id: string;
  date: string;
  description: string;
  category: string;
  type: 'income' | 'expense';
  amount: number;
  projectId: string;
  status: 'paid' | 'pending' | 'overdue';
}

export interface CalendarEvent {
  id: string;
  title: string;
  date: string;
  type: 'meeting' | 'deadline' | 'inspection' | 'delivery' | 'task';
  projectId?: string;
  time?: string;
  color: string;
}

export interface Account {
  id: string;
  name: string;
  type: 'bank' | 'cash' | 'credit';
  balance: number;
  currency: string;
  lastTransaction: string;
}

export interface Toast {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
}
