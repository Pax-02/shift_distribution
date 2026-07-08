import clsx from 'clsx';
import { BarChart3, Building2, CalendarClock, LayoutDashboard, LogOut, Shuffle, Users, type LucideIcon } from 'lucide-react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';
import type { Permission } from '../../types';

interface NavItem {
    to: string;
    label: string;
    icon: LucideIcon;
    permission?: Permission;
}

const MAIN_ITEMS: NavItem[] = [{ to: '/', label: 'Dashboard', icon: LayoutDashboard }];

const FEATURE_ITEMS: NavItem[] = [
    { to: '/workers', label: 'Workers', icon: Users, permission: 'workers.view' },
    { to: '/clients', label: 'Clients', icon: Building2, permission: 'clients.view' },
    { to: '/shifts', label: 'Shifts', icon: CalendarClock, permission: 'shifts.view' },
    { to: '/allocation', label: 'Allocation', icon: Shuffle, permission: 'allocation.run' },
    { to: '/reports', label: 'Reports', icon: BarChart3, permission: 'reports.view' },
];

function NavGroup({ title, items }: { title: string; items: NavItem[] }) {
    const { hasPermission } = useAuth();
    const visible = items.filter((item) => !item.permission || hasPermission(item.permission));
    if (visible.length === 0) {
        return null;
    }

    return (
        <div>
            <p className="px-3 text-xs font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">{title}</p>
            <nav className="mt-2 space-y-0.5">
                {visible.map(({ to, label, icon: Icon }) => (
                    <NavLink
                        key={to}
                        to={to}
                        end={to === '/'}
                        className={({ isActive }) =>
                            clsx(
                                'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                isActive
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
                            )
                        }
                    >
                        <Icon className="h-4.5 w-4.5" />
                        {label}
                    </NavLink>
                ))}
            </nav>
        </div>
    );
}

export function Sidebar() {
    const { logout } = useAuth();
    const navigate = useNavigate();

    async function handleLogout() {
        await logout();
        navigate('/login', { replace: true });
    }

    return (
        <aside className="flex w-64 shrink-0 flex-col border-r border-slate-200 bg-white px-4 py-6 dark:border-white/10 dark:bg-slate-900">
            <div className="mb-8 flex items-center gap-2.5 px-2">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white">
                    S
                </div>
                <p className="text-sm font-semibold text-slate-900 dark:text-white">Shift Distribution</p>
            </div>

            <div className="flex-1 space-y-6">
                <NavGroup title="Main menu" items={MAIN_ITEMS} />
                <NavGroup title="Features" items={FEATURE_ITEMS} />
            </div>

            <button
                type="button"
                onClick={() => void handleLogout()}
                className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            >
                <LogOut className="h-4.5 w-4.5" />
                Log out
            </button>
        </aside>
    );
}
