import { createContext, useCallback, useContext, useMemo, useState, type ReactNode } from 'react';
import { login as loginRequest, logout as logoutRequest } from '../api/auth';
import { clearToken, getToken, setToken } from '../api/tokenStorage';
import type { Permission, User } from '../types';

// Kept alongside tokenStorage's TOKEN_KEY rather than merged into it: the
// token is needed by the API client (which must not import a React-y
// module), while the user object is only ever needed by this context.
const USER_KEY = 'shift_distribution.user';

function readStoredUser(): User | null {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? (JSON.parse(raw) as User) : null;
}

interface AuthContextValue {
    user: User | null;
    isAuthenticated: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    hasPermission: (permission: Permission) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    // Hydrated from localStorage at boot and trusted as-is — not re-verified
    // against GET /api/user here. TODO(next auth step): verify the token on
    // app boot instead of trusting the persisted blob blindly; acceptable
    // for now since this scaffold has no data-fetching page yet, and
    // apiFetch's 401 handler will catch a stale token as soon as one exists.
    const [user, setUser] = useState<User | null>(() => (getToken() ? readStoredUser() : null));

    const login = useCallback(async (email: string, password: string) => {
        const response = await loginRequest(email, password);
        setToken(response.token);
        localStorage.setItem(USER_KEY, JSON.stringify(response.user));
        setUser(response.user);
    }, []);

    const logout = useCallback(async () => {
        try {
            await logoutRequest();
        } finally {
            clearToken();
            localStorage.removeItem(USER_KEY);
            setUser(null);
        }
    }, []);

    const hasPermission = useCallback(
        (permission: Permission) => user?.role.permissions.includes(permission) ?? false,
        [user],
    );

    const value = useMemo(
        () => ({ user, isAuthenticated: user !== null, login, logout, hasPermission }),
        [user, login, logout, hasPermission],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
