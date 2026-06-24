import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  createUser,
  deleteUser,
  getRolePermissions,
  getUser,
  listPermissionsGrouped,
  listRoles,
  listUsers,
  resetUserPassword,
  setUserStatus,
  updateRolePermissions,
  updateUser,
} from '@/api/admin';
import type { CreateUserPayload, UpdateUserPayload, UserListFilters } from '@/types/admin';

export const usersListKey = (filters: UserListFilters) => ['admin', 'users', filters] as const;
export const userDetailKey = (id: number) => ['admin', 'users', id] as const;
export const rolesKey = ['admin', 'roles'] as const;
export const rolePermissionsKey = (id: number) => ['admin', 'roles', id, 'permissions'] as const;
export const permissionsGroupedKey = ['admin', 'permissions', 'grouped'] as const;

export function useUsers(filters: UserListFilters = {}) {
  return useQuery({
    queryKey: usersListKey(filters),
    queryFn: () => listUsers(filters),
    staleTime: 30_000,
  });
}

export function useUser(id: number | null) {
  return useQuery({
    queryKey: userDetailKey(id ?? 0),
    queryFn: () => getUser(id as number),
    enabled: id != null,
  });
}

export function useCreateUser() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateUserPayload) => createUser(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['admin', 'users'] }),
  });
}

export function useUpdateUser(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: UpdateUserPayload) => updateUser(id, payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['admin', 'users'] });
      qc.invalidateQueries({ queryKey: userDetailKey(id) });
    },
  });
}

export function useDeleteUser() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteUser(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['admin', 'users'] }),
  });
}

export function useSetUserStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, isActive }: { id: number; isActive: boolean }) =>
      setUserStatus(id, isActive),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['admin', 'users'] }),
  });
}

export function useResetUserPassword() {
  return useMutation({
    mutationFn: (id: number) => resetUserPassword(id),
  });
}

export function useRoles() {
  return useQuery({ queryKey: rolesKey, queryFn: listRoles, staleTime: 5 * 60_000 });
}

export function useRolePermissions(id: number | null) {
  return useQuery({
    queryKey: rolePermissionsKey(id ?? 0),
    queryFn: () => getRolePermissions(id as number),
    enabled: id != null,
  });
}

export function useUpdateRolePermissions(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (permissions: string[]) => updateRolePermissions(id, permissions),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: rolePermissionsKey(id) });
      qc.invalidateQueries({ queryKey: rolesKey });
    },
  });
}

export function usePermissionsGrouped() {
  return useQuery({
    queryKey: permissionsGroupedKey,
    queryFn: listPermissionsGrouped,
    staleTime: 5 * 60_000,
  });
}
