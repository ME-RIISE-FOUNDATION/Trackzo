import type { Project, Client, Material, PurchaseOrder, Transaction, CalendarEvent, Account, EstimationItem } from '../types';

export const projects: Project[] = [
  {
    id: 'p1',
    name: 'Skyline Tower Residences',
    clientId: 'c1',
    clientName: 'Metro Developers Ltd.',
    siteAddress: '14 Harbor Blvd, Downtown, NY 10001',
    type: 'Residential High-Rise',
    status: 'active',
    budget: 4800000,
    spent: 2940000,
    progress: 62,
    startDate: '2024-02-01',
    endDate: '2025-09-30',
    area: 18400,
    floors: 22,
    description: '22-floor luxury residential tower with 88 units, rooftop amenities, and underground parking.',
    manager: 'James Carter',
    tags: ['luxury', 'high-rise', 'LEED'],
  },
  {
    id: 'p2',
    name: 'Greenfield Commercial Park',
    clientId: 'c2',
    clientName: 'Apex Realty Group',
    siteAddress: '200 Industrial Ave, Queens, NY 11101',
    type: 'Commercial Complex',
    status: 'active',
    budget: 2600000,
    spent: 980000,
    progress: 38,
    startDate: '2024-06-15',
    endDate: '2025-12-31',
    area: 9200,
    floors: 4,
    description: 'Multi-unit commercial park with office spaces, retail units, and shared amenities.',
    manager: 'Sarah Mitchell',
    tags: ['commercial', 'office'],
  },
  {
    id: 'p3',
    name: 'Sunrise Villa Estate',
    clientId: 'c3',
    clientName: 'Harrison Family Trust',
    siteAddress: '7 Crestwood Lane, Brooklyn, NY 11215',
    type: 'Luxury Villa',
    status: 'completed',
    budget: 920000,
    spent: 905000,
    progress: 100,
    startDate: '2023-08-01',
    endDate: '2024-07-31',
    area: 4800,
    floors: 3,
    description: 'Bespoke luxury villa with pool, home theatre, smart home systems, and landscaped grounds.',
    manager: 'James Carter',
    tags: ['luxury', 'villa', 'smart-home'],
  },
  {
    id: 'p4',
    name: 'Riverfront Warehouse Conversion',
    clientId: 'c4',
    clientName: 'Urban Loft Holdings',
    siteAddress: '85 River St, Hoboken, NJ 07030',
    type: 'Renovation',
    status: 'on-hold',
    budget: 1400000,
    spent: 320000,
    progress: 23,
    startDate: '2024-04-01',
    endDate: '2025-06-30',
    area: 6200,
    floors: 5,
    description: 'Historic warehouse conversion to mixed-use loft apartments and ground-floor retail.',
    manager: 'Sarah Mitchell',
    tags: ['renovation', 'mixed-use'],
  },
  {
    id: 'p5',
    name: 'Northgate School Extension',
    clientId: 'c5',
    clientName: 'City Education Authority',
    siteAddress: '1 Academy Road, The Bronx, NY 10451',
    type: 'Institutional',
    status: 'planning',
    budget: 3200000,
    spent: 45000,
    progress: 5,
    startDate: '2025-01-15',
    endDate: '2026-06-30',
    area: 11000,
    floors: 3,
    description: 'New STEM block, sports hall, and cafeteria extension for Northgate Academy.',
    manager: 'David Okonkwo',
    tags: ['institutional', 'education', 'government'],
  },
];

export const clients: Client[] = [
  { id: 'c1', name: 'Robert Hargrove', company: 'Metro Developers Ltd.', email: 'r.hargrove@metrodev.com', phone: '+1 212-555-0181', address: '88 Wall Street', city: 'New York, NY', totalProjects: 3, totalValue: 8200000, status: 'active', joinedDate: '2022-03-14', avatar: 'RH' },
  { id: 'c2', name: 'Priya Sharma', company: 'Apex Realty Group', email: 'priya@apexrealty.com', phone: '+1 718-555-0242', address: '200 Industrial Ave', city: 'Queens, NY', totalProjects: 2, totalValue: 3800000, status: 'active', joinedDate: '2023-07-02', avatar: 'PS' },
  { id: 'c3', name: 'William Harrison III', company: 'Harrison Family Trust', email: 'wharrison@harrisonft.com', phone: '+1 347-555-0305', address: '7 Crestwood Lane', city: 'Brooklyn, NY', totalProjects: 1, totalValue: 920000, status: 'active', joinedDate: '2023-05-19', avatar: 'WH' },
  { id: 'c4', name: 'Maya Cohen', company: 'Urban Loft Holdings', email: 'maya@urbanloft.io', phone: '+1 201-555-0417', address: '85 River St', city: 'Hoboken, NJ', totalProjects: 1, totalValue: 1400000, status: 'active', joinedDate: '2024-01-08', avatar: 'MC' },
  { id: 'c5', name: 'Dr. Samuel Adeyemi', company: 'City Education Authority', email: 's.adeyemi@cea.gov', phone: '+1 718-555-0560', address: '1 Academy Road', city: 'The Bronx, NY', totalProjects: 1, totalValue: 3200000, status: 'active', joinedDate: '2024-09-15', avatar: 'SA' },
  { id: 'c6', name: 'Linda Kwong', company: 'Pacific Hospitality Group', email: 'lkwong@pacifichg.com', phone: '+1 212-555-0648', address: '300 Broadway', city: 'Manhattan, NY', totalProjects: 0, totalValue: 0, status: 'inactive', joinedDate: '2023-11-20', avatar: 'LK' },
];

export const materials: Material[] = [
  { id: 'm1', name: 'Portland Cement (OPC 53)', category: 'Cement', unit: 'Bags (50kg)', stock: 850, minStock: 300, rate: 12.50, supplier: 'Atlas Cement Co.', lastUpdated: '2025-07-28' },
  { id: 'm2', name: 'Steel Rebar (12mm)', category: 'Steel', unit: 'MT', stock: 42, minStock: 15, rate: 820, supplier: 'Ironclad Steel Works', lastUpdated: '2025-07-30' },
  { id: 'm3', name: 'Coarse Aggregate (20mm)', category: 'Aggregate', unit: 'Cubic Yard', stock: 180, minStock: 80, rate: 65, supplier: 'Valley Quarry LLC', lastUpdated: '2025-07-25' },
  { id: 'm4', name: 'Fine Sand (Washed)', category: 'Aggregate', unit: 'Cubic Yard', stock: 95, minStock: 100, rate: 55, supplier: 'Valley Quarry LLC', lastUpdated: '2025-07-25' },
  { id: 'm5', name: 'Red Clay Bricks', category: 'Masonry', unit: 'Thousand', stock: 28, minStock: 10, rate: 480, supplier: 'Heritage Brick Co.', lastUpdated: '2025-07-22' },
  { id: 'm6', name: 'Structural Timber (4x4)', category: 'Timber', unit: 'Board Ft', stock: 3200, minStock: 1000, rate: 3.20, supplier: 'Pacific Lumber Inc.', lastUpdated: '2025-07-29' },
  { id: 'm7', name: 'PVC Pipes (4")', category: 'Plumbing', unit: 'Length (20ft)', stock: 12, minStock: 40, rate: 28, supplier: 'FlowTech Supplies', lastUpdated: '2025-07-18' },
  { id: 'm8', name: 'Electrical Conduit (1")', category: 'Electrical', unit: 'Roll (100m)', stock: 8, minStock: 20, rate: 145, supplier: 'Voltex Electrical', lastUpdated: '2025-07-20' },
  { id: 'm9', name: 'Plywood (3/4" Marine)', category: 'Timber', unit: 'Sheet', stock: 220, minStock: 80, rate: 62, supplier: 'Pacific Lumber Inc.', lastUpdated: '2025-07-27' },
  { id: 'm10', name: 'Portland Cement (White)', category: 'Cement', unit: 'Bags (25kg)', stock: 5, minStock: 50, rate: 22, supplier: 'Atlas Cement Co.', lastUpdated: '2025-07-15' },
];

export const purchaseOrders: PurchaseOrder[] = [
  { id: 'po1', supplier: 'Atlas Cement Co.', items: [{ name: 'Portland Cement OPC 53', qty: 500, rate: 12.50 }], total: 6250, status: 'delivered', date: '2025-07-20', expectedDate: '2025-07-25', projectId: 'p1' },
  { id: 'po2', supplier: 'Ironclad Steel Works', items: [{ name: 'Steel Rebar 12mm', qty: 20, rate: 820 }], total: 16400, status: 'approved', date: '2025-07-28', expectedDate: '2025-08-05', projectId: 'p1' },
  { id: 'po3', supplier: 'Valley Quarry LLC', items: [{ name: 'Coarse Aggregate 20mm', qty: 80, rate: 65 }, { name: 'Fine Sand Washed', qty: 60, rate: 55 }], total: 8500, status: 'pending', date: '2025-07-30', expectedDate: '2025-08-08', projectId: 'p2' },
  { id: 'po4', supplier: 'FlowTech Supplies', items: [{ name: 'PVC Pipes 4in', qty: 60, rate: 28 }], total: 1680, status: 'pending', date: '2025-07-31', expectedDate: '2025-08-10', projectId: 'p2' },
  { id: 'po5', supplier: 'Heritage Brick Co.', items: [{ name: 'Red Clay Bricks', qty: 20, rate: 480 }], total: 9600, status: 'cancelled', date: '2025-07-10', expectedDate: '2025-07-18', projectId: 'p4' },
];

export const transactions: Transaction[] = [
  { id: 't1', date: '2025-07-31', description: 'Client Payment - Skyline Tower Phase 3', category: 'Client Receipt', type: 'income', amount: 480000, projectId: 'p1', status: 'paid' },
  { id: 't2', date: '2025-07-29', description: 'Steel Procurement - Ironclad Steel', category: 'Materials', type: 'expense', amount: 42600, projectId: 'p1', status: 'paid' },
  { id: 't3', date: '2025-07-28', description: 'Labour Wages - July W4', category: 'Labour', type: 'expense', amount: 28400, projectId: 'p1', status: 'paid' },
  { id: 't4', date: '2025-07-25', description: 'Client Payment - Greenfield Park Milestone 1', category: 'Client Receipt', type: 'income', amount: 240000, projectId: 'p2', status: 'paid' },
  { id: 't5', date: '2025-07-22', description: 'Subcontractor - MEP Works', category: 'Subcontractor', type: 'expense', amount: 85000, projectId: 'p2', status: 'paid' },
  { id: 't6', date: '2025-07-20', description: 'Equipment Rental - Tower Crane', category: 'Equipment', type: 'expense', amount: 18500, projectId: 'p1', status: 'paid' },
  { id: 't7', date: '2025-07-18', description: 'Pending Invoice - Greenfield Phase 2', category: 'Client Receipt', type: 'income', amount: 180000, projectId: 'p2', status: 'pending' },
  { id: 't8', date: '2025-07-15', description: 'Concrete Mix Supply - Atlas Cement', category: 'Materials', type: 'expense', amount: 12800, projectId: 'p1', status: 'paid' },
  { id: 't9', date: '2025-07-12', description: 'Insurance Premium - Project Coverage', category: 'Insurance', type: 'expense', amount: 6200, projectId: 'p1', status: 'paid' },
  { id: 't10', date: '2025-07-08', description: 'Client Payment - Sunrise Villa Final', category: 'Client Receipt', type: 'income', amount: 92000, projectId: 'p3', status: 'paid' },
  { id: 't11', date: '2025-07-05', description: 'Overdue Payment - Urban Loft Deposit', category: 'Client Receipt', type: 'income', amount: 140000, projectId: 'p4', status: 'overdue' },
  { id: 't12', date: '2025-07-03', description: 'Labour Wages - June Final', category: 'Labour', type: 'expense', amount: 31200, projectId: 'p1', status: 'paid' },
];

export const calendarEvents: CalendarEvent[] = [
  { id: 'e1', title: 'Foundation Inspection - Skyline', date: '2025-08-04', type: 'inspection', projectId: 'p1', time: '09:00', color: 'bg-blue-500' },
  { id: 'e2', title: 'Client Meeting - Apex Realty', date: '2025-08-06', type: 'meeting', projectId: 'p2', time: '14:00', color: 'bg-purple-500' },
  { id: 'e3', title: 'Steel Delivery - Ironclad', date: '2025-08-05', type: 'delivery', projectId: 'p1', time: '08:00', color: 'bg-amber-500' },
  { id: 'e4', title: 'Phase 3 Deadline - Skyline', date: '2025-08-15', type: 'deadline', projectId: 'p1', time: null as unknown as string, color: 'bg-red-500' },
  { id: 'e5', title: 'Safety Audit - Greenfield', date: '2025-08-12', type: 'inspection', projectId: 'p2', time: '10:00', color: 'bg-blue-500' },
  { id: 'e6', title: 'Concrete Pour - Level 14', date: '2025-08-08', type: 'task', projectId: 'p1', time: '07:00', color: 'bg-emerald-500' },
  { id: 'e7', title: 'Subcontractor Review', date: '2025-08-20', type: 'meeting', time: '11:00', color: 'bg-purple-500' },
  { id: 'e8', title: 'Material Delivery - Valley Quarry', date: '2025-08-08', type: 'delivery', projectId: 'p2', time: '13:00', color: 'bg-amber-500' },
  { id: 'e9', title: 'Northgate Site Survey', date: '2025-08-22', type: 'inspection', projectId: 'p5', time: '09:30', color: 'bg-blue-500' },
  { id: 'e10', title: 'Progress Report - Metro Dev', date: '2025-08-25', type: 'meeting', projectId: 'p1', time: '15:00', color: 'bg-purple-500' },
];

export const accounts: Account[] = [
  { id: 'a1', name: 'Chase Business Checking', type: 'bank', balance: 842600, currency: 'USD', lastTransaction: '2025-07-31' },
  { id: 'a2', name: 'Citibank Operations Account', type: 'bank', balance: 225000, currency: 'USD', lastTransaction: '2025-07-29' },
  { id: 'a3', name: 'Petty Cash - Site Office', type: 'cash', balance: 4200, currency: 'USD', lastTransaction: '2025-07-30' },
  { id: 'a4', name: 'AmEx Business Platinum', type: 'credit', balance: -32800, currency: 'USD', lastTransaction: '2025-07-28' },
];

export const defaultEstimationItems: EstimationItem[] = [
  { id: 'ei1', description: 'Excavation & Earth Work', unit: 'Cubic Yard', qty: 1200, rate: 18, tax: 8, discount: 0 },
  { id: 'ei2', description: 'Concrete Foundation (M30)', unit: 'Cubic Meter', qty: 480, rate: 145, tax: 8, discount: 2 },
  { id: 'ei3', description: 'Steel Reinforcement', unit: 'Metric Ton', qty: 42, rate: 1200, tax: 8, discount: 0 },
  { id: 'ei4', description: 'Brick Masonry Work', unit: 'Sq.Ft.', qty: 18000, rate: 12, tax: 8, discount: 5 },
  { id: 'ei5', description: 'Plastering (Internal)', unit: 'Sq.Ft.', qty: 32000, rate: 4.50, tax: 8, discount: 0 },
];

export const budgetVsExpenseData = [
  { month: 'Feb', budget: 380000, expense: 210000 },
  { month: 'Mar', budget: 420000, expense: 385000 },
  { month: 'Apr', budget: 510000, expense: 465000 },
  { month: 'May', budget: 480000, expense: 510000 },
  { month: 'Jun', budget: 550000, expense: 498000 },
  { month: 'Jul', budget: 620000, expense: 572000 },
];

export const cashFlowData = [
  { month: 'Feb', income: 520000, expense: 310000 },
  { month: 'Mar', income: 680000, expense: 590000 },
  { month: 'Apr', income: 420000, expense: 465000 },
  { month: 'May', income: 890000, expense: 640000 },
  { month: 'Jun', income: 740000, expense: 618000 },
  { month: 'Jul', income: 992000, expense: 722000 },
];

export const projectStatusData = [
  { name: 'Active', value: 2, color: '#1D4ED8' },
  { name: 'Completed', value: 1, color: '#10B981' },
  { name: 'Planning', value: 1, color: '#F59E0B' },
  { name: 'On Hold', value: 1, color: '#EF4444' },
];

export const materialCostData = [
  { name: 'Cement', value: 28 },
  { name: 'Steel', value: 35 },
  { name: 'Aggregate', value: 15 },
  { name: 'Timber', value: 12 },
  { name: 'Other', value: 10 },
];
