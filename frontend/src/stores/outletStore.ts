import { create } from 'zustand';
import type { OutletSummary } from '@/types/auth';

export const OUTLET_ID_STORAGE_KEY = 'rms.active.outletId';

type OutletState = {
  outletId: number | null;
  outlets: OutletSummary[];
  hydrated: boolean;
  setOutlets: (outlets: OutletSummary[]) => void;
  setActive: (outletId: number | null) => void;
  activeOutlet: () => OutletSummary | null;
};

function readStored(): number | null {
  try {
    const raw = localStorage.getItem(OUTLET_ID_STORAGE_KEY);
    return raw ? Number(raw) : null;
  } catch {
    return null;
  }
}

function persist(outletId: number | null): void {
  try {
    if (outletId) localStorage.setItem(OUTLET_ID_STORAGE_KEY, String(outletId));
    else localStorage.removeItem(OUTLET_ID_STORAGE_KEY);
  } catch {
    // ignore
  }
}

export const useOutletStore = create<OutletState>((set, get) => ({
  outletId: readStored(),
  outlets: [],
  hydrated: false,

  setOutlets: (outlets) => {
    set((state) => {
      let nextId = state.outletId;
      const valid = outlets.find((o) => o.id === nextId);
      if (!valid && outlets.length > 0) {
        nextId = outlets[0].id;
        persist(nextId);
      }
      return { outlets, outletId: nextId, hydrated: true };
    });
  },

  setActive: (outletId) => {
    persist(outletId);
    set({ outletId });
  },

  activeOutlet: () => {
    const { outlets, outletId } = get();
    return outlets.find((o) => o.id === outletId) ?? null;
  },
}));