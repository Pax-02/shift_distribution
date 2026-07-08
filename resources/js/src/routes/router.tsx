import { createBrowserRouter } from 'react-router-dom';
import { RequireAuth } from '../auth/RequireAuth';
import { DashboardPage } from '../pages/DashboardPage';
import { LoginPage } from '../pages/LoginPage';

export const router = createBrowserRouter([
    { path: '/login', element: <LoginPage /> },
    {
        path: '/',
        element: <RequireAuth />,
        children: [{ index: true, element: <DashboardPage /> }],
    },
]);
