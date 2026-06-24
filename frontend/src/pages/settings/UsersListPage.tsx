import { useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '@/stores/authStore';
import { useDeleteUser, useRoles, useSetUserStatus, useUsers } from '@/hooks/useAdmin';
import { Card } from '@/components/common/Card';
import { cn } from '@/utils/cn';
import { formatIsoDate } from '@/utils/date';
import type { UserListFilters } from '@/types/admin';

export default function UsersListPage() {
  const actor = useAuthStore((s) => s.user);
  const [filters, setFilters] = useState<UserListFilters>({ per_page: 25, page: 1 });
  const { data, isLoading, isFetching } = useUsers(filters);
  const { data: roles = [] } = useRoles();
  const setStatus = useSetUserStatus();
  const deleteUser = useDeleteUser();
  const navigate = useNavigate();

  const outletOptions = useMemo(() => {
    const seen = new Map<number, string>();
    actor?.outlets.forEach((o) => seen.set(o.id, `${o.name} (${o.code})`));
    return Array.from(seen, ([id, label]) => ({ id, label }));
  }, [actor]);

  const onSearch = (e: React.ChangeEvent<HTMLInputElement>) =>
    setFilters((f) => ({ ...f, search: e.target.value, page: 1 }));

  return (
    <main className="mx-auto max-w-6xl space-y-6 p-6">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Staff Users</h1>
          <p className="text-sm text-slate-500">
            Manage users, roles, and outlet access for your restaurant.
          </p>
        </div>
        <Link
          to="/settings/users/new"
          className="rounded-md bg-rms-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rms-700"
        >
          + Add User
        </Link>
      </header>

      <Card>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-4">
          <input
            type="search"
            placeholder="Search name, email, phone…"
            value={filters.search ?? ''}
            onChange={onSearch}
            className="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-rms-500 focus:outline-none focus:ring-2 focus:ring-rms-100"
          />
          <select
            value={filters.role ?? ''}
            onChange={(e) =>
              setFilters((f) => ({ ...f, role: e.target.value || undefined, page: 1 }))
            }
            className="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm"
          >
            <option value="">All roles</option>
            {roles.map((r) => (
              <option key={r.id} value={r.name}>
                {r.display_name ?? r.name}
              </option>
            ))}
          </select>
          <select
            value={filters.outlet_id ?? ''}
            onChange={(e) =>
              setFilters((f) => ({
                ...f,
                outlet_id: e.target.value ? Number(e.target.value) : undefined,
                page: 1,
              }))
            }
            className="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm"
          >
            <option value="">All outlets</option>
            {outletOptions.map((o) => (
              <option key={o.id} value={o.id}>
                {o.label}
              </option>
            ))}
          </select>
          <select
            value={filters.is_active === undefined ? '' : String(filters.is_active)}
            onChange={(e) =>
              setFilters((f) => ({
                ...f,
                is_active: e.target.value === '' ? undefined : e.target.value === 'true',
                page: 1,
              }))
            }
            className="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm"
          >
            <option value="">All status</option>
            <option value="true">Active</option>
            <option value="false">Inactive</option>
          </select>
        </div>
      </Card>

      <Card className="p-0">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-slate-100 text-sm">
            <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
              <tr>
                <th className="px-4 py-3">Name</th>
                <th className="px-4 py-3">Email</th>
                <th className="px-4 py-3">Roles</th>
                <th className="px-4 py-3">Outlets</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Last Login</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {isLoading && (
                <tr>
                  <td colSpan={7} className="px-4 py-6 text-center text-slate-500">
                    Loading users…
                  </td>
                </tr>
              )}
              {!isLoading && data?.data.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-6 text-center text-slate-500">
                    No users found.
                  </td>
                </tr>
              )}
              {data?.data.map((u) => (
                <tr key={u.id} className="hover:bg-slate-50">
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2">
                      <div className="flex h-8 w-8 items-center justify-center rounded-full bg-rms-100 text-xs font-semibold text-rms-700">
                        {initials(u.name)}
                      </div>
                      <div>
                        <div className="font-medium text-slate-900">{u.name}</div>
                        <div className="text-xs text-slate-500">{u.phone ?? '—'}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-3 text-slate-700">{u.email}</td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-1">
                      {u.roles.map((r) => (
                        <span
                          key={r}
                          className="rounded-full bg-rms-50 px-2 py-0.5 text-xs font-semibold text-rms-700"
                        >
                          {r}
                        </span>
                      ))}
                    </div>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-1">
                      {u.outlets.map((o) => (
                        <span
                          key={o.id}
                          className="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700"
                        >
                          {o.code}
                        </span>
                      ))}
                    </div>
                  </td>
                  <td className="px-4 py-3">
                    <span
                      className={cn(
                        'rounded-full px-2 py-0.5 text-xs font-semibold',
                        u.is_active
                          ? 'bg-success-50 text-success-600'
                          : 'bg-slate-100 text-slate-600',
                      )}
                    >
                      {u.is_active ? 'Active' : 'Inactive'}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-xs text-slate-500">
                    {u.last_login_at ? formatIsoDate(u.last_login_at) : 'Never'}
                  </td>
                  <td className="px-4 py-3 text-right">
                    <div className="flex justify-end gap-1">
                      <button
                        type="button"
                        className="rounded px-2 py-1 text-xs text-rms-700 hover:bg-rms-50"
                        onClick={() => navigate(`/settings/users/${u.id}/edit`)}
                      >
                        Edit
                      </button>
                      {actor?.id !== u.id && (
                        <button
                          type="button"
                          className="rounded px-2 py-1 text-xs text-slate-700 hover:bg-slate-50"
                          onClick={() => setStatus.mutate({ id: u.id, isActive: !u.is_active })}
                        >
                          {u.is_active ? 'Deactivate' : 'Activate'}
                        </button>
                      )}
                      {actor?.id !== u.id && (
                        <button
                          type="button"
                          className="rounded px-2 py-1 text-xs text-danger-600 hover:bg-danger-50"
                          onClick={() => {
                            if (confirm(`Delete ${u.name}?`)) {
                              deleteUser.mutate(u.id);
                            }
                          }}
                        >
                          Delete
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {data && data.meta.last_page > 1 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
            <span>
              Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} total
            </span>
            <div className="flex gap-2">
              <button
                type="button"
                className="rounded border border-slate-300 px-2 py-1 disabled:opacity-50"
                disabled={data.meta.current_page <= 1}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) - 1 }))}
              >
                Prev
              </button>
              <button
                type="button"
                className="rounded border border-slate-300 px-2 py-1 disabled:opacity-50"
                disabled={data.meta.current_page >= data.meta.last_page}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) + 1 }))}
              >
                Next
              </button>
            </div>
          </div>
        )}
        {isFetching && !isLoading && (
          <div className="border-t border-slate-100 px-4 py-2 text-xs text-slate-400">
            Refreshing…
          </div>
        )}
      </Card>
    </main>
  );
}

function initials(name: string): string {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('');
}
