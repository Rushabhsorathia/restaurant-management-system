# RMS-049: Multi-Language Support (i18n)

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-049 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | High |
| **Story Points** | 13 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-001 (Auth & Multi-Tenancy), RMS-006 (Menu Management), RMS-014 (Billing & Payment) |

## User Story
As a restaurant owner operating in a multilingual market like India, I want the entire POS, KDS, customer-facing kiosk, and admin panel to support 18+ regional languages (Hindi, Gujarati, Tamil, Telugu, Bengali, Marathi, Kannada, Malayalam, Punjabi, Urdu, Arabic RTL, English) with proper currency/date formatting, so that my staff and customers can interact in their preferred language without friction.

## Description
This story delivers full internationalization (i18n) and localization (l10n) across every user-facing surface of the Restaurant Management System. India alone has 22 scheduled languages with 100+ million speakers each, and a Tier-2 restaurant chain in Mumbai might employ Marathi-speaking waiters, serve Gujarati-speaking customers, and stock Hindi-script menus. The current single-language UI forces context-switching that costs time and creates errors at the point of sale.

The implementation uses `react-i18next` on the frontend (lazy-loaded namespace JSON, browser language detection with manual override) and Laravel's `trans()` helper backed by JSON catalogues stored in `lang/{locale}.json` and per-module `lang/{locale}/module.php` files. A dedicated `translations` table supports runtime translatable content (menu item names, descriptions, allergen labels) that admins can edit through a Translation Manager UI without redeploying.

Locale handling covers three concerns: text translation, number/currency formatting (₹1,23,456.00 in en-IN vs ₹123.456,00 in de-DE), and date/time formatting (DD/MM/YYYY in India, MM/DD/YYYY in US). Right-to-Left (RTL) support is implemented for Arabic and Urdu via CSS logical properties and a `dir="rtl"` toggle that mirrors layouts for receipts, KDS, and admin tables. Each `restaurants` row gains a `default_locale` and a `supported_locales` JSON array; each `users` row gets a `locale` preference; each `menu_items` row gets a `translations_json` column for per-locale name/description.

The Translation Manager UI lets admins view a side-by-side key-by-key editor (source locale on left, target locale on right) with a missing-translation indicator. Bulk auto-translate via a configurable provider (Google Cloud Translation / Azure Translator / local LibreTranslate) is gated by feature flag and writes to the catalogue only after admin approval. The system supports pluralization rules (ICU MessageFormat) and interpolation (`{{table}}` for order numbers). All API responses carry the resolved locale, and webhook payloads to aggregators include locale-aware menu names.

## Acceptance Criteria
- [ ] Frontend supports 18+ locales: en, hi, gu, ta, te, bn, mr, kn, ml, pa, ur, or, as, ar, zh, fr, de, es with fallback chain en -> default locale.
- [ ] `react-i18next` lazy-loads namespace JSON per route to keep initial bundle under 250KB gzipped.
- [ ] Backend uses Laravel `trans()` with JSON catalogues in `lang/{locale}.json`; missing keys fall back gracefully with a `__` wrapper and log a warning.
- [ ] User can switch language from any screen; preference persists in localStorage and propagates to backend via `Accept-Language` header + user profile.
- [ ] Per-outlet default locale is configurable; menu item names/descriptions support per-locale overrides via `menu_item_translations` table.
- [ ] Currency formatting follows locale: ₹1,23,456.00 (en-IN) vs $1,234.56 (en-US) using `Intl.NumberFormat` on frontend and `NumberFormatter` on backend.
- [ ] Date formatting follows locale: DD/MM/YYYY (en-IN) vs MM/DD/YYYY (en-US); timezone respects outlet's `Asia/Kolkata`-style setting.
- [ ] RTL support for Arabic (ar) and Urdu (ur) mirrors entire layout via CSS logical properties and `dir="rtl"`; verified on POS, KDS, and admin.
- [ ] Translation Manager UI shows side-by-side editor with missing-translation badges, search/filter, and bulk import/export (XLIFF 1.2, JSON, CSV).
- [ ] Auto-translate via configurable provider fills missing keys; admin must approve before publish.
- [ ] Plurals use ICU MessageFormat: `{count, plural, one {# item} other {# items}}`.
- [ ] All API responses include `X-Locale` header; aggregators receive locale-specific menu names in webhook payloads.
- [ ] Web print receipts and thermal printer output respect selected locale for item names.
- [ ] Translation coverage report shows % translated per locale per module; CI gate at 90% for default release locales.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Language Switcher | Global (top bar) | Quick locale toggle available on every screen |
| Translation Manager | /admin/settings/translations | Side-by-side key editor for all locales |
| Menu Item Translation | /menu/items/{id}/translations | Per-locale name/description/allergen editor |
| Outlet Localization | /settings/outlets/{id}/localization | Default locale, supported locales, timezone, currency |
| User Language Preference | /profile/preferences | Personal locale selection |

### Screen Details

**Language Switcher (Global Component)**
- Position: Top-right of every authenticated layout, globe icon with current locale code (e.g., "EN").
- Click: Dropdown shows searchable list of all supported locales with native name + English name + flag emoji.
- Selection: Instantly applies locale, persists to localStorage, syncs to backend via PATCH `/users/me/locale`.
- RTL locales (ar, ur) trigger a full layout flip animation.

**Translation Manager (/admin/settings/translations)**
- Layout: Three-column grid -- left sidebar with module tree, middle column source-locale editor, right column target-locale editor.
- Module tree: Expandable nodes: Common, Auth, POS, KDS, Inventory, CRM, Billing, Reports, Hardware, Errors.
- Source locale: en (read-only, gold standard); target locale: dropdown to switch (hi, gu, ta, etc.).
- Per-key editor: Key path, source value (editable for en), target value (textarea with char count), status badge (translated/missing/stale/auto-approved).
- Search bar: Filters keys by path or value substring.
- Bulk actions: "Auto-translate missing", "Export XLIFF", "Import XLIFF", "Reset to source", "Mark approved".
- Top bar stats: "78% translated (142/182 keys), 12 stale, 0 missing".
- Buttons per row: "Save", "Revert", "Open context" (shows where the key is used in the app).

**Menu Item Translation (/menu/items/{id}/translations)**
- Tabs: One per supported locale plus "Source (en)".
- Fields per locale: Display Name, Short Description, Long Description, Allergen Labels, Spice Level Label, Veg/Non-Veg Label.
- Side panel preview: Shows the item as it would appear on a menu board / kiosk in that locale.
- Buttons: "Save All", "Auto-translate from source", "Reset".

**Outlet Localization (/settings/outlets/{id}/localization)**
- Fields: Default Locale (dropdown), Supported Locales (multiselect with search), Timezone (IANA list), Currency Code (default INR), Currency Symbol Position, Date Format, Time Format, First Day of Week.
- Live preview: Shows a sample bill rendered in the selected locale.
- Buttons: "Save", "Reset to restaurant default".

**User Language Preference (/profile/preferences)**
- Fields: Language (dropdown with native names), Date Format (override), Number Format (override), Timezone (override).
- Save persists immediately and reloads UI.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/locales | List supported locales with metadata |
| GET | /api/v1/translations/{locale}/{namespace} | Fetch translation catalogue for locale |
| POST | /api/v1/translations/{locale} | Update translation key(s) |
| POST | /api/v1/translations/auto-translate | Run auto-translation for missing keys |
| POST | /api/v1/translations/import | Import XLIFF / JSON catalogue |
| GET | /api/v1/translations/export/{locale} | Export catalogue |
| GET | /api/v1/translations/coverage | Per-locale coverage report |
| GET | /api/v1/menu-items/{id}/translations | Get menu item translations |
| PUT | /api/v1/menu-items/{id}/translations | Update menu item translations |
| GET | /api/v1/outlets/{id}/localization | Get outlet locale config |
| PUT | /api/v1/outlets/{id}/localization | Update outlet locale config |
| PATCH | /api/v1/users/me/locale | Update personal language preference |
| GET | /api/v1/users/me/locale | Get personal preference |
| POST | /api/v1/translations/bulk-approve | Bulk approve auto-translated keys |
| GET | /api/v1/translations/stale | List translations behind source version |

## Database Tables

**translations**
- id (bigIncrements, PK)
- locale (string(10), indexed) -- e.g., 'hi', 'en-IN'
- namespace (string, indexed) -- e.g., 'pos', 'kds', 'common'
- key (string, indexed)
- value (text, nullable)
- source_value (text) -- snapshot of en for staleness check
- source_version (unsignedInteger) -- increments on en change
- is_auto_generated (boolean, default false)
- approved_at (timestamp, nullable)
- approved_by (foreignId, users, nullable)
- created_at, updated_at
- unique([locale, namespace, key])
- index([namespace, locale])

**menu_item_translations**
- id (bigIncrements, PK)
- menu_item_id (foreignId, menu_items)
- locale (string(10), indexed)
- name (string)
- short_description (string, nullable)
- long_description (text, nullable)
- allergen_labels_json (json)
- spice_label (string, nullable)
- dietary_label (string, nullable)
- created_at, updated_at
- unique([menu_item_id, locale])

**outlet_localizations**
- id (bigIncrements, PK)
- outlet_id (foreignId, outlets, unique)
- default_locale (string(10))
- supported_locales_json (json) -- array
- timezone (string, default 'Asia/Kolkata')
- currency_code (string(3), default 'INR')
- currency_symbol_position (enum: before, after, default before)
- date_format (enum: DMY, MDY, YMD, default DMY)
- time_format (enum: 'H:i', 'h:i A', default 'h:i A')
- first_day_of_week (enum: sunday, monday, default monday)
- timestamps

**translation_audit_log**
- id (bigIncrements, PK)
- locale (string)
- key (string)
- old_value (text, nullable)
- new_value (text)
- changed_by (foreignId, users)
- source (enum: manual, auto, import)
- created_at

## Technical Notes

**Laravel Backend:**
- Locale middleware (`SetLocale`) reads `Accept-Language` header, validates against `config('app.supported_locales')`, falls back to outlet default, then `config('app.fallback_locale')`. Stored on request via `app()->setLocale()`.
- Translation loader extends `Illuminate\Translation\LoaderInterface` to read from `translations` table with Redis cache (`Cache::remember('trans.{locale}.{ns}', 3600, ...)`); invalidates on `saved` event.
- Helper: `t('pos.checkout.title')` resolves to DB value if present, else file catalogue, else `__key__`.
- Auto-translate job `AutoTranslateJob` calls configured provider (interface `TranslationProvider`) with retry + rate limiting; output goes to `is_auto_generated=true` rows pending approval.
- API resources emit `X-Locale` header; menu item resources include `name_i18n` map of all requested locales.
- NumberFormatter via `NumberFormatter::CURRENCY` with ICU currency codes; date formatting via `IntlDateFormatter`.
- Outbound webhook payloads to Swiggy/Zomato localized per outlet's default locale.

**React Frontend:**
- `i18next` configured with `react-i18next` v15; backend connector fetches `translations/{locale}/{namespace}` on first use, caches in `localStorage` with 24h TTL.
- Suspense boundaries wrap translated sections to avoid blocking render.
- `useTranslation(ns)` hook used per route; namespace per page reduces initial payload.
- `Intl.NumberFormat` and `Intl.DateTimeFormat` wrapped in `useFormat()` hook for currency, date, time, relative time.
- RTL handled via Tailwind logical properties (`ms-2`, `me-2`, `ps-4`, `pe-4`) and `dir="rtl"` on `<html>`; tested on POS, KDS, kiosk.
- Translation Manager built with React + TipTap for rich-text keys; XLIFF parser via `xliff` npm package.
- Language switcher as global portal component.

## Subtasks
1. [ ] Configure `config/app.php` supported_locales list (18+) and fallback chain
2. [ ] Build `translations`, `menu_item_translations`, `outlet_localizations`, `translation_audit_log` migrations and models
3. [ ] Implement `SetLocale` middleware with Accept-Language + user + outlet precedence
4. [ ] Build translation loader that reads from DB with Redis cache and file fallback
5. [ ] Implement `TranslationProvider` interface with Google/Azure/LibreTranslate adapters
6. [ ] Build `AutoTranslateJob` and `ApproveTranslationsJob` queue workers
7. [ ] Build `MenuItemTranslationController` with bulk update
8. [ ] Build `OutletLocalizationController` and `UserLocaleController`
9. [ ] Implement ICU MessageFormat plural + interpolation in `trans()` helper
10. [ ] Add `Intl.NumberFormat` and `Intl.DateTimeFormat` React hooks
11. [ ] Build React Translation Manager with side-by-side editor
12. [ ] Build React Menu Item Translation editor with kiosk preview
13. [ ] Build global Language Switcher with RTL handling
14. [ ] Add RTL CSS logical properties to Tailwind config; test on POS, KDS, kiosk
15. [ ] Implement XLIFF 1.2 import/export
16. [ ] Add translation coverage report endpoint and CI gate
17. [ ] Localize receipt template (thermal + A4) per locale
18. [ ] Write tests for locale resolution, fallback, pluralization, currency formatting, RTL layout

## Testing Criteria
- [ ] Switching locale mid-session updates every visible string without page reload
- [ ] Missing translation key falls back to en and logs warning
- [ ] Currency ₹1,23,456.00 renders correctly in en-IN; $1,234.56 in en-US
- [ ] Date 25/06/2026 renders in en-IN; 06/25/2026 in en-US
- [ ] Arabic locale flips entire layout (sidebar, tables, icons) without overflow
- [ ] ICU plural resolves: 1 item, 5 items, 0 items correctly
- [ ] Auto-translate provider fills missing keys and marks them auto-generated until approved
- [ ] XLIFF import updates existing keys and inserts new ones; conflicts reported
- [ ] Menu item locale override takes precedence over source name on kiosk and receipt
- [ ] Per-user locale preference overrides outlet default after login
- [ ] Translation coverage report accurately reflects DB state
- [ ] Webhook payload to Swiggy contains locale-specific item names
