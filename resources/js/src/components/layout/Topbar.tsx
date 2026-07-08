import { useAuth } from '../../auth/AuthContext';
import { ThemeToggleButton } from '../../theme/ThemeToggleButton';

export function Topbar() {
    // Non-null: AppShell only renders inside RequireAuth.
    const { user } = useAuth();

    return (
        <header className="flex h-16 shrink-0 items-center justify-end gap-4 border-b border-slate-200 bg-white px-6 dark:border-white/10 dark:bg-slate-900">
            <ThemeToggleButton />
            <div className="flex items-center gap-2.5">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    {user!.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p className="text-sm font-medium text-slate-900 dark:text-slate-100">{user!.name}</p>
                    <p className="text-xs text-slate-400 capitalize dark:text-slate-500">{user!.role.name}</p>
                </div>
            </div>
        </header>
    );
}
