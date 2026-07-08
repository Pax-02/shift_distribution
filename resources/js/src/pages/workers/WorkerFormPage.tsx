import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ApiError } from '../../api/client';
import { Button } from '../../components/ui/Button';
import { FormField, fieldInputClasses } from '../../components/ui/FormField';
import { Modal } from '../../components/ui/Modal';
import { PillTabs } from '../../components/ui/PillTabs';
import { useCreateWorkerMutation, useUpdateWorkerMutation, useWorkerQuery } from '../../hooks/useWorkers';
import type { Gender, JourneyPreference } from '../../types/enums';
import type { Worker, WorkerPayload } from '../../types/worker';

interface WorkerFormState {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    gender: Gender;
    date_of_birth: string;
    address_line1: string;
    address_line2: string;
    city: string;
    postcode: string;
    country: string;
    contracted_weekly_hours: string;
    max_weekly_hours: string;
    standby: boolean;
    journey_preference: JourneyPreference;
    is_active: boolean;
    notes: string;
}

const EMPTY_FORM: WorkerFormState = {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    gender: 'prefer_not_to_say',
    date_of_birth: '',
    address_line1: '',
    address_line2: '',
    city: '',
    postcode: '',
    country: '',
    contracted_weekly_hours: '',
    max_weekly_hours: '',
    standby: false,
    journey_preference: 'any',
    is_active: true,
    notes: '',
};

function workerToFormState(worker: Worker): WorkerFormState {
    return {
        first_name: worker.first_name,
        last_name: worker.last_name,
        email: worker.email ?? '',
        phone: worker.phone ?? '',
        gender: worker.gender ?? 'prefer_not_to_say',
        date_of_birth: worker.date_of_birth ?? '',
        address_line1: worker.address.line1 ?? '',
        address_line2: worker.address.line2 ?? '',
        city: worker.address.city ?? '',
        postcode: worker.address.postcode ?? '',
        country: worker.address.country ?? '',
        contracted_weekly_hours: worker.contracted_weekly_hours,
        max_weekly_hours: worker.max_weekly_hours ?? '',
        standby: worker.standby ?? false,
        journey_preference: worker.journey_preference ?? 'any',
        is_active: worker.is_active ?? false,
        notes: worker.notes ?? '',
    };
}

function formStateToPayload(form: WorkerFormState): WorkerPayload {
    return {
        first_name: form.first_name,
        last_name: form.last_name,
        email: form.email || null,
        phone: form.phone || null,
        gender: form.gender,
        date_of_birth: form.date_of_birth || null,
        address_line1: form.address_line1 || null,
        address_line2: form.address_line2 || null,
        city: form.city || null,
        postcode: form.postcode || null,
        country: form.country || null,
        contracted_weekly_hours: Number(form.contracted_weekly_hours),
        max_weekly_hours: form.max_weekly_hours ? Number(form.max_weekly_hours) : null,
        standby: form.standby,
        journey_preference: form.journey_preference,
        is_active: form.is_active,
        notes: form.notes || null,
    };
}

export function WorkerFormPage() {
    const { id } = useParams<{ id: string }>();
    const isEdit = Boolean(id);
    const navigate = useNavigate();
    const { data, isLoading } = useWorkerQuery(id);
    const createMutation = useCreateWorkerMutation();
    const updateMutation = useUpdateWorkerMutation(id ?? '');
    const [form, setForm] = useState<WorkerFormState>(EMPTY_FORM);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [hydrated, setHydrated] = useState(!isEdit);

    useEffect(() => {
        if (data?.data && !hydrated) {
            setForm(workerToFormState(data.data));
            setHydrated(true);
        }
    }, [data, hydrated]);

    function set<K extends keyof WorkerFormState>(key: K, value: WorkerFormState[K]) {
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
            navigate('/workers');
        } catch (error) {
            if (error instanceof ApiError && error.errors) {
                setErrors(error.errors);
            }
        }
    }

    const close = () => navigate('/workers');
    const isSubmitting = createMutation.isPending || updateMutation.isPending;

    if (isEdit && isLoading && !hydrated) {
        return (
            <Modal title="Loading…" onClose={close}>
                <p className="text-sm text-slate-500 dark:text-slate-400">Loading worker…</p>
            </Modal>
        );
    }

    return (
        <Modal
            title={isEdit ? 'Edit worker' : 'Add worker'}
            subtitle={isEdit ? "Update this worker's details." : 'Add a new support worker.'}
            onClose={close}
        >
            <form onSubmit={(event) => void handleSubmit(event)} className="space-y-4" noValidate>
                <div className="grid grid-cols-2 gap-4">
                    <FormField label="First name" htmlFor="first_name" required error={errors.first_name}>
                        <input
                            id="first_name"
                            className={fieldInputClasses}
                            value={form.first_name}
                            onChange={(event) => set('first_name', event.target.value)}
                            required
                        />
                    </FormField>
                    <FormField label="Last name" htmlFor="last_name" required error={errors.last_name}>
                        <input
                            id="last_name"
                            className={fieldInputClasses}
                            value={form.last_name}
                            onChange={(event) => set('last_name', event.target.value)}
                            required
                        />
                    </FormField>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <FormField label="Email" htmlFor="email" error={errors.email}>
                        <input
                            id="email"
                            type="email"
                            className={fieldInputClasses}
                            value={form.email}
                            onChange={(event) => set('email', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Phone" htmlFor="phone" error={errors.phone}>
                        <input
                            id="phone"
                            className={fieldInputClasses}
                            value={form.phone}
                            onChange={(event) => set('phone', event.target.value)}
                        />
                    </FormField>
                </div>

                <FormField label="Gender" htmlFor="gender" required error={errors.gender}>
                    <PillTabs
                        value={form.gender}
                        onChange={(value) => set('gender', value)}
                        options={[
                            { value: 'male', label: 'Male' },
                            { value: 'female', label: 'Female' },
                            { value: 'other', label: 'Other' },
                            { value: 'prefer_not_to_say', label: 'Prefer not to say' },
                        ]}
                    />
                </FormField>

                <FormField label="Date of birth" htmlFor="date_of_birth" error={errors.date_of_birth}>
                    <input
                        id="date_of_birth"
                        type="date"
                        className={fieldInputClasses}
                        value={form.date_of_birth}
                        onChange={(event) => set('date_of_birth', event.target.value)}
                    />
                </FormField>

                <div>
                    <p className="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Address</p>
                    <div className="space-y-3">
                        <input
                            placeholder="Address line 1"
                            className={fieldInputClasses}
                            value={form.address_line1}
                            onChange={(event) => set('address_line1', event.target.value)}
                        />
                        <input
                            placeholder="Address line 2"
                            className={fieldInputClasses}
                            value={form.address_line2}
                            onChange={(event) => set('address_line2', event.target.value)}
                        />
                        <div className="grid grid-cols-3 gap-3">
                            <input
                                placeholder="City"
                                className={fieldInputClasses}
                                value={form.city}
                                onChange={(event) => set('city', event.target.value)}
                            />
                            <input
                                placeholder="Postcode"
                                className={fieldInputClasses}
                                value={form.postcode}
                                onChange={(event) => set('postcode', event.target.value)}
                            />
                            <input
                                placeholder="Country"
                                className={fieldInputClasses}
                                value={form.country}
                                onChange={(event) => set('country', event.target.value)}
                            />
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <FormField
                        label="Contracted weekly hours"
                        htmlFor="contracted_weekly_hours"
                        required
                        error={errors.contracted_weekly_hours}
                    >
                        <input
                            id="contracted_weekly_hours"
                            type="number"
                            min="0"
                            max="168"
                            step="0.5"
                            className={fieldInputClasses}
                            value={form.contracted_weekly_hours}
                            onChange={(event) => set('contracted_weekly_hours', event.target.value)}
                            required
                        />
                    </FormField>
                    <FormField label="Max weekly hours" htmlFor="max_weekly_hours" error={errors.max_weekly_hours}>
                        <input
                            id="max_weekly_hours"
                            type="number"
                            min="0"
                            max="168"
                            step="0.5"
                            className={fieldInputClasses}
                            value={form.max_weekly_hours}
                            onChange={(event) => set('max_weekly_hours', event.target.value)}
                        />
                    </FormField>
                </div>

                <FormField label="Journey preference" htmlFor="journey_preference" error={errors.journey_preference}>
                    <PillTabs
                        value={form.journey_preference}
                        onChange={(value) => set('journey_preference', value)}
                        options={[
                            { value: 'short', label: 'Short' },
                            { value: 'long', label: 'Long' },
                            { value: 'any', label: 'Any' },
                        ]}
                    />
                </FormField>

                <div className="flex items-center gap-6">
                    <label className="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input
                            type="checkbox"
                            checked={form.standby}
                            onChange={(event) => set('standby', event.target.checked)}
                            className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                        />
                        Standby
                    </label>
                    <label className="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input
                            type="checkbox"
                            checked={form.is_active}
                            onChange={(event) => set('is_active', event.target.checked)}
                            className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                        />
                        Active
                    </label>
                </div>

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
                        {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Add worker'}
                    </Button>
                </div>
            </form>
        </Modal>
    );
}
