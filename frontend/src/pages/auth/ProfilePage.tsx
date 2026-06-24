import { useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useChangePassword, useLogout, useMe } from '@/hooks/useAuth';
import { useAuthStore } from '@/stores/authStore';
import { Card } from '@/components/common/Card';
import { formatIsoDate } from '@/utils/date';

const schema = z
  .object({
    current_password: z.string().min(1, 'Current password is required'),
    password: z
      .string()
      .min(8, 'Password must be at least 8 characters')
      .regex(/[A-Za-z]/, 'Include at least one letter')
      .regex(/\d/, 'Include at least one number'),
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })
  .refine((d) => d.password !== d.current_password, {
    message: 'New password must differ from current',
    path: ['password'],
  });

type ChangeForm = z.infer<typeof schema>;

export default function ProfilePage() {
  const [params] = useSearchParams();
  const reason = params.get('reason');
  const { data } = useMe();
  const user = useAuthStore((s) => s.user);
  const mustChange = useAuthStore((s) => s.mustChangePassword);
  const changePassword = useChangePassword();
  const logout = useLogout();
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const displayUser = data?.user ?? user;

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ChangeForm>({
    resolver: zodResolver(schema),
    defaultValues: { current_password: '', password: '', password_confirmation: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setError(null);
    setSuccess(null);
    try {
      await changePassword.mutateAsync(values);
      setSuccess('Password changed successfully.');
      reset();
    } catch {
      setError('Could not change password. Check your current password.');
    }
  });

  if (!displayUser) {
    return <div className="p-8 text-sm text-slate-500">Loading profile…</div>;
  }

  return (
    <main className="mx-auto max-w-4xl space-y-6 p-6">
      <header>
        <h1 className="text-2xl font-bold text-slate-900">Profile</h1>
        <p className="text-sm text-slate-500">Your account, security, and assigned outlets.</p>
      </header>

      {reason === 'must-change-password' && mustChange && (
        <div className="rounded-md border border-warn-100 bg-warn-50 px-3 py-2 text-sm text-warn-600">
          Please change your password before continuing.
        </div>
      )}

      <Card>
        <h2 className="mb-4 text-lg font-semibold text-slate-900">Account</h2>
        <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Row label="Name" value={displayUser.name} />
          <Row label="Email" value={displayUser.email} />
          <Row label="Phone" value={displayUser.phone ?? '—'} />
          <Row
            label="Last login"
            value={displayUser.last_login_at ? formatIsoDate(displayUser.last_login_at) : 'Never'}
          />
          <Row label="Roles">
            <div className="flex flex-wrap gap-1">
              {displayUser.roles.map((r) => (
                <span
                  key={r}
                  className="rounded-full bg-rms-50 px-2 py-0.5 text-xs font-semibold text-rms-700"
                >
                  {r}
                </span>
              ))}
            </div>
          </Row>
          <Row label="Outlets">
            <div className="flex flex-wrap gap-1">
              {displayUser.outlets.length === 0 && <span className="text-slate-400">None</span>}
              {displayUser.outlets.map((o) => (
                <span
                  key={o.id}
                  className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                >
                  {o.name} ({o.code})
                </span>
              ))}
            </div>
          </Row>
        </dl>
        <div className="mt-4 flex justify-end">
          <button
            type="button"
            onClick={() => logout.mutate()}
            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          >
            Sign out
          </button>
        </div>
      </Card>

      <Card>
        <h2 id="security" className="mb-4 text-lg font-semibold text-slate-900">
          Security
        </h2>
        <form className="flex flex-col gap-4" onSubmit={onSubmit} noValidate>
          <Field
            label="Current password"
            htmlFor="current_password"
            error={errors.current_password?.message}
          >
            <input
              id="current_password"
              type="password"
              autoComplete="current-password"
              className={inputCls(!!errors.current_password)}
              {...register('current_password')}
            />
          </Field>
          <Field label="New password" htmlFor="password" error={errors.password?.message}>
            <input
              id="password"
              type="password"
              autoComplete="new-password"
              className={inputCls(!!errors.password)}
              {...register('password')}
            />
          </Field>
          <Field
            label="Confirm new password"
            htmlFor="password_confirmation"
            error={errors.password_confirmation?.message}
          >
            <input
              id="password_confirmation"
              type="password"
              autoComplete="new-password"
              className={inputCls(!!errors.password_confirmation)}
              {...register('password_confirmation')}
            />
          </Field>

          {error && (
            <div className="rounded-md border border-danger-100 bg-danger-50 px-3 py-2 text-sm text-danger-600">
              {error}
            </div>
          )}
          {success && (
            <div className="rounded-md border border-success-100 bg-success-50 px-3 py-2 text-sm text-success-600">
              {success}
            </div>
          )}

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={isSubmitting}
              className="rounded-md bg-rms-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rms-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {isSubmitting ? 'Saving…' : 'Change password'}
            </button>
          </div>
        </form>
      </Card>
    </main>
  );
}

function Row({
  label,
  value,
  children,
}: {
  label: string;
  value?: string;
  children?: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-1">
      <dt className="text-xs uppercase tracking-wide text-slate-500">{label}</dt>
      <dd className="text-sm font-medium text-slate-900">{children ?? value}</dd>
    </div>
  );
}

function Field({
  label,
  htmlFor,
  error,
  children,
}: {
  label: string;
  htmlFor: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-1">
      <label htmlFor={htmlFor} className="text-sm font-medium text-slate-700">
        {label}
      </label>
      {children}
      {error && <p className="text-xs text-danger-600">{error}</p>}
    </div>
  );
}

function inputCls(hasError: boolean): string {
  return [
    'w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
    hasError
      ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-100'
      : 'border-slate-300 focus:border-rms-500 focus:ring-rms-100',
  ].join(' ');
}
