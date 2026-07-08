import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import { AuthProvider } from './auth/AuthContext';
import { router } from './routes/router';

// No custom defaultOptions yet — no real queries exist until the first
// data-fetching feature page lands; staleTime/retry tuning goes here then.
const queryClient = new QueryClient();

createRoot(document.getElementById('app')!).render(
    <StrictMode>
        <QueryClientProvider client={queryClient}>
            <AuthProvider>
                <RouterProvider router={router} />
            </AuthProvider>
        </QueryClientProvider>
    </StrictMode>,
);
