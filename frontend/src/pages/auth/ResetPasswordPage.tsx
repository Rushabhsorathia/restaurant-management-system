import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useResetPassword } from '@/hooks/useAuth';
import { Card } from '@/components/common/Card';

const schema = z
  .object({
    email: z.string().min(1, 'Email is required').email('Enter a valid email address'),
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
  });

type ResetForm = z.infer<typeof schema>;

export default function ResetPasswordPage() {
  const [params] = useSearchParams();
  const token = params.get('token') ?? '';
  const email = params.get('email') ?? '';
  const navigate = useNavigate();
  const reset = useResetPassword();
  const [error, setError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<ResetForm>({
    resolver: zodResolver(schema),
    defaultValues: { email, password: '', password_confirmation: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setError(null);
    try {
      await reset.mutateAsync({
        token,
        email: values.email,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });
      navigate('/login', { replace: true, state: { reset: true } });
    } catch {
      setError('This reset link is invalid or has expired.');
    }
  });

  return (
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 via-white to-rms-50 p-6">
      <Card className="w-full max-w-md">
        <h1 className="mb-1 text-2xl font-bold text-slate-900">Set a new password</h1>
        <p className="mb-6 text-sm text-slate-500">
          Choose a strong password you don&apos;t use anywhere else.
        </p>

        {!token && (
          <div className="rounded-md border border-danger-100 bg-danger-50 px-3 py-2 text-sm text-danger-600">
            Missing or invalid reset token.
          </div>
        )}

        <form className="flex flex-col gap-4" onSubmit={onSubmit} noValidate>
          <div className="flex flex-col gap-1">
            <label htmlFor="email" className="text-sm font-medium text-slate-700">
              Email
            </label>
            <input
              id="email"
              type="email"
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rms-500 focus:outline-none focus:ring-2 focus:ring-rms-100"
              {...register('email')}
            />
            {errors.email && <p className="text-xs text-danger-600">{errors.email.message}</p>}
          </div>
          <div className="flex flex-col gap-1">
            <label htmlFor="password" className="text-sm font-medium text-slate-700">
              New password
            </label>
            <input
              id="password"
              type="password"
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rms-500 focus:outline-none focus:ring-2 focus:ring-rms-100"
              {...register('password')}
            />
            {errors.password && (
              <p className="text-xs text-danger-600">{errors.password.message}</p>
            )}
          </div>
          <div className="flex flex-col gap-1">
            <label htmlFor="password_confirmation" className="text-sm font-medium text-slate-700">
              Confirm password
            </label>
            <input
              id="password_confirmation"
              type="password"
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rms-500 focus:outline-none focus:ring-2 focus:ring-rms-100"
              {...register('password_confirmation')}
            />
            {errors.password_confirmation && (
              <p className="text-xs text-danger-600">{errors.password_confirmation.message}</p>
            )}
          </div>

          {error && (
            <div className="rounded-md border border-danger-100 bg-danger-50 px-3 py-2 text-sm text-danger-600">
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={isSubmitting || !token}
            className="rounded-md bg-rms-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rms-700 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {isSubmitting ? 'Resetting…' : 'Reset password'}
          </button>
          <Link to="/login" className="text-center text-sm text-slate-500 hover:text-slate-700">
            Back to sign in
          </Link>
        </form>
      </Card>
    </main>
  );
}
