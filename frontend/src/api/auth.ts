import api from './client';
import type { LoginResponse, MeResponse } from '@/types/auth';

export type LoginPayload = {
  email: string;
  password: string;
  device_name?: string;
};

export async function login(payload: LoginPayload): Promise<LoginResponse> {
  const { data } = await api.post<LoginResponse>('/v1/auth/login', payload);
  return data;
}

export async function logout(): Promise<void> {
  await api.post('/v1/auth/logout');
}

export async function fetchMe(): Promise<MeResponse> {
  const { data } = await api.get<MeResponse>('/v1/auth/me');
  return data;
}

export async function changePassword(payload: {
  current_password: string;
  password: string;
  password_confirmation: string;
}): Promise<{ success: boolean; message: string; must_change_password: boolean }> {
  const { data } = await api.post('/v1/auth/change-password', payload);
  return data;
}

export async function forgotPassword(
  email: string,
): Promise<{ success: boolean; message: string }> {
  const { data } = await api.post('/v1/auth/forgot-password', { email });
  return data;
}

export async function resetPassword(payload: {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}): Promise<{ success: boolean; message: string }> {
  const { data } = await api.post('/v1/auth/reset-password', payload);
  return data;
}
