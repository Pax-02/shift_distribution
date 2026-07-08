import { useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';

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
        <div>
            <p>Welcome, {user!.name}</p>
            <button onClick={() => void handleLogout()}>Log out</button>
        </div>
    );
}
