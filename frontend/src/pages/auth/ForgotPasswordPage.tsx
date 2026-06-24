import { useState } from 'react';
import { Link } from 'react-router-dom';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForgotPassword } from '@/hooks/useAuth';
import { Card } from '@/components/common/Card';

const schema = z.object({
  email: z.string().min(1, 'Email is required').email('Enter a valid email address'),
});

type ForgotForm = z.infer<typeof schema>;

export default function ForgotPasswordPage() {
  const forgot = useForgotPassword();
  const [submitted, setSubmitted] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<ForgotForm>({
    resolver: zodResolver(schema),
    defaultValues: { email: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    try {
      await forgot.mutateAsync(values.email);
    } finally {
      setSubmitted(true);
    }
  });

  return (
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 via-white to-rms-50 p-6">
      <Card className="w-full max-w-md">
        <h1 className="mb-1 text-2xl font-bold text-slate-900">Forgot password</h1>
        <p className="mb-6 text-sm text-slate-500">
          Enter your email and we&apos;ll send a reset link if your account exists.
        </p>

        {submitted ? (
          <div className="flex flex-col gap-4">
            <div className="rounded-md border border-success-100 bg-success-50 px-3 py-2 text-sm text-success-600">
              If the email exists, a reset link has been sent.
            </div>
            <Link
              to="/login"
              className="text-center text-sm font-medium text-rms-600 hover:text-rms-700"
            >
              Back to sign in
            </Link>
          </div>
        ) : (
          <form className="flex flex-col gap-4" onSubmit={onSubmit} noValidate>
            <div className="flex flex-col gap-1">
              <label htmlFor="email" className="text-sm font-medium text-slate-700">
                Email
              </label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rms-500 focus:outline-none focus:ring-2 focus:ring-rms-100"
                {...register('email')}
              />
              {errors.email && <p className="text-xs text-danger-600">{errors.email.message}</p>}
            </div>
            <button
              type="submit"
              disabled={isSubmitting}
              className="rounded-md bg-rms-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rms-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {isSubmitting ? 'Sending…' : 'Send reset link'}
            </button>
            <Link to="/login" className="text-center text-sm text-slate-500 hover:text-slate-700">
              Back to sign in
            </Link>
          </form>
        )}
      </Card>
    </main>
  );
}
