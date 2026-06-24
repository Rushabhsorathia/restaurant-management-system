import { Navigate } from 'react-router-dom';
import { useHealth } from '@/hooks/useHealth';
import { useAuthStore } from '@/stores/authStore';
import { APP_NAME, APP_VERSION } from '@/constants/app';
import { formatIsoDate } from '@/utils/date';
import { cn } from '@/utils/cn';
import { Card } from '@/components/common/Card';

export default function LandingPage() {
  const token = useAuthStore((s) => s.token);
  const { data, isLoading, isError, refetch, isFetching } = useHealth();

  if (token) {
    return <Navigate to="/dashboard" replace />;
  }

  const apiReachable = !isError && Boolean(data);
  const healthy = apiReachable && data?.status === 'ok';

  return (
    <main className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-rms-50">
      <div className="mx-auto flex max-w-4xl flex-col gap-8 px-6 py-16">
        <header className="flex flex-col gap-2">
          <span className="text-sm font-semibold uppercase tracking-wider text-rms-600">
            Restaurant Management System
          </span>
          <h1 className="text-4xl font-bold text-slate-900 sm:text-5xl">{APP_NAME}</h1>
          <p className="max-w-2xl text-slate-600">
            Frontend SPA scaffolding is online. This landing page confirms that Vite, React,
            Tailwind, and the API health check are wired up correctly. It will be replaced by the
            login screen in RMS-003.
          </p>
        </header>

        <Card className="flex flex-col gap-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs uppercase tracking-wide text-slate-500">Build</p>
              <p className="text-lg font-semibold text-slate-900">v{APP_VERSION}</p>
            </div>
            <StatusPill healthy={healthy} loading={isLoading || isFetching} />
          </div>

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Stat label="Environment" value={data?.environment ?? '—'} />
            <Stat
              label="API Service"
              value={data ? data.service : 'unreachable'}
              tone={apiReachable ? 'ok' : 'err'}
            />
            <Stat label="Time" value={data ? formatIsoDate(data.time) : '—'} />
          </div>

          {data && (
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <ServiceStatus
                name="Database"
                ok={data.checks.database.ok}
                detail={data.checks.database.driver ?? data.checks.database.error}
              />
              <ServiceStatus
                name="Redis"
                ok={data.checks.redis.ok}
                detail={data.checks.redis.response ?? data.checks.redis.error}
              />
            </div>
          )}

          <div className="flex items-center justify-between border-t border-slate-100 pt-4">
            <p className="text-sm text-slate-500">
              Last checked: {data ? formatIsoDate(data.time) : '—'}
            </p>
            <button
              type="button"
              onClick={() => refetch()}
              className="rounded-md bg-rms-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition hover:bg-rms-700"
            >
              Re-check
            </button>
          </div>
        </Card>

        <p className="text-center text-xs text-slate-400">
          Milestone 1 — Ticket RMS-001 · Project Setup &amp; Boilerplate
        </p>
      </div>
    </main>
  );
}

function StatusPill({ healthy, loading }: { healthy: boolean; loading: boolean }) {
  if (loading) {
    return (
      <span
        className={cn(
          'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium',
          'bg-slate-100 text-slate-600',
        )}
      >
        <span className="h-2 w-2 animate-pulse rounded-full bg-slate-400" />
        Checking…
      </span>
    );
  }
  return (
    <span
      className={cn(
        'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium',
        healthy ? 'bg-success-50 text-success-600' : 'bg-danger-50 text-danger-600',
      )}
    >
      <span className={cn('h-2 w-2 rounded-full', healthy ? 'bg-success-500' : 'bg-danger-500')} />
      {healthy ? 'System online' : 'System degraded'}
    </span>
  );
}

function Stat({
  label,
  value,
  tone = 'default',
}: {
  label: string;
  value: string;
  tone?: 'default' | 'ok' | 'err';
}) {
  return (
    <div className="rounded-lg bg-slate-50 p-3">
      <p className="text-xs uppercase tracking-wide text-slate-500">{label}</p>
      <p
        className={cn(
          'truncate text-sm font-semibold',
          tone === 'ok' && 'text-success-600',
          tone === 'err' && 'text-danger-600',
          tone === 'default' && 'text-slate-900',
        )}
        title={value}
      >
        {value}
      </p>
    </div>
  );
}

function ServiceStatus({ name, ok, detail }: { name: string; ok: boolean; detail?: string }) {
  return (
    <div
      className={cn(
        'flex items-center justify-between rounded-lg border p-3',
        ok ? 'border-success-100 bg-success-50' : 'border-danger-100 bg-danger-50',
      )}
    >
      <div>
        <p className="text-sm font-semibold text-slate-900">{name}</p>
        <p className="text-xs text-slate-500">{detail ?? '—'}</p>
      </div>
      <span
        className={cn(
          'rounded-full px-2 py-0.5 text-xs font-semibold',
          ok ? 'bg-success-500 text-white' : 'bg-danger-500 text-white',
        )}
      >
        {ok ? 'OK' : 'FAIL'}
      </span>
    </div>
  );
}
