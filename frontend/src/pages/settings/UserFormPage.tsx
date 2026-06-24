import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { AxiosError } from 'axios';
import { useAuthStore } from '@/stores/authStore';
import { useCreateUser, useRoles, useUpdateUser, useUser } from '@/hooks/useAdmin';
import { CheckboxGroup } from '@/components/common/CheckboxGroup';
import { Card } from '@/components/common/Card';
import { formatIsoDate } from '@/utils/date';

const schema = z
  .object({
    name: z.string().min(1, 'Name is required'),
    email: z.string().email('Enter a valid email'),
    phone: z.string().optional(),
    password: z.string().optional(),
    password_confirmation: z.string().optional(),
    roles: z.array(z.string()).min(1, 'At least one role is required'),
    outlets: z.array(z.number()).min(1, 'At least one outlet is required'),
    is_active: z.boolean().default(true),
    must_change_password: z.boolean().default(true),
  })
  .superRefine((data, ctx) => {
    if (!data.password || data.password.length === 0) return;
    if (data.password.length < 8) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        path: ['password'],
        message: 'Password must be at least 8 characters',
      });
    }
    if (data.password !== data.password_confirmation) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        path: ['password_confirmation'],
        message: 'Passwords do not match',
      });
    }
  });

type FormValues = z.infer<typeof schema>;
void ({} as FormValues);

export default function UserFormPage() {
  const { id } = useParams<{ id: string }>();
  const isEdit = Boolean(id);
  const navigate = useNavigate();
  const actor = useAuthStore((s) => s.user);
  const { data: roles = [] } = useRoles();
  const { data: user, isLoading } = useUser(isEdit ? Number(id) : null);
  const createUser = useCreateUser();
  const updateUser = useUpdateUser(Number(id));

  const outletOptions = useMemo(
    () => (actor?.outlets ?? []).map((o) => ({ value: o.id, label: `${o.name} (${o.code})` })),
    [actor],
  );

  const [error, setError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    reset,
    formState: { errors, isSubmitting },
  } = useForm({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      roles: [] as string[],
      outlets: outletOptions.map((o) => o.value),
      is_active: true,
      must_change_password: true,
    },
  });

  useEffect(() => {
    if (user) {
      reset({
        name: user.name,
        email: user.email,
        phone: user.phone ?? '',
        password: '',
        password_confirmation: '',
        roles: user.roles,
        outlets: user.outlets.map((o) => o.id),
        is_active: user.is_active,
        must_change_password: user.must_change_password,
      });
    }
  }, [user, reset]);

  const onSubmit = handleSubmit(async (values) => {
    setError(null);
    try {
      if (isEdit && id) {
        await updateUser.mutateAsync({
          name: values.name,
          email: values.email,
          phone: values.phone,
          roles: values.roles,
          outlets: values.outlets,
          is_active: values.is_active,
          must_change_password: values.must_change_password,
          ...(values.password
            ? { password: values.password, password_confirmation: values.password_confirmation }
            : {}),
        });
      } else {
        await createUser.mutateAsync({
          name: values.name,
          email: values.email,
          phone: values.phone,
          password: values.password ?? '',
          password_confirmation: values.password_confirmation ?? '',
          roles: values.roles,
          outlets: values.outlets,
          is_active: values.is_active,
          must_change_password: values.must_change_password,
        });
      }
      navigate('/settings/users');
    } catch (e) {
      const err = e as AxiosError<{ message?: string; errors?: Record<string, string[]> }>;
      setError(err.response?.data?.message ?? 'Could not save user.');
    }
  });

  const roleChecklist = roles.map((r) => ({ value: r.name, label: r.display_name ?? r.name }));
  const selectedRoles = watch('roles') ?? [];
  const selectedOutlets = watch('outlets') ?? [];

  return (
    <main className="mx-auto max-w-3xl space-y-6 p-6">
      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">{isEdit ? 'Edit User' : 'New User'}</h1>
          {user && (
            <p className="text-xs text-slate-500">
              Created {formatIsoDate(user.created_at ?? '')} · Last login{' '}
              {user.last_login_at ? formatIsoDate(user.last_login_at) : 'never'}
            </p>
          )}
        </div>
      </header>

      <form onSubmit={onSubmit} noValidate className="space-y-6">
        <Card>
          <h2 className="mb-4 text-lg font-semibold text-slate-900">Personal info</h2>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Field label="Name" htmlFor="name" error={errors.name?.message}>
              <input
                id="name"
                type="text"
                className={inputCls(!!errors.name)}
                {...register('name')}
              />
            </Field>
            <Field label="Email" htmlFor="email" error={errors.email?.message}>
              <input
                id="email"
                type="email"
                className={inputCls(!!errors.email)}
                {...register('email')}
              />
            </Field>
            <Field label="Phone" htmlFor="phone" error={errors.phone?.message}>
              <input
                id="phone"
                type="tel"
                className={inputCls(!!errors.phone)}
                {...register('phone')}
              />
            </Field>
            <Field label="Avatar URL" htmlFor="avatar">
              <input id="avatar" type="text" className={inputCls(false)} placeholder="https://…" />
            </Field>
          </div>
        </Card>

        <Card>
          <h2 className="mb-4 text-lg font-semibold text-slate-900">Security</h2>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Field
              label={isEdit ? 'New password (leave blank to keep)' : 'Password'}
              htmlFor="password"
              error={errors.password?.message}
            >
              <input
                id="password"
                type="password"
                autoComplete="new-password"
                className={inputCls(!!errors.password)}
                {...register('password')}
              />
            </Field>
            <Field
              label="Confirm password"
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
          </div>
          <label className="mt-3 inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" {...register('must_change_password')} />
            Force password change on first login
          </label>
          <label className="ml-4 inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" {...register('is_active')} />
            Active
          </label>
        </Card>

        <Card>
          <h2 className="mb-4 text-lg font-semibold text-slate-900">Roles</h2>
          <Field error={errors.roles?.message}>
            <CheckboxGroup
              options={roleChecklist}
              selected={selectedRoles}
              onChange={(next) => setValue('roles', next, { shouldValidate: true })}
            />
          </Field>
        </Card>

        <Card>
          <h2 className="mb-4 text-lg font-semibold text-slate-900">Outlet access</h2>
          <Field error={errors.outlets?.message}>
            <CheckboxGroup
              options={outletOptions}
              selected={selectedOutlets}
              onChange={(next) => setValue('outlets', next, { shouldValidate: true })}
            />
          </Field>
        </Card>

        {error && (
          <div className="rounded-md border border-danger-100 bg-danger-50 px-3 py-2 text-sm text-danger-600">
            {error}
          </div>
        )}

        <div className="flex justify-end gap-2">
          <button
            type="button"
            onClick={() => navigate('/settings/users')}
            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={isSubmitting || isLoading}
            className="rounded-md bg-rms-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rms-700 disabled:opacity-60"
          >
            {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Create user'}
          </button>
        </div>
      </form>
    </main>
  );
}

function Field({
  label,
  htmlFor,
  error,
  children,
}: {
  label?: string;
  htmlFor?: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-1">
      {label && (
        <label htmlFor={htmlFor} className="text-sm font-medium text-slate-700">
          {label}
        </label>
      )}
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
