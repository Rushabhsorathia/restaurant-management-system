import { Navigate, useLocation } from 'react-router-dom';
import type { ReactNode } from 'react';
import { useAuthStore } from '@/stores/authStore';
import { useMe } from '@/hooks/useAuth';

type RequireAuthProps = {
  children: ReactNode;
};

export function RequireAuth({ children }: RequireAuthProps) {
  const token = useAuthStore((s) => s.token);
  const location = useLocation();
  const { data, isLoading, isError } = useMe();

  if (!token) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center text-sm text-slate-500">
        Loading session…
      </div>
    );
  }

  if (isError) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  const mustChange = data?.must_change_password ?? false;
  if (mustChange && !location.pathname.startsWith('/profile')) {
    return <Navigate to="/profile?reason=must-change-password" replace />;
  }

  return <>{children}</>;
}

type PublicOnlyProps = {
  children: ReactNode;
};

export function PublicOnly({ children }: PublicOnlyProps) {
  const token = useAuthStore((s) => s.token);
  if (token) {
    return <Navigate to="/dashboard" replace />;
  }
  return <>{children}</>;
}
