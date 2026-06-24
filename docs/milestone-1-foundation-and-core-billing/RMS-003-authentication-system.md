# RMS-003: Authentication System

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-003 |
| **Type** | Story |
| **Epic** | Foundation |
| **Milestone** | M1 - Foundation & Core Billing |
| **Priority** | P0 - Critical |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | Done |
| **Dependencies** | RMS-001, RMS-002 |

## User Story

As a restaurant staff member, I want to log in to the system securely with my credentials, reset my password if I forget it, and have my session managed via tokens, so that I can access the POS and admin features appropriate to my role.

## Description

This ticket implements the full authentication system using Laravel Sanctum for token-based API authentication. It covers login, logout, the authenticated user profile, password reset (forgot password + reset), token refresh, and first-time password change for seeded admin accounts. The frontend gets login, forgot password, and reset password screens, plus an auth store and route guards.

Authentication must validate credentials, issue a Sanctum token with appropriate abilities, return the user with their roles and assigned outlets, and enforce account active status. Password resets use signed, expiring tokens sent via email. Failed login attempts are rate-limited and logged. The frontend stores the token, attaches it to all API requests via an Axios interceptor, and redirects unauthenticated users to login.

## Acceptance Criteria

- [x] `POST /api/v1/auth/login` accepts email + password, validates, and returns a Sanctum token plus the authenticated user with roles and outlets.
- [x] Invalid credentials return 422 with a generic error (no user enumeration).
- [x] Inactive users (`is_active = 0`) cannot log in and receive a clear message.
- [x] `POST /api/v1/auth/logout` invalidates the current token.
- [x] `GET /api/v1/auth/me` returns the authenticated user with roles, permissions, and assigned outlets.
- [x] `POST /api/v1/auth/forgot-password` sends a signed reset link email (rate-limited).
- [x] `POST /api/v1/auth/reset-password` accepts token + new password and resets it.
- [x] `POST /api/v1/auth/change-password` allows authenticated users to change their password.
- [x] Login is rate-limited (e.g., 5 attempts per minute per IP/email).
- [x] Frontend Login screen validates inputs and displays errors inline.
- [x] Frontend stores token in memory/localStorage and attaches it via Axios interceptor.
- [x] Protected routes redirect to `/login` when unauthenticated; 401 responses clear the token.
- [x] First-login forced password change for the seeded admin account.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Login | /login | Email + password login form |
| Forgot Password | /forgot-password | Request password reset link |
| Reset Password | /reset-password/:token | Set a new password |
| Change Password | /profile#security | Change password while logged in |
| Profile | /profile | View own profile, avatar, roles |

### Screen Details

**Login (/login):** Centered card layout on a branded background. Fields: Email (text input, required, email format), Password (password input, required, min 8). Buttons: "Sign In" (primary), "Forgot password?" (link). Validation shows inline errors below each field. On success, redirect to `/dashboard`. Show a "Stay signed in" checkbox. Display a loading spinner during submission. Error toast/banner on failure. Top-right language selector placeholder.

**Forgot Password (/forgot-password):** Card with Email field and "Send reset link" button. On success, show confirmation message "If the email exists, a reset link has been sent." Link back to login. Rate-limit messaging handled server-side.

**Reset Password (/reset-password/:token):** Card with Email, New Password, Confirm Password fields and a "Reset password" button. Password strength meter on the new password field. Validation: passwords match, min 8 chars, at least one letter and one number. On success, redirect to login with a success banner.

**Profile (/profile):** Shows user avatar (upload), name, email, phone, assigned roles (badges), assigned outlets (badges). A "Security" tab with change-password form (current password, new password, confirm). Read-only fields for last login time.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/auth/login | Authenticate and return token + user |
| POST | /api/v1/auth/logout | Invalidate current token |
| GET | /api/v1/auth/me | Get authenticated user |
| POST | /api/v1/auth/forgot-password | Send reset link email |
| POST | /api/v1/auth/reset-password | Reset password with token |
| POST | /api/v1/auth/change-password | Change password (authenticated) |

## Database Tables

Uses existing tables from RMS-002: `users` (reads/updates password, last_login_at), `personal_access_tokens` (Sanctum token storage). Password reset uses Laravel's `password_resets` table (auto-created). No new business tables.

## Technical Notes

- **Backend:** Use Laravel Sanctum. Create `AuthController` with login/logout/me methods. Use Form Requests (`LoginRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `ChangePasswordRequest`) for validation.
- **Token abilities:** Issue tokens with abilities scoped by role or use a global token + server-side permission checks via spatie/laravel-permission.
- **Reset flow:** Use Laravel's built-in password broker (`Password::sendResetLink`, `Password::reset`). Configure mail driver (SMTP/log in dev).
- **Rate limiting:** Apply `throttle:5,1` middleware on login and forgot-password endpoints.
- **First-login change:** Add a `must_change_password` boolean to users (or store in settings json); if true, login response includes a flag and frontend forces the change-password screen.
- **Frontend:** Create `authStore` (Zustand) holding token + user. Axios request interceptor attaches `Authorization: Bearer <token>`. Response interceptor catches 401 and clears auth + redirects to `/login`. Create `useAuth` hook wrapping the store + TanStack Query for `/me`.
- **Route guard:** A `<RequireAuth>` wrapper component checks authStore and redirects to `/login` if no token.

## Subtasks

1. [x] Configure Sanctum and guard
2. [x] Create AuthController (login, logout, me)
3. [x] Create ForgotPassword + ResetPassword controllers using password broker
4. [x] Create ChangePassword endpoint
5. [x] Add Form Request validation classes
6. [x] Apply rate limiting on auth endpoints
7. [x] Implement first-login forced password change flag
8. [x] Configure mail driver for reset emails
9. [x] Frontend: create authStore (Zustand)
10. [x] Frontend: configure Axios interceptors (token attach, 401 handling)
11. [x] Frontend: build Login screen with validation
12. [x] Frontend: build Forgot Password + Reset Password screens
13. [x] Frontend: build Profile + Change Password screen
14. [x] Frontend: implement RequireAuth route guard
15. [x] Write backend tests for login, logout, reset, change-password

## Testing Criteria

- [x] Valid credentials return 200 with token and user payload.
- [x] Invalid credentials return 422 without revealing which field is wrong.
- [x] Inactive user login returns 403 with account-inactive message.
- [x] Logout invalidates the token; subsequent /me returns 401.
- [x] Forgot-password sends email (verified via log/SMTP).
- [x] Reset-password with valid token succeeds; with invalid/expired token fails.
- [x] Change-password requires current password and enforces complexity.
- [x] Rate limiting blocks after 5 failed attempts per minute.
- [x] Frontend login flow end-to-end: enter creds, get token, redirect to dashboard. (AuthTest: `Login with valid credentials returns token and user`.)
- [x] Protected route redirects to login when token absent/expired. (Frontend: RequireAuth redirects via token check.)
- [x] First-login flow forces password change before dashboard access. (Backend middleware EnsureMustChangePassword + frontend RequireAuth redirect to /profile.)
