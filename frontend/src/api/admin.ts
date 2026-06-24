import api from './client';
import type {
  AdminRole,
  AdminUser,
  CreateUserPayload,
  Paginated,
  PermissionsByModule,
  UpdateUserPayload,
  UserListFilters,
} from '@/types/admin';

function buildQuery(filters: UserListFilters): Record<string, string | number> {
  const out: Record<string, string | number> = {};
  if (filters.search) out.search = filters.search;
  if (filters.role) out.role = filters.role;
  if (filters.outlet_id) out.outlet_id = filters.outlet_id;
  if (filters.is_active !== undefined) out.is_active = filters.is_active ? '1' : '0';
  if (filters.page) out.page = filters.page;
  if (filters.per_page) out.per_page = filters.per_page;
  return out;
}

export async function listUsers(filters: UserListFilters = {}): Promise<Paginated<AdminUser>> {
  const { data } = await api.get<Paginated<AdminUser>>('/v1/users', {
    params: buildQuery(filters),
  });
  return data;
}

export async function getUser(id: number): Promise<AdminUser> {
  const { data } = await api.get<{ data: AdminUser }>(`/v1/users/${id}`);
  return data.data;
}

export async function createUser(payload: CreateUserPayload): Promise<AdminUser> {
  const { data } = await api.post<{ data: AdminUser }>('/v1/users', payload);
  return data.data;
}

export async function updateUser(id: number, payload: UpdateUserPayload): Promise<AdminUser> {
  const { data } = await api.put<{ data: AdminUser }>(`/v1/users/${id}`, payload);
  return data.data;
}

export async function deleteUser(id: number): Promise<void> {
  await api.delete(`/v1/users/${id}`);
}

export async function setUserStatus(
  id: number,
  isActive: boolean,
): Promise<{ is_active: boolean }> {
  const { data } = await api.patch<{ is_active: boolean }>(`/v1/users/${id}/status`, {
    is_active: isActive,
  });
  return data;
}

export async function resetUserPassword(
  id: number,
): Promise<{ success: boolean; message: string }> {
  const { data } = await api.post<{ success: boolean; message: string }>(
    `/v1/users/${id}/reset-password`,
  );
  return data;
}

export async function listRoles(): Promise<AdminRole[]> {
  const { data } = await api.get<{ data: AdminRole[] }>('/v1/roles');
  return data.data;
}

export async function getRolePermissions(
  id: number,
): Promise<{ role: AdminRole; permissions: PermissionsByModule['permissions'] }> {
  const { data } = await api.get<{
    role: AdminRole;
    permissions: PermissionsByModule['permissions'];
  }>(`/v1/roles/${id}/permissions`);
  return data;
}

export async function updateRolePermissions(id: number, permissions: string[]): Promise<AdminRole> {
  const { data } = await api.put<{ role: AdminRole }>(`/v1/roles/${id}/permissions`, {
    permissions,
  });
  return data.role;
}

export async function listPermissionsGrouped(): Promise<PermissionsByModule[]> {
  const { data } = await api.get<{ modules: PermissionsByModule[] }>('/v1/permissions');
  return data.modules;
}
