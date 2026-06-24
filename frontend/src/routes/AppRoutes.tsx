import { Navigate, Route, Routes } from 'react-router-dom';
import LoginPage from '@/pages/auth/LoginPage';
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage';
import ResetPasswordPage from '@/pages/auth/ResetPasswordPage';
import ProfilePage from '@/pages/auth/ProfilePage';
import DashboardPage from '@/pages/dashboard/DashboardPage';
import UsersListPage from '@/pages/settings/UsersListPage';
import UserFormPage from '@/pages/settings/UserFormPage';
import RolesPage from '@/pages/settings/RolesPage';
import { PublicOnly, RequireAuth } from '@/components/auth/RequireAuth';
import { RequirePermission } from '@/components/auth/RequirePermission';

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/dashboard" replace />} />

      <Route
        path="/login"
        element={
          <PublicOnly>
            <LoginPage />
          </PublicOnly>
        }
      />
      <Route path="/forgot-password" element={<ForgotPasswordPage />} />
      <Route path="/reset-password" element={<ResetPasswordPage />} />

      <Route
        path="/dashboard"
        element={
          <RequireAuth>
            <DashboardPage />
          </RequireAuth>
        }
      />
      <Route
        path="/profile"
        element={
          <RequireAuth>
            <ProfilePage />
          </RequireAuth>
        }
      />

      <Route
        path="/settings/users"
        element={
          <RequireAuth>
            <RequirePermission permission={['users.view', 'users.create']}>
              <UsersListPage />
            </RequirePermission>
          </RequireAuth>
        }
      />
      <Route
        path="/settings/users/new"
        element={
          <RequireAuth>
            <RequirePermission permission="users.create">
              <UserFormPage />
            </RequirePermission>
          </RequireAuth>
        }
      />
      <Route
        path="/settings/users/:id/edit"
        element={
          <RequireAuth>
            <RequirePermission permission="users.update">
              <UserFormPage />
            </RequirePermission>
          </RequireAuth>
        }
      />
      <Route
        path="/settings/roles"
        element={
          <RequireAuth>
            <RequirePermission permission="role.view">
              <RolesPage />
            </RequirePermission>
          </RequireAuth>
        }
      />

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
