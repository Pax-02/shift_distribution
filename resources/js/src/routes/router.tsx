import { createBrowserRouter } from 'react-router-dom';
import { RequireAuth } from '../auth/RequireAuth';
import { RequirePermission } from '../auth/RequirePermission';
import { AppShell } from '../components/layout/AppShell';
import { ClientFormPage } from '../pages/clients/ClientFormPage';
import { ClientsListPage } from '../pages/clients/ClientsListPage';
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
                    {
                        path: 'clients',
                        element: <RequirePermission permission="clients.view" />,
                        children: [
                            { index: true, element: <ClientsListPage /> },
                            {
                                element: <RequirePermission permission="clients.manage" />,
                                children: [
                                    { path: 'new', element: <ClientFormPage /> },
                                    { path: ':id/edit', element: <ClientFormPage /> },
                                ],
                            },
                        ],
                    },
                ],
            },
        ],
    },
]);
