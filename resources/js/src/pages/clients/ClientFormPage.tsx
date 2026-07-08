import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ApiError } from '../../api/client';
import { Button } from '../../components/ui/Button';
import { FormField, fieldInputClasses } from '../../components/ui/FormField';
import { Modal } from '../../components/ui/Modal';
import { useClientQuery, useCreateClientMutation, useUpdateClientMutation } from '../../hooks/useClients';
import type { Client, ClientPayload } from '../../types/client';

interface ClientFormState {
    name: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    is_active: boolean;
    notes: string;
}

const EMPTY_FORM: ClientFormState = {
    name: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    is_active: true,
    notes: '',
};

function clientToFormState(client: Client): ClientFormState {
    return {
        name: client.name,
        contact_name: client.contact_name ?? '',
        contact_email: client.contact_email ?? '',
        contact_phone: client.contact_phone ?? '',
        is_active: client.is_active,
        notes: client.notes ?? '',
    };
}

function formStateToPayload(form: ClientFormState): ClientPayload {
    return {
        name: form.name,
        contact_name: form.contact_name || null,
        contact_email: form.contact_email || null,
        contact_phone: form.contact_phone || null,
        is_active: form.is_active,
        notes: form.notes || null,
    };
}

export function ClientFormPage() {
    const { id } = useParams<{ id: string }>();
    const isEdit = Boolean(id);
    const navigate = useNavigate();
    const { data, isLoading } = useClientQuery(id);
    const createMutation = useCreateClientMutation();
    const updateMutation = useUpdateClientMutation(id ?? '');
    const [form, setForm] = useState<ClientFormState>(EMPTY_FORM);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [hydrated, setHydrated] = useState(!isEdit);

    useEffect(() => {
        if (data?.data && !hydrated) {
            setForm(clientToFormState(data.data));
            setHydrated(true);
        }
    }, [data, hydrated]);

    function set<K extends keyof ClientFormState>(key: K, value: ClientFormState[K]) {
        setForm((current) => ({ ...current, [key]: value }));
    }

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setErrors({});
        try {
            const payload = formStateToPayload(form);
            if (isEdit) {
                await updateMutation.mutateAsync(payload);
            } else {
                await createMutation.mutateAsync(payload);
            }
            navigate('/clients');
        } catch (error) {
            if (error instanceof ApiError && error.errors) {
                setErrors(error.errors);
            }
        }
    }

    const close = () => navigate('/clients');
    const isSubmitting = createMutation.isPending || updateMutation.isPending;

    if (isEdit && isLoading && !hydrated) {
        return (
            <Modal title="Loading…" onClose={close}>
                <p className="text-sm text-slate-500 dark:text-slate-400">Loading client…</p>
            </Modal>
        );
    }

    return (
        <Modal
            title={isEdit ? 'Edit client' : 'Add client'}
            subtitle={isEdit ? "Update this client's details." : 'Add a new client organization.'}
            onClose={close}
        >
            <form onSubmit={(event) => void handleSubmit(event)} className="space-y-4" noValidate>
                <FormField label="Name" htmlFor="name" required error={errors.name}>
                    <input
                        id="name"
                        className={fieldInputClasses}
                        value={form.name}
                        onChange={(event) => set('name', event.target.value)}
                        required
                    />
                </FormField>

                <FormField label="Contact name" htmlFor="contact_name" error={errors.contact_name}>
                    <input
                        id="contact_name"
                        className={fieldInputClasses}
                        value={form.contact_name}
                        onChange={(event) => set('contact_name', event.target.value)}
                    />
                </FormField>

                <div className="grid grid-cols-2 gap-4">
                    <FormField label="Contact email" htmlFor="contact_email" error={errors.contact_email}>
                        <input
                            id="contact_email"
                            type="email"
                            className={fieldInputClasses}
                            value={form.contact_email}
                            onChange={(event) => set('contact_email', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Contact phone" htmlFor="contact_phone" error={errors.contact_phone}>
                        <input
                            id="contact_phone"
                            className={fieldInputClasses}
                            value={form.contact_phone}
                            onChange={(event) => set('contact_phone', event.target.value)}
                        />
                    </FormField>
                </div>

                <label className="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input
                        type="checkbox"
                        checked={form.is_active}
                        onChange={(event) => set('is_active', event.target.checked)}
                        className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                    />
                    Active
                </label>

                <FormField label="Notes" htmlFor="notes" error={errors.notes}>
                    <textarea
                        id="notes"
                        rows={3}
                        className={fieldInputClasses}
                        value={form.notes}
                        onChange={(event) => set('notes', event.target.value)}
                    />
                </FormField>

                <div className="flex justify-end gap-3 pt-2">
                    <Button variant="secondary" type="button" onClick={close}>
                        Cancel
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>
                        {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Add client'}
                    </Button>
                </div>
            </form>
        </Modal>
    );
}
