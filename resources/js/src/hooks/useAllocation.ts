import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
    cancelAssignment,
    confirmAssignment,
    getAllocationHistory,
    runAllocation,
    type AllocationHistoryParams,
} from '../api/allocation';
import { shiftKeys } from './useShifts';

export const allocationKeys = {
    all: ['allocation'] as const,
    history: (params: AllocationHistoryParams) => [...allocationKeys.all, 'history', params] as const,
};

export function useAllocationHistoryQuery(params: AllocationHistoryParams) {
    return useQuery({
        queryKey: allocationKeys.history(params),
        queryFn: () => getAllocationHistory(params),
    });
}

// Every mutation here also invalidates shifts: running allocation flips the
// shift to "allocated", confirming flips it to "confirmed" — the shifts
// list/status filter would otherwise show stale data.
export function useRunAllocationMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (shiftId: string) => runAllocation(shiftId),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: allocationKeys.all });
            queryClient.invalidateQueries({ queryKey: shiftKeys.all });
        },
    });
}

export function useConfirmAssignmentMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (id: string) => confirmAssignment(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: allocationKeys.all });
            queryClient.invalidateQueries({ queryKey: shiftKeys.all });
        },
    });
}

export function useCancelAssignmentMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: ({ id, reason }: { id: string; reason?: string }) => cancelAssignment(id, reason),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: allocationKeys.all });
            queryClient.invalidateQueries({ queryKey: shiftKeys.all });
        },
    });
}
