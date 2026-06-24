import { Link } from 'react-router-dom';
import { Card } from '@/components/common/Card';
import { useAuthStore } from '@/stores/authStore';
import { APP_NAME } from '@/constants/app';

export default function DashboardPage() {
  const user = useAuthStore((s) => s.user);
  const mustChange = useAuthStore((s) => s.mustChangePassword);

  return (
    <main className="mx-auto max-w-5xl space-y-6 p-6">
      <header>
        <span className="text-xs font-semibold uppercase tracking-wider text-rms-600">
          {APP_NAME}
        </span>
        <h1 className="text-3xl font-bold text-slate-900">
          Welcome{user ? `, ${user.name.split(' ')[0]}` : ''}
        </h1>
        <p className="text-sm text-slate-500">
          You&apos;re signed in. The POS, menu, and admin tools will appear here in upcoming
          milestones.
        </p>
      </header>

      {mustChange && (
        <div className="rounded-md border border-warn-100 bg-warn-50 px-3 py-2 text-sm text-warn-600">
          Your account requires a password change.{' '}
          <Link to="/profile?reason=must-change-password" className="font-semibold underline">
            Change it now
          </Link>
          .
        </div>
      )}

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Card>
          <p className="text-xs uppercase tracking-wide text-slate-500">Roles</p>
          <div className="mt-1 flex flex-wrap gap-1">
            {user?.roles.length ? (
              user.roles.map((r) => (
                <span
                  key={r}
                  className="rounded-full bg-rms-50 px-2 py-0.5 text-xs font-semibold text-rms-700"
                >
                  {r}
                </span>
              ))
            ) : (
              <span className="text-slate-400">None</span>
            )}
          </div>
        </Card>
        <Card>
          <p className="text-xs uppercase tracking-wide text-slate-500">Outlets</p>
          <p className="mt-1 text-sm font-semibold text-slate-900">{user?.outlets.length ?? 0}</p>
        </Card>
        <Card>
          <p className="text-xs uppercase tracking-wide text-slate-500">Permissions</p>
          <p className="mt-1 text-sm font-semibold text-slate-900">
            {user?.permissions.length ?? 0}
          </p>
        </Card>
      </div>
    </main>
  );
}
