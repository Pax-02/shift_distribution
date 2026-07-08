import { Navigate, Outlet } from 'react-router-dom';
import type { Permission } from '../types';
import { useAuth } from './AuthContext';

export function RequirePermission({ permission }: { permission: Permission }) {
    const { hasPermission } = useAuth();
    if (!hasPermission(permission)) {
        return <Navigate to="/" replace />;
    }
    return <Outlet />;
}
