import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { AxiosError } from 'axios';
import { useAuthStore } from '@/stores/authStore';
import { useLogin } from '@/hooks/useAuth';
import { Card } from '@/components/common/Card';
import type { ApiError } from '@/types/auth';
import { APP_NAME } from '@/constants/app';

const schema = z.object({
  email: z.string().min(1, 'Email is required').email('Enter a valid email address'),
  password: z.string().min(1, 'Password is required'),
  remember: z.boolean().optional(),
});

type LoginForm = z.infer<typeof schema>;

type LocationState = { from?: string } | null;

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const token = useAuthStore((s) => s.token);
  const login = useLogin();
  const [error, setError] = useState<string | null>(null);

  const from = (location.state as LocationState)?.from ?? '/dashboard';

  useEffect(() => {
    if (token) navigate('/dashboard', { replace: true });
  }, [token, navigate]);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<LoginForm>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '', remember: false },
  });

  const onSubmit = handleSubmit(async (values) => {
    setError(null);
    try {
      const res = await login.mutateAsync({
        email: values.email,
        password: values.password,
        device_name: navigator.userAgent,
      });
      navigate(res.must_change_password ? '/profile?reason=must-change-password' : from, {
        replace: true,
      });
    } catch (e) {
      const err = e as AxiosError<ApiError>;
      if (err.response?.status === 403) {
        setError(err.response.data?.message ?? 'Account is not allowed to sign in.');
      } else if (err.response?.status === 429) {
        setError('Too many attempts. Please try again in a minute.');
      } else {
        setError('Invalid email or password.');
      }
    }
  });

  return (
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 via-white to-rms-50 p-6">
      <Card className="w-full max-w-md">
        <div className="mb-6 flex flex-col gap-1">
          <span className="text-xs font-semibold uppercase tracking-wider text-rms-600">
            {APP_NAME}
          </span>
          <h1 className="text-2xl font-bold text-slate-900">Sign in to your account</h1>
          <p className="text-sm text-slate-500">Use your RMS credentials to continue.</p>
        </div>

        <form className="flex flex-col gap-4" onSubmit={onSubmit} noValidate>
          <Field label="Email" htmlFor="email" error={errors.email?.message}>
            <input
              id="email"
              type="email"
              autoComplete="email"
              className={inputCls(!!errors.email)}
              {...register('email')}
            />
          </Field>
          <Field label="Password" htmlFor="password" error={errors.password?.message}>
            <input
              id="password"
              type="password"
              autoComplete="current-password"
              className={inputCls(!!errors.password)}
              {...register('password')}
            />
          </Field>

          <div className="flex items-center justify-between text-sm">
            <label className="inline-flex items-center gap-2 text-slate-600">
              <input
                type="checkbox"
                className="rounded border-slate-300"
                {...register('remember')}
              />
              Stay signed in
            </label>
            <Link to="/forgot-password" className="font-medium text-rms-600 hover:text-rms-700">
              Forgot password?
            </Link>
          </div>

          {error && (
            <div className="rounded-md border border-danger-100 bg-danger-50 px-3 py-2 text-sm text-danger-600">
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={isSubmitting}
            className="rounded-md bg-rms-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rms-700 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {isSubmitting ? 'Signing in…' : 'Sign In'}
          </button>
        </form>
      </Card>
    </main>
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
    'w-full rounded-md border px-3 py-2 text-sm shadow-sm transition focus:outline-none focus:ring-2',
    hasError
      ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-100'
      : 'border-slate-300 focus:border-rms-500 focus:ring-rms-100',
  ].join(' ');
}
