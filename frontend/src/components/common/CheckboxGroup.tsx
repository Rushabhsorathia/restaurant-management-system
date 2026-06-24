import type { ReactNode } from 'react';

type CheckboxGroupProps<T extends string | number> = {
  options: { value: T; label: string; description?: string }[];
  selected: T[];
  onChange: (next: T[]) => void;
  columns?: number;
};

export function CheckboxGroup<T extends string | number>({
  options,
  selected,
  onChange,
  columns = 2,
}: CheckboxGroupProps<T>) {
  const toggle = (value: T) => {
    if (selected.includes(value)) {
      onChange(selected.filter((v) => v !== value));
    } else {
      onChange([...selected, value]);
    }
  };

  return (
    <div
      className="grid gap-2"
      style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
    >
      {options.map((opt) => (
        <label
          key={String(opt.value)}
          className="flex cursor-pointer items-start gap-2 rounded-md border border-slate-200 p-2 hover:border-rms-300"
        >
          <input
            type="checkbox"
            className="mt-1 rounded border-slate-300"
            checked={selected.includes(opt.value)}
            onChange={() => toggle(opt.value)}
          />
          <div>
            <div className="text-sm font-medium text-slate-800">{opt.label}</div>
            {opt.description && <div className="text-xs text-slate-500">{opt.description}</div>}
          </div>
        </label>
      ))}
    </div>
  );
}

type CheckboxGroupChildrenProps = {
  children: ReactNode;
};

export function CheckboxGroupRow({ children }: CheckboxGroupChildrenProps) {
  return <div className="flex flex-col gap-1">{children}</div>;
}
