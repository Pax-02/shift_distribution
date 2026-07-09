import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
    cancelAssignment,
    confirmAssignment,
    getAllocationHistory,
    runAllocation,
    type AllocationHistoryParams,
} from '../api/allocation';
import { reportKeys } from './useReports';
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

// Every mutation here also invalidates shifts (running flips a shift to
// "allocated", confirming to "confirmed") and reports (RecalculateWorkerHours
// runs server-side on confirm/cancel, changing what /reports/hours and
// /reports/shifts return for the affected week) — otherwise those pages
// silently keep serving a cached-but-now-wrong result within staleTime.
export function useRunAllocationMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (shiftId: string) => runAllocation(shiftId),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: allocationKeys.all });
            queryClient.invalidateQueries({ queryKey: shiftKeys.all });
            queryClient.invalidateQueries({ queryKey: reportKeys.all });
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
            queryClient.invalidateQueries({ queryKey: reportKeys.all });
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
            queryClient.invalidateQueries({ queryKey: reportKeys.all });
        },
    });
}
