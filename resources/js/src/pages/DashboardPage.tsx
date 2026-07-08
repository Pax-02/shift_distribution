import { useAuth } from '../auth/AuthContext';

export function DashboardPage() {
    // Non-null: this page only renders inside RequireAuth, which guarantees
    // an authenticated user.
    const { user } = useAuth();

    return (
        <div>
            <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Welcome, {user!.name.split(' ')[0]}</h1>
            <p className="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                This is a placeholder dashboard — stat cards and a report chart land here once workers, clients, shifts,
                and reports exist to back them.
            </p>
        </div>
    );
}
