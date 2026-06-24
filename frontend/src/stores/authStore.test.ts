/**
 * @vitest-environment jsdom
 */
import { describe, expect, it, beforeEach } from 'vitest';
import { useAuthStore } from './authStore';

const fakeUser = {
  id: 1,
  restaurant_id: 1,
  name: 'Test User',
  email: 't@x.io',
  phone: null,
  avatar_url: null,
  is_active: true,
  must_change_password: false,
  last_login_at: null,
  roles: ['admin', 'cashier'],
  permissions: ['menu.view', 'order.create'],
  outlets: [],
  created_at: null,
};

describe('authStore', () => {
  beforeEach(() => {
    useAuthStore.setState({ token: null, user: null, mustChangePassword: false });
  });

  it('setSession stores token + user + mustChangePassword', () => {
    useAuthStore.getState().setSession('tok-123', fakeUser, true);
    const state = useAuthStore.getState();
    expect(state.token).toBe('tok-123');
    expect(state.user?.email).toBe('t@x.io');
    expect(state.mustChangePassword).toBe(true);
  });

  it('clear removes token + user', () => {
    useAuthStore.getState().setSession('tok-123', fakeUser, true);
    useAuthStore.getState().clear();
    const state = useAuthStore.getState();
    expect(state.token).toBeNull();
    expect(state.user).toBeNull();
    expect(state.mustChangePassword).toBe(false);
  });

  it('hasRole checks roles array', () => {
    useAuthStore.getState().setSession('tok', fakeUser, false);
    expect(useAuthStore.getState().hasRole('admin')).toBe(true);
    expect(useAuthStore.getState().hasRole('hq_admin')).toBe(false);
  });

  it('can checks permissions array', () => {
    useAuthStore.getState().setSession('tok', fakeUser, false);
    expect(useAuthStore.getState().can('menu.view')).toBe(true);
    expect(useAuthStore.getState().can('bill.settle')).toBe(false);
  });
});
