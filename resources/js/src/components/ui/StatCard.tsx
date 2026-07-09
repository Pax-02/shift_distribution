interface StatCardProps {
    label: string;
    value: string | number;
    hint?: string;
}

export function StatCard({ label, value, hint }: StatCardProps) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{value}</p>
            {hint && <p className="mt-1 text-xs text-slate-400 dark:text-slate-500">{hint}</p>}
        </div>
    );
}
