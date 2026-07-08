import { X } from 'lucide-react';
import type { ReactNode } from 'react';

interface ModalProps {
    title: string;
    subtitle?: string;
    onClose: () => void;
    children: ReactNode;
}

// Presentational only — used as the entire body of a routed page (e.g.
// /workers/new), not layered over another page. Gives the reference
// design's modal look without modal-over-list state coordination; close
// and successful-submit both just navigate() back to the list route.
export function Modal({ title, subtitle, onClose, children }: ModalProps) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
            <div className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
                <div className="flex items-start justify-between border-b border-slate-100 px-6 py-4 dark:border-white/10">
                    <div>
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">{title}</h2>
                        {subtitle && <p className="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{subtitle}</p>}
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        aria-label="Close"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>
                <div className="px-6 py-5">{children}</div>
            </div>
        </div>
    );
}
