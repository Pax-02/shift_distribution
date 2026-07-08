import clsx from 'clsx';

interface PillTabsProps<T extends string> {
    value: T;
    onChange: (value: T) => void;
    options: { value: T; label: string }[];
}

export function PillTabs<T extends string>({ value, onChange, options }: PillTabsProps<T>) {
    return (
        <div className="inline-flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800">
            {options.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    onClick={() => onChange(option.value)}
                    className={clsx(
                        'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                        value === option.value
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200',
                    )}
                >
                    {option.label}
                </button>
            ))}
        </div>
    );
}
