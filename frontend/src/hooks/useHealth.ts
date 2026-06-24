import { useQuery } from '@tanstack/react-query';
import { fetchHealth, type HealthCheck } from '@/api/health';

export const healthQueryKey = ['health'] as const;

export function useHealth() {
  return useQuery<HealthCheck>({
    queryKey: healthQueryKey,
    queryFn: fetchHealth,
    refetchOnWindowFocus: false,
    staleTime: 30_000,
  });
}
