import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createClient, deleteClient, getClient, listClients, updateClient, type ListClientsParams } from '../api/clients';
import type { ClientPayload } from '../types/client';

export const clientKeys = {
    all: ['clients'] as const,
    lists: () => [...clientKeys.all, 'list'] as const,
    list: (params: ListClientsParams) => [...clientKeys.lists(), params] as const,
    detail: (id: string) => [...clientKeys.all, 'detail', id] as const,
};

export function useClientsQuery(params: ListClientsParams) {
    return useQuery({
        queryKey: clientKeys.list(params),
        queryFn: () => listClients(params),
    });
}

export function useClientQuery(id: string | undefined) {
    return useQuery({
        queryKey: clientKeys.detail(id ?? ''),
        queryFn: () => getClient(id!),
        enabled: Boolean(id),
    });
}

export function useCreateClientMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: ClientPayload) => createClient(payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: clientKeys.all }),
    });
}

export function useUpdateClientMutation(id: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (payload: Partial<ClientPayload>) => updateClient(id, payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: clientKeys.all }),
    });
}

export function useDeleteClientMutation() {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (id: string) => deleteClient(id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: clientKeys.all }),
    });
}
