import type { ReactNode } from 'react';
import { cn } from '@/utils/cn';

type CardProps = {
  children: ReactNode;
  className?: string;
};

export function Card({ children, className }: CardProps) {
  return (
    <div className={cn('rounded-2xl bg-white p-6 shadow-card ring-1 ring-slate-100', className)}>
      {children}
    </div>
  );
}
