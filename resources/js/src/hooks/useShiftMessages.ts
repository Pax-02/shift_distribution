import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createShiftMessage } from '../api/shiftMessages';
import type { CreateShiftMessagePayload } from '../types/shiftMessage';
import { shiftKeys } from './useShifts';

// A validated message can create a Shift as a side effect (see
// ParseShiftMessageJob), so invalidate shifts alongside the mutation itself
// — otherwise the new shift wouldn't show up in the pending-shift picker
// without a manual refresh.
export function useCreateShiftMessageMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: CreateShiftMessagePayload) => createShiftMessage(payload),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: shiftKeys.all });
        },
    });
}
