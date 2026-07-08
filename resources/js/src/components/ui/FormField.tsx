import type { ReactNode } from 'react';

// Shared by every raw <input>/<select>/<textarea> in entity forms, whether
// wrapped in FormField or used bare (e.g. compact placeholder-only address
// sub-fields that don't need their own label).
export const fieldInputClasses =
    'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 dark:placeholder:text-slate-500';

interface FormFieldProps {
    label: string;
    htmlFor: string;
    error?: string[];
    required?: boolean;
    children: ReactNode;
}

export function FormField({ label, htmlFor, error, required, children }: FormFieldProps) {
    return (
        <div>
            <label htmlFor={htmlFor} className="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                {label}
                {required && <span className="text-red-500"> *</span>}
            </label>
            {children}
            {error && <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{error.join(' ')}</p>}
        </div>
    );
}
