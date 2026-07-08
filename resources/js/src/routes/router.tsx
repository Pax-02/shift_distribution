import { createBrowserRouter } from 'react-router-dom';
import { RequireAuth } from '../auth/RequireAuth';
import { RequirePermission } from '../auth/RequirePermission';
import { AppShell } from '../components/layout/AppShell';
import { DashboardPage } from '../pages/DashboardPage';
import { LoginPage } from '../pages/LoginPage';
import { WorkerFormPage } from '../pages/workers/WorkerFormPage';
import { WorkersListPage } from '../pages/workers/WorkersListPage';

export const router = createBrowserRouter([
    { path: '/login', element: <LoginPage /> },
    {
        path: '/',
        element: <RequireAuth />,
        children: [
            {
                element: <AppShell />,
                children: [
                    { index: true, element: <DashboardPage /> },
                    {
                        path: 'workers',
                        element: <RequirePermission permission="workers.view" />,
                        children: [
                            { index: true, element: <WorkersListPage /> },
                            {
                                element: <RequirePermission permission="workers.manage" />,
                                children: [
                                    { path: 'new', element: <WorkerFormPage /> },
                                    { path: ':id/edit', element: <WorkerFormPage /> },
                                ],
                            },
                        ],
                    },
                ],
            },
        ],
    },
]);
