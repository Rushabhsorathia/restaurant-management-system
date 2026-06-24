import type { OutletSummary } from './auth';

export type UserStatus = 'active' | 'inactive';

export type AdminUser = {
  id: number;
  restaurant_id: number | null;
  name: string;
  email: string;
  phone: string | null;
  avatar_url: string | null;
  is_active: boolean;
  must_change_password: boolean;
  last_login_at: string | null;
  roles: string[];
  permissions: string[];
  outlets: OutletSummary[];
  created_at: string | null;
  deleted_at: string | null;
};

export type Paginated<T> = {
  data: T[];
  links: Record<string, unknown>;
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
  };
};

export type UserListFilters = {
  search?: string;
  role?: string;
  outlet_id?: number;
  is_active?: boolean;
  page?: number;
  per_page?: number;
};

export type CreateUserPayload = {
  name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  roles: string[];
  outlets: number[];
  is_active?: boolean;
  must_change_password?: boolean;
  restaurant_id?: number;
};

export type UpdateUserPayload = Partial<Omit<CreateUserPayload, 'password_confirmation'>> & {
  password?: string;
  password_confirmation?: string;
};

export type AdminRole = {
  id: number;
  name: string;
  display_name: string | null;
  guard_name: string;
  permissions_count?: number;
  permissions?: string[];
};

export type AdminPermission = {
  id: number;
  name: string;
  display_name: string | null;
  module: string | null;
  guard_name: string;
};

export type PermissionsByModule = {
  module: string | null;
  permissions: AdminPermission[];
};
