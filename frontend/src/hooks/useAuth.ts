import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  changePassword,
  fetchMe,
  forgotPassword,
  login as loginApi,
  logout as logoutApi,
  resetPassword as resetPasswordApi,
  type LoginPayload,
} from '@/api/auth';
import { useAuthStore } from '@/stores/authStore';

export const meQueryKey = ['auth', 'me'] as const;

export function useMe() {
  const token = useAuthStore((s) => s.token);
  return useQuery({
    queryKey: meQueryKey,
    queryFn: fetchMe,
    enabled: !!token,
    retry: false,
    staleTime: 60_000,
  });
}

type LoginVars = LoginPayload;
type LoginResult = Awaited<ReturnType<typeof loginApi>>;

export function useLogin() {
  const setSession = useAuthStore((s) => s.setSession);
  const queryClient = useQueryClient();
  return useMutation<LoginResult, Error, LoginVars>({
    mutationFn: loginApi,
    onSuccess: (data) => {
      setSession(data.token, data.user, data.must_change_password);
      queryClient.setQueryData(meQueryKey, {
        success: true,
        must_change_password: data.must_change_password,
        user: data.user,
      });
    },
  });
}

export function useLogout() {
  const clear = useAuthStore((s) => s.clear);
  const queryClient = useQueryClient();
  return useMutation<void, Error, void>({
    mutationFn: async () => {
      try {
        await logoutApi();
      } finally {
        clear();
        queryClient.removeQueries({ queryKey: meQueryKey });
      }
    },
  });
}

export function useChangePassword() {
  const setUser = useAuthStore((s) => s.setUser);
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: changePassword,
    onSuccess: async () => {
      const me = await queryClient.fetchQuery({ queryKey: meQueryKey, queryFn: fetchMe });
      setUser(me.user, me.must_change_password);
    },
  });
}

export function useForgotPassword() {
  return useMutation({
    mutationFn: (email: string) => forgotPassword(email),
  });
}

export function useResetPassword() {
  return useMutation({
    mutationFn: resetPasswordApi,
  });
}
