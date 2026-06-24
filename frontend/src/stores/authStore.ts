import { create } from 'zustand';
import { AUTH_TOKEN_STORAGE_KEY, AUTH_UNAUTHORIZED_EVENT, setAuthToken } from '@/api/client';
import type { AuthUser } from '@/types/auth';

type AuthState = {
  token: string | null;
  user: AuthUser | null;
  mustChangePassword: boolean;
  setSession: (token: string, user: AuthUser, mustChangePassword: boolean) => void;
  setUser: (user: AuthUser, mustChangePassword?: boolean) => void;
  clear: () => void;
  hasRole: (role: string) => boolean;
  can: (permission: string) => boolean;
};

function readStoredToken(): string | null {
  try {
    return localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);
  } catch {
    return null;
  }
}

function removeStoredToken(): void {
  try {
    localStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
  } catch {
    // ignore
  }
}

export const useAuthStore = create<AuthState>((set, get) => ({
  token: readStoredToken(),
  user: null,
  mustChangePassword: false,

  setSession: (token, user, mustChangePassword) => {
    setAuthToken(token);
    set({ token, user, mustChangePassword });
  },

  setUser: (user, mustChangePassword) => {
    set((state) => ({
      user,
      mustChangePassword: mustChangePassword ?? state.mustChangePassword,
    }));
  },

  clear: () => {
    setAuthToken(null);
    removeStoredToken();
    set({ token: null, user: null, mustChangePassword: false });
  },

  hasRole: (role) => {
    const { user } = get();
    return !!user?.roles.includes(role);
  },

  can: (permission) => {
    const { user } = get();
    return !!user?.permissions.includes(permission);
  },
}));

if (typeof window !== 'undefined') {
  window.addEventListener(AUTH_UNAUTHORIZED_EVENT, () => {
    useAuthStore.getState().clear();
  });
}
