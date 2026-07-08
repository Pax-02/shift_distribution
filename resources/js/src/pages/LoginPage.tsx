import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { ApiError } from '../api/client';
import { useAuth } from '../auth/AuthContext';
import { EyeIcon, EyeOffIcon, LockIcon, MailIcon } from '../components/icons';
import { ThemeToggleButton } from '../theme/ThemeToggleButton';

export function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setErrors({});
        setIsSubmitting(true);
        try {
            await login(email, password);
            navigate('/', { replace: true });
        } catch (error) {
            setErrors(
                error instanceof ApiError && error.errors ? error.errors : { email: ['Login failed. Please try again.'] },
            );
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 dark:bg-slate-950">
            {/* Two soft radial glows behind the card — cheap way to avoid a flat single-color background without needing an image asset. */}
            <div className="pointer-events-none absolute -top-40 -left-40 h-96 w-96 rounded-full bg-blue-400/10 blur-3xl dark:bg-blue-600/20" />
            <div className="pointer-events-none absolute -right-40 -bottom-40 h-96 w-96 rounded-full bg-indigo-400/10 blur-3xl dark:bg-indigo-600/20" />

            <div className="absolute top-4 right-4">
                <ThemeToggleButton />
            </div>

            <div className="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 shadow-xl dark:border-white/10 dark:bg-slate-900/70 dark:shadow-2xl dark:backdrop-blur">
                <div className="mb-8 flex items-center gap-3">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-lg font-semibold text-white shadow-lg shadow-blue-950/20 dark:shadow-blue-950/50">
                        S
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-slate-900 dark:text-white">Shift Distribution</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Sign in to manage shifts</p>
                    </div>
                </div>

                <form onSubmit={(event) => void handleSubmit(event)} className="space-y-4" noValidate>
                    <div>
                        <label htmlFor="email" className="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Email
                        </label>
                        <div className="relative">
                            <MailIcon className="pointer-events-none absolute top-1/2 left-3 h-4.5 w-4.5 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                            <input
                                id="email"
                                type="email"
                                autoComplete="email"
                                value={email}
                                onChange={(event) => setEmail(event.target.value)}
                                required
                                placeholder="you@example.com"
                                className="w-full rounded-lg border border-slate-300 bg-white py-2.5 pr-3 pl-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 dark:placeholder:text-slate-500"
                            />
                        </div>
                        {errors.email && <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.email.join(' ')}</p>}
                    </div>

                    <div>
                        <label
                            htmlFor="password"
                            className="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Password
                        </label>
                        <div className="relative">
                            <LockIcon className="pointer-events-none absolute top-1/2 left-3 h-4.5 w-4.5 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                autoComplete="current-password"
                                value={password}
                                onChange={(event) => setPassword(event.target.value)}
                                required
                                placeholder="••••••••"
                                className="w-full rounded-lg border border-slate-300 bg-white py-2.5 pr-10 pl-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 dark:placeholder:text-slate-500"
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword((value) => !value)}
                                className="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                                aria-label={showPassword ? 'Hide password' : 'Show password'}
                            >
                                {showPassword ? <EyeOffIcon className="h-4.5 w-4.5" /> : <EyeIcon className="h-4.5 w-4.5" />}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.password.join(' ')}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-500 active:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {isSubmitting ? 'Signing in…' : 'Sign in'}
                    </button>
                </form>
            </div>
        </div>
    );
}
