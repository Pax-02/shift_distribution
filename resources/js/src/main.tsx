import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';

// Placeholder mount to prove the Vite + React + TS build pipeline works end
// to end before any real app code (routing, auth, API client) is added.
createRoot(document.getElementById('app')!).render(
    <StrictMode>
        <div>Hello</div>
    </StrictMode>,
);
