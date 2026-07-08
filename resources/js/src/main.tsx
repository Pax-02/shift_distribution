import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { AuthProvider, useAuth } from './auth/AuthContext';
import { LoginPage } from './pages/LoginPage';

// Temporary harness to manually verify auth end-to-end before real routing
// (react-router-dom) is wired up in the next step — replaced by a
// RouterProvider tree with a protected dashboard route then.
function AuthGate() {
    const { user, logout } = useAuth();
    if (!user) {
        return <LoginPage />;
    }
    return (
        <div>
            <p>Logged in as {user.name}</p>
            <button onClick={() => void logout()}>Log out</button>
        </div>
    );
}

createRoot(document.getElementById('app')!).render(
    <StrictMode>
        <AuthProvider>
            <AuthGate />
        </AuthProvider>
    </StrictMode>,
);
