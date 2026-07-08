import { useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { LogOutIcon } from '../components/icons';

export function DashboardPage() {
    // Non-null: this page only renders inside RequireAuth, which guarantees
    // an authenticated user.
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    async function handleLogout() {
        await logout();
        navigate('/login', { replace: true });
    }

    return (
        <div className="min-h-screen bg-slate-950">
            <header className="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div className="flex items-center gap-3">
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white">
                        S
                    </div>
                    <p className="text-sm font-semibold text-white">Shift Distribution</p>
                </div>
                <div className="flex items-center gap-4">
                    <div className="text-right">
                        <p className="text-sm font-medium text-slate-100">{user!.name}</p>
                        <p className="text-xs text-slate-400 capitalize">{user!.role.name}</p>
                    </div>
                    <button
                        onClick={() => void handleLogout()}
                        className="flex items-center gap-1.5 rounded-lg border border-slate-700 px-3 py-1.5 text-sm text-slate-300 transition-colors hover:border-slate-600 hover:text-white"
                    >
                        <LogOutIcon className="h-4 w-4" />
                        Log out
                    </button>
                </div>
            </header>

            <main className="flex flex-col items-center justify-center px-6 py-24 text-center">
                <h1 className="text-2xl font-semibold text-white">Welcome, {user!.name.split(' ')[0]}</h1>
                <p className="mt-2 max-w-sm text-sm text-slate-400">
                    This is a placeholder dashboard — worker, client, shift, and allocation views land here next.
                </p>
            </main>
        </div>
    );
}
