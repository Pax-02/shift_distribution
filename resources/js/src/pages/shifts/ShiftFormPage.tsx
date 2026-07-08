import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ApiError } from '../../api/client';
import { Button } from '../../components/ui/Button';
import { FormField, fieldInputClasses } from '../../components/ui/FormField';
import { Modal } from '../../components/ui/Modal';
import { PillTabs } from '../../components/ui/PillTabs';
import { useClientsQuery } from '../../hooks/useClients';
import { useCreateShiftMutation, useShiftQuery, useUpdateShiftMutation } from '../../hooks/useShifts';
import type { GenderRequirement, ShiftStatus } from '../../types/enums';
import type { CreateShiftPayload, Shift, UpdateShiftPayload } from '../../types/shift';

interface ShiftFormState {
    client_id: string;
    shift_date: string;
    start_time: string;
    end_time: string;
    pickup_address_text: string;
    destination_address_text: string;
    gender_requirement: GenderRequirement;
    status: ShiftStatus;
    notes: string;
}

const EMPTY_FORM: ShiftFormState = {
    client_id: '',
    shift_date: '',
    start_time: '',
    end_time: '',
    pickup_address_text: '',
    destination_address_text: '',
    gender_requirement: 'any',
    status: 'pending',
    notes: '',
};

function shiftToFormState(shift: Shift): ShiftFormState {
    return {
        client_id: shift.client_id,
        shift_date: shift.shift_date,
        // API returns "HH:MM:SS"; the <input type="time"> and the H:i
        // validation on submit both expect "HH:MM".
        start_time: shift.start_time.slice(0, 5),
        end_time: shift.end_time.slice(0, 5),
        pickup_address_text: shift.pickup_address_text ?? '',
        destination_address_text: shift.destination_address_text ?? '',
        gender_requirement: shift.gender_requirement ?? 'any',
        status: shift.status,
        notes: shift.notes ?? '',
    };
}

function formStateToCreatePayload(form: ShiftFormState): CreateShiftPayload {
    return {
        client_id: form.client_id,
        pickup_address_text: form.pickup_address_text || null,
        destination_address_text: form.destination_address_text || null,
        shift_date: form.shift_date,
        start_time: form.start_time,
        end_time: form.end_time,
        gender_requirement: form.gender_requirement,
        notes: form.notes || null,
    };
}

function formStateToUpdatePayload(form: ShiftFormState): UpdateShiftPayload {
    return { ...formStateToCreatePayload(form), status: form.status };
}

export function ShiftFormPage() {
    const { id } = useParams<{ id: string }>();
    const isEdit = Boolean(id);
    const navigate = useNavigate();
    const { data, isLoading } = useShiftQuery(id);
    // Only page 1 of clients — same known limitation as the list page's
    // client-name lookup, fine at this seed-data scale.
    const { data: clientsData } = useClientsQuery({});
    const clients = clientsData?.data ?? [];
    const createMutation = useCreateShiftMutation();
    const updateMutation = useUpdateShiftMutation(id ?? '');
    const [form, setForm] = useState<ShiftFormState>(EMPTY_FORM);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [hydrated, setHydrated] = useState(!isEdit);

    useEffect(() => {
        if (data?.data && !hydrated) {
            setForm(shiftToFormState(data.data));
            setHydrated(true);
        }
    }, [data, hydrated]);

    function set<K extends keyof ShiftFormState>(key: K, value: ShiftFormState[K]) {
        setForm((current) => ({ ...current, [key]: value }));
    }

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setErrors({});
        try {
            if (isEdit) {
                await updateMutation.mutateAsync(formStateToUpdatePayload(form));
            } else {
                await createMutation.mutateAsync(formStateToCreatePayload(form));
            }
            navigate('/shifts');
        } catch (error) {
            if (error instanceof ApiError && error.errors) {
                setErrors(error.errors);
            }
        }
    }

    const close = () => navigate('/shifts');
    const isSubmitting = createMutation.isPending || updateMutation.isPending;

    if (isEdit && isLoading && !hydrated) {
        return (
            <Modal title="Loading…" onClose={close}>
                <p className="text-sm text-slate-500 dark:text-slate-400">Loading shift…</p>
            </Modal>
        );
    }

    return (
        <Modal
            title={isEdit ? 'Edit shift' : 'Add shift'}
            subtitle={isEdit ? 'Update this shift.' : 'Create a new shift request.'}
            onClose={close}
        >
            <form onSubmit={(event) => void handleSubmit(event)} className="space-y-4" noValidate>
                <FormField label="Client" htmlFor="client_id" required error={errors.client_id}>
                    <select
                        id="client_id"
                        className={fieldInputClasses}
                        value={form.client_id}
                        onChange={(event) => set('client_id', event.target.value)}
                        required
                    >
                        <option value="" disabled>
                            Select a client…
                        </option>
                        {clients.map((client) => (
                            <option key={client.id} value={client.id}>
                                {client.name}
                            </option>
                        ))}
                    </select>
                </FormField>

                <FormField label="Shift date" htmlFor="shift_date" required error={errors.shift_date}>
                    <input
                        id="shift_date"
                        type="date"
                        className={fieldInputClasses}
                        value={form.shift_date}
                        onChange={(event) => set('shift_date', event.target.value)}
                        required
                    />
                </FormField>

                <div className="grid grid-cols-2 gap-4">
                    <FormField label="Start time" htmlFor="start_time" required error={errors.start_time}>
                        <input
                            id="start_time"
                            type="time"
                            className={fieldInputClasses}
                            value={form.start_time}
                            onChange={(event) => set('start_time', event.target.value)}
                            required
                        />
                    </FormField>
                    <FormField label="End time" htmlFor="end_time" required error={errors.end_time}>
                        <input
                            id="end_time"
                            type="time"
                            className={fieldInputClasses}
                            value={form.end_time}
                            onChange={(event) => set('end_time', event.target.value)}
                            required
                        />
                    </FormField>
                </div>

                <FormField label="Pickup address" htmlFor="pickup_address_text" required error={errors.pickup_address_text}>
                    <input
                        id="pickup_address_text"
                        className={fieldInputClasses}
                        value={form.pickup_address_text}
                        onChange={(event) => set('pickup_address_text', event.target.value)}
                        required
                    />
                </FormField>

                <FormField
                    label="Destination address"
                    htmlFor="destination_address_text"
                    required
                    error={errors.destination_address_text}
                >
                    <input
                        id="destination_address_text"
                        className={fieldInputClasses}
                        value={form.destination_address_text}
                        onChange={(event) => set('destination_address_text', event.target.value)}
                        required
                    />
                </FormField>

                <FormField label="Gender requirement" htmlFor="gender_requirement" error={errors.gender_requirement}>
                    <PillTabs
                        value={form.gender_requirement}
                        onChange={(value) => set('gender_requirement', value)}
                        options={[
                            { value: 'any', label: 'Any' },
                            { value: 'male', label: 'Male' },
                            { value: 'female', label: 'Female' },
                        ]}
                    />
                </FormField>

                {isEdit && (
                    <FormField label="Status" htmlFor="status" error={errors.status}>
                        <select
                            id="status"
                            className={fieldInputClasses}
                            value={form.status}
                            onChange={(event) => set('status', event.target.value as ShiftStatus)}
                        >
                            <option value="pending">Pending</option>
                            <option value="allocated">Allocated</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </FormField>
                )}

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
                        {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Add shift'}
                    </Button>
                </div>
            </form>
        </Modal>
    );
}
