export type RoleName = string;

export type PermissionName = string;

export type OutletSummary = {
  id: number;
  restaurant_id: number;
  name: string;
  code: string;
  type: string;
  city: string | null;
  is_active: boolean;
  is_central_kitchen: boolean;
};

export type AuthUser = {
  id: number;
  restaurant_id: number | null;
  name: string;
  email: string;
  phone: string | null;
  avatar_url: string | null;
  is_active: boolean;
  must_change_password?: boolean;
  last_login_at: string | null;
  roles: RoleName[];
  permissions: PermissionName[];
  outlets: OutletSummary[];
  created_at: string | null;
};

export type LoginResponse = {
  success: boolean;
  token: string;
  must_change_password: boolean;
  user: AuthUser;
};

export type MeResponse = {
  success: boolean;
  must_change_password: boolean;
  user: AuthUser;
};

export type ApiError = {
  success: false;
  message?: string;
  code?: string;
  errors?: Record<string, string[]>;
};
