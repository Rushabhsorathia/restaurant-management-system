import { useEffect, useMemo, useState } from 'react';
import {
  usePermissionsGrouped,
  useRolePermissions,
  useRoles,
  useUpdateRolePermissions,
} from '@/hooks/useAdmin';
import { Card } from '@/components/common/Card';
import { cn } from '@/utils/cn';

export default function RolesPage() {
  const { data: roles = [] } = useRoles();
  const { data: modules = [] } = usePermissionsGrouped();
  const [selectedRoleId, setSelectedRoleId] = useState<number | null>(null);
  const { data: roleData, isLoading } = useRolePermissions(selectedRoleId);
  const update = useUpdateRolePermissions(selectedRoleId ?? 0);
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [dirty, setDirty] = useState(false);

  useEffect(() => {
    if (!selectedRoleId && roles.length > 0) {
      setSelectedRoleId(roles[0].id);
    }
  }, [roles, selectedRoleId]);

  useEffect(() => {
    if (roleData) {
      setSelected(new Set(roleData.permissions.map((p) => p.name)));
      setDirty(false);
    }
  }, [roleData]);

  const groupedPermissions = useMemo(() => modules, [modules]);

  const toggle = (name: string) => {
    const next = new Set(selected);
    if (next.has(name)) next.delete(name);
    else next.add(name);
    setSelected(next);
    setDirty(true);
  };

  const selectAllInModule = (moduleName: string, on: boolean) => {
    const next = new Set(selected);
    groupedPermissions
      .find((m) => m.module === moduleName)
      ?.permissions.forEach((p) => {
        if (on) next.add(p.name);
        else next.delete(p.name);
      });
    setSelected(next);
    setDirty(true);
  };

  const onSave = async () => {
    await update.mutateAsync(Array.from(selected));
    setDirty(false);
  };

  const isAdminRole = roleData?.role.name === 'admin';
  const totalSelected = selected.size;
  const totalAll = groupedPermissions.reduce((sum, m) => sum + m.permissions.length, 0);

  return (
    <main className="mx-auto max-w-6xl space-y-6 p-6">
      <header>
        <h1 className="text-2xl font-bold text-slate-900">Roles &amp; Permissions</h1>
        <p className="text-sm text-slate-500">
          Choose a role to view and edit the permissions it grants.
        </p>
      </header>

      <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
        <Card className="md:col-span-1">
          <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Roles
          </h2>
          <ul className="space-y-1">
            {roles.map((r) => (
              <li key={r.id}>
                <button
                  type="button"
                  onClick={() => setSelectedRoleId(r.id)}
                  className={cn(
                    'flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm transition',
                    selectedRoleId === r.id
                      ? 'bg-rms-50 text-rms-700'
                      : 'text-slate-700 hover:bg-slate-50',
                  )}
                >
                  <span className="font-medium">{r.display_name ?? r.name}</span>
                  <span className="text-xs text-slate-500">{r.permissions_count ?? 0}</span>
                </button>
              </li>
            ))}
          </ul>
        </Card>

        <Card className="md:col-span-2">
          <div className="mb-4 flex items-center justify-between">
            <div>
              <h2 className="text-lg font-semibold text-slate-900">
                {roleData?.role.display_name ?? roleData?.role.name ?? 'Select a role'}
              </h2>
              <p className="text-xs text-slate-500">
                {totalSelected} / {totalAll} permissions selected
              </p>
            </div>
            <button
              type="button"
              onClick={onSave}
              disabled={!dirty || update.isPending || isAdminRole}
              className="rounded-md bg-rms-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-rms-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {update.isPending ? 'Saving…' : 'Save changes'}
            </button>
          </div>

          {isAdminRole && (
            <div className="mb-4 rounded-md border border-warn-100 bg-warn-50 px-3 py-2 text-sm text-warn-600">
              The admin role is system-managed. Use HQ Admin to override.
            </div>
          )}

          {isLoading && (
            <div className="py-8 text-center text-sm text-slate-500">Loading permissions…</div>
          )}

          <div className="space-y-6">
            {groupedPermissions.map((m) => {
              const allOn = m.permissions.every((p) => selected.has(p.name));
              return (
                <div key={m.module ?? 'general'}>
                  <div className="mb-2 flex items-center justify-between">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-slate-500">
                      {m.module ?? 'General'}
                    </h3>
                    <button
                      type="button"
                      onClick={() => m.module && selectAllInModule(m.module, !allOn)}
                      disabled={isAdminRole}
                      className="text-xs font-medium text-rms-600 hover:text-rms-700 disabled:text-slate-400"
                    >
                      {allOn ? 'Uncheck all' : 'Check all'}
                    </button>
                  </div>
                  <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    {m.permissions.map((p) => (
                      <label
                        key={p.id}
                        className="flex cursor-pointer items-start gap-2 rounded-md border border-slate-200 p-2 hover:border-rms-300"
                      >
                        <input
                          type="checkbox"
                          className="mt-1 rounded border-slate-300"
                          checked={selected.has(p.name)}
                          disabled={isAdminRole}
                          onChange={() => toggle(p.name)}
                        />
                        <div>
                          <div className="text-sm font-medium text-slate-800">{p.name}</div>
                          <div className="text-xs text-slate-500">{p.display_name ?? p.name}</div>
                        </div>
                      </label>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        </Card>
      </div>
    </main>
  );
}
