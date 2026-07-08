import { useState, type FormEvent } from 'react';
import { ApiError } from '../api/client';
import { useAuth } from '../auth/AuthContext';

export function LoginPage() {
    const { login } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setErrors({});
        setIsSubmitting(true);
        try {
            await login(email, password);
        } catch (error) {
            setErrors(error instanceof ApiError && error.errors ? error.errors : { email: ['Login failed. Please try again.'] });
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <form onSubmit={(event) => void handleSubmit(event)}>
            <h1>Log in</h1>
            <label>
                Email
                <input type="email" value={email} onChange={(event) => setEmail(event.target.value)} required />
            </label>
            {errors.email && <p role="alert">{errors.email.join(' ')}</p>}
            <label>
                Password
                <input
                    type="password"
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    required
                />
            </label>
            {errors.password && <p role="alert">{errors.password.join(' ')}</p>}
            <button type="submit" disabled={isSubmitting}>
                {isSubmitting ? 'Logging in…' : 'Log in'}
            </button>
        </form>
    );
}
