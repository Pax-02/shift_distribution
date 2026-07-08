import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createShift, deleteShift, getShift, listShifts, updateShift, type ListShiftsParams } from '../api/shifts';
import type { CreateShiftPayload, UpdateShiftPayload } from '../types/shift';

export const shiftKeys = {
    all: ['shifts'] as const,
    lists: () => [...shiftKeys.all, 'list'] as const,
    list: (params: ListShiftsParams) => [...shiftKeys.lists(), params] as const,
    detail: (id: string) => [...shiftKeys.all, 'detail', id] as const,
};

export function useShiftsQuery(params: ListShiftsParams) {
    return useQuery({
        queryKey: shiftKeys.list(params),
        queryFn: () => listShifts(params),
    });
}

export function useShiftQuery(id: string | undefined) {
    return useQuery({
        queryKey: shiftKeys.detail(id ?? ''),
        queryFn: () => getShift(id!),
        enabled: Boolean(id),
    });
}

export function useCreateShiftMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: CreateShiftPayload) => createShift(payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: shiftKeys.all }),
    });
}

export function useUpdateShiftMutation(id: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: UpdateShiftPayload) => updateShift(id, payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: shiftKeys.all }),
    });
}

export function useDeleteShiftMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (id: string) => deleteShift(id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: shiftKeys.all }),
    });
}
