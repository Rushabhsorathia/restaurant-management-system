# RMS — Frontend (React 18 + Vite + TypeScript)

The Restaurant Management System SPA. React 18, Vite 5, TypeScript (strict), Tailwind CSS 3, TanStack Query, Zustand, React Router v6, React Hook Form + Zod, Axios, Recharts, date-fns, clsx.

## Requirements

- Node.js 20+
- npm 10+

## Setup

```bash
npm install
cp .env.example .env   # optional, see "Environment variables" below
npm run dev            # http://localhost:5173
```

The dev server proxies `/api/*` to `http://localhost:8000` (the Laravel backend) by default. To point at a different API, set `VITE_API_URL` in `.env`.

## Environment variables

| Variable | Default | Description |
|----------|---------|-------------|
| `VITE_API_URL` | `/api` | Base URL for the backend API. |
| `VITE_APP_NAME` | `RMS` | Display name shown on the landing page. |
| `VITE_APP_VERSION` | `0.1.0` | Build version string. |

## Scripts

| Script | Description |
|--------|-------------|
| `npm run dev` | Start the Vite dev server on port 5173. |
| `npm run build` | Type-check and produce a production build in `dist/`. |
| `npm run preview` | Preview the production build locally. |
| `npm test` | Run Vitest once (CI mode). |
| `npm run test:watch` | Vitest in watch mode. |
| `npm run lint` | ESLint over the source tree. |
| `npm run lint:fix` | ESLint with `--fix`. |
| `npm run format` | Prettier write across `src/`. |
| `npm run format:check` | Prettier check (used in CI). |
| `npx tsc -b` | TypeScript type check (no emit). |

## Project layout

Matches [docs/plans/architecture.md](../docs/plans/architecture.md). Feature folders are created up-front so subsequent tickets (RMS-003 auth, RMS-004 users, RMS-006 menu, …) can drop their files into a stable structure.

```
frontend/
|-- src/
|   |-- api/                  # Axios instance + endpoint modules
|   |-- assets/               # Static assets
|   |-- components/
|   |   |-- common/           # Button, Modal, Table, Input, Card
|   |   |-- layout/           # Sidebar, Topbar, OutletSwitcher
|   |   |-- pos/              # POS-specific widgets
|   |   |-- charts/
|   |   |-- forms/
|   |-- constants/            # App-wide constants (app name, API base URL)
|   |-- hooks/                # useAuth, useOrders, useMenu, useWebSocket, ...
|   |-- pages/                # Feature pages (auth, dashboard, menu, ...)
|   |-- routes/               # React Router v6 config + guards
|   |-- services/             # Client-side business helpers
|   |-- stores/               # Zustand stores (auth, cart, outlet, ui)
|   |-- types/                # Shared TypeScript types
|   |-- utils/                # Pure helpers (date, cn, ...)
|   |-- App.tsx
|   |-- main.tsx
|   |-- index.css             # Tailwind entry
|-- index.html
|-- vite.config.ts            # Vite + Vitest config
|-- tailwind.config.js        # Tailwind theme (rms-* design tokens)
|-- postcss.config.js
|-- .eslintrc.cjs             # ESLint (TS + React + Prettier compat)
|-- .prettierrc.json
```

## State management

- **Server state** — TanStack Query (`@tanstack/react-query`). Hooks like `useHealth` wrap API calls.
- **Client state** — Zustand stores under `src/stores/`. `uiStore` is the only one currently populated; `authStore`, `cartStore`, and `outletStore` will be added with RMS-003 / RMS-004 / RMS-006.
- **Forms** — React Hook Form + Zod resolvers (per-ticket wiring).

## License

MIT
