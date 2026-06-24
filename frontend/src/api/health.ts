import api from './client';

export type HealthCheck = {
  status: 'ok' | 'degraded';
  service: string;
  version: string;
  environment: string;
  time: string;
  checks: {
    database: { ok: boolean; driver?: string; error?: string };
    redis: { ok: boolean; response?: string; error?: string };
  };
};

export async function fetchHealth(): Promise<HealthCheck> {
  const { data } = await api.get<HealthCheck>('/health');
  return data;
}
