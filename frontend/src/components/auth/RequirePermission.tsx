import type { ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuthStore } from '@/stores/authStore';

type RequirePermissionProps = {
  permission: string | string[];
  children: ReactNode;
  fallback?: ReactNode;
};

export function RequirePermission({ permission, children, fallback }: RequirePermissionProps) {
  const user = useAuthStore((s) => s.user);
  const perms = user?.permissions ?? [];
  const required = Array.isArray(permission) ? permission : [permission];
  const allowed = required.some((p) => perms.includes(p));

  if (!allowed) {
    return fallback ? <>{fallback}</> : <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
}
