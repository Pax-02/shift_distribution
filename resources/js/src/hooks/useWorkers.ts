import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createWorker, deleteWorker, getWorker, listWorkers, updateWorker, type ListWorkersParams } from '../api/workers';
import type { WorkerPayload } from '../types/worker';

export const workerKeys = {
    all: ['workers'] as const,
    lists: () => [...workerKeys.all, 'list'] as const,
    list: (params: ListWorkersParams) => [...workerKeys.lists(), params] as const,
    detail: (id: string) => [...workerKeys.all, 'detail', id] as const,
};

export function useWorkersQuery(params: ListWorkersParams) {
    return useQuery({
        queryKey: workerKeys.list(params),
        queryFn: () => listWorkers(params),
    });
}

export function useWorkerQuery(id: string | undefined) {
    return useQuery({
        queryKey: workerKeys.detail(id ?? ''),
        queryFn: () => getWorker(id!),
        enabled: Boolean(id),
    });
}

export function useCreateWorkerMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: WorkerPayload) => createWorker(payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: workerKeys.all }),
    });
}

export function useUpdateWorkerMutation(id: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: Partial<WorkerPayload>) => updateWorker(id, payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: workerKeys.all }),
    });
}

export function useDeleteWorkerMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (id: string) => deleteWorker(id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: workerKeys.all }),
    });
}
