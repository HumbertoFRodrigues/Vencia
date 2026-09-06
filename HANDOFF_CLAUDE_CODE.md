# Handoff: Vencia (gestão de assinaturas e serviços recorrentes)

## Overview
Single-admin web app for managing clients, recurring services (domains, hosting, corporate email, maintenance, AI-tool access such as ChatGPT/Claude), payments, due dates, reminders and history. Market: Mozambique — currency **MZN**, UI language **European Portuguese (pt-PT)**, payment methods **M-Pesa, e-Mola, bank transfer, cash**.

Core model: **cliente → vários serviços → pagamentos → vencimentos → lembretes → histórico**.
The product's whole job is answering, in seconds: who paid, how much came in this month, who is overdue, what expires next, which access must be suspended, what the recurring revenue is.

Not an ERP, not accounting software. Simplicity, automation and clear due dates come first.

## About the Design Files
Everything in this bundle is a **design reference written in HTML/JSX** — prototypes that show the intended look and behaviour. **Do not ship them as production code.**
The task is to **recreate these designs in the target codebase's environment** (React, Vue, Svelte, Laravel/Blade, Django templates, SwiftUI…) using its established patterns, router, forms and data layer. If no codebase exists yet, pick the framework you judge best for the project and implement the designs there.
The token CSS (`design_system/styles.css` + `design_system/tokens/*.css`) is the one part that **can** be adopted verbatim — it is plain CSS custom properties with no dependencies.

## Fidelity
**High-fidelity.** Final colours, typography, spacing, radii, shadows, motion, dark theme and interaction states are all decided. Recreate the UI faithfully using the codebase's own component library where one exists; when it doesn't, port the components in `design_system/components/` (self-contained React, no npm dependencies beyond React itself).

Two things are deliberately fake and must be built for real: **persistence** (all data comes from `prototype/data.js`) and **email sending / the daily job**.

---

## Screens / Views

All screens share the shell: fixed **236px** sidebar, sticky **60px** top bar, scrolling content column with **24px** gutter, content capped at **1320px**. Detail pages use a two-column grid: `minmax(0,1.9fr)` main + `minmax(280px,1fr)` rail, **12px** gap. Metric tiles: `repeat(auto-fit,minmax(178px,1fr))`, 12px gap.

### 1. Dashboard (`prototype/DashboardScreen.jsx`)
- **Purpose:** answer "what needs me today" without a click.
- **Layout:** page header (eyebrow = month, h1 "Dashboard", actions right) → 6 metric tiles → two-column grid: próximos-vencimentos table (left) / alert banner + alert list + MRR-ARR card (right).
- **Metric tiles:** Receita deste mês · Receita esperada · Em atraso · Clientes activos · Serviços activos · Vencendo em breve. Each = 11px uppercase label (letter-spacing .07em, `--text-muted`) + 30px mono value (weight 600, letter-spacing -.03em) + optional 12px footnote. Card: white, 1px `#e5e8ef`, radius 12px, padding 16px, shadow `0 1px 2px rgba(20,23,29,.04), 0 1px 3px rgba(20,23,29,.05)`.
- **Table:** columns Serviço (logo 26px + name) · Cliente · Vencimento (mono 12px) · Valor (right, mono) · Estado (status pill). Header row: 11px uppercase on `--table-head-bg`. Rows: 11px vertical padding, 1px bottom rule, hover `--surface-hover`, whole row clickable → service detail. Sorted by nearest due date.
- **Alerts list:** rows of icon + text + chevron; each row opens the related service.
- **All figures derive from the ledger** — see State Management. Never hard-code money.

### 2. Clientes (`prototype/ClientsScreen.jsx`)
Status tabs (Todos / Activos / Inactivos with counts) + live search (name, email, company) → table: avatar initials circle 30px + name/email stacked · Empresa (tag) · nº Serviços (mono, right) · Receita/mês (mono, right, muted) · Cliente desde (mono 12px) · Estado. Row click → client detail. Empty search state: `EmptyState` icon `search-x`.

### 3. Cliente (detalhe) (`prototype/ClientDetailScreen.jsx`)
Back link "Clientes" → h1 name → meta row (email as link, phone, company tag, "Cliente desde"). Actions: Editar, **Novo serviço** (primary).
Main: "Serviços" h2 + card grid `repeat(auto-fill,minmax(232px,1fr))` of `ServiceCard`.
Rail: "Resumo financeiro" (Total pago / Total pendente / Receita mensal / Receita anual / Último pagamento / Próximo vencimento — label left, mono value right, 1px rules between) and "Histórico" timeline.

### 4. Serviços (`prototype/ServicesScreen.jsx`)
Status tabs + category select + search → grid of `ServiceCard` (`repeat(auto-fill,minmax(268px,1fr))`), each with logo, client, **breve descrição clamped to 2 lines**, price, due phrase, status pill, "Ver serviço".
Bottom card: **Biblioteca de serviços** — pill strip of known services with their logos.

### 5. Serviço (detalhe) (`prototype/ServiceDetailScreen.jsx`)
Back link (client name) → h1 = 38px logo + service name → meta (plano tag, periodicidade tag, client email). Actions: **Renovar** (primary), Registrar pagamento, Editar, **Suspender** (danger).
Contextual `AlertBanner`: `vencido` → danger "Este acesso terminou"; `suspenso` → warning with date + reason; `a_vencer` → warning "Vence em N dias".
"Assinatura" card (subtitle = descrição): field grid `repeat(auto-fit,minmax(140px,1fr))`, each field = 11px uppercase label + 15px medium value — Cliente, Categoria, Método habitual, Valor, Período, Início, Vencimento, Próximo aviso, Estado (pill).
"Lembretes" card: checkbox row for 30/15/7/3/1 dias antes, no dia, após vencimento.
Rail: "Histórico do serviço" timeline.
**Renovar dialog** (440px): Valor pago (suffix MZN), Data do pagamento, Período select, **método picker with logos**, Observações, plus a computed line "Novo vencimento calculado: dd/mm/aaaa". Confirm → status `activo`, success toast.
**Suspender dialog** (420px): Motivo select, Data, Observação, "Enviar email de aviso" checkbox. Confirm → status `suspenso`, danger toast. **Never delete the service.**

### 6. Vencimentos (`prototype/DueDatesScreen.jsx`)
Toggle Lista / Calendário.
Lista: status tabs → table (Serviço with logo + plano, Cliente, Período, Vencimento, Valor, Estado with relative label "Vence em N dias" / "Vencido há N dia").
Calendário: 7-column month grid, 1px gaps on `--border-subtle`, weekday header row on `--table-head-bg`, cells min-height 78px; each event = 11px pill tinted by status with a 14px service logo; click → service detail.

### 7. Pagamentos (`prototype/PaymentsScreen.jsx`)
4 metric tiles (Entradas do período, A receber, MRR, ARR) → **method logo pills as quick filters** (toggle) → month / method / client selects + search → table: Data (mono) · Cliente · Serviço (logo 24px) · Período (outline tag) · Método (**logo badge**) · Valor (right, green). Card subtitle = period total; header tag = record count.

### 8. Finanças (`prototype/FinanceScreen.jsx`)
Tiles: Entradas, A receber, MRR, ARR. Then two cards: **"Como o dinheiro entrou"** — one row per payment method (logo badge + 8px progress bar in `--green-600` + amount) — and **"Entradas por mês"** (last 5 months, same bar pattern in `--accent`). Bottom: "A receber" table.

### 9. Histórico (`prototype/HistoryScreen.jsx`)
Type tabs (Todos / Pagamentos / Emails / Alterações) + search → `Timeline` of global events; rail card "Últimos pagamentos" (service logo + name + method badge + amount). **Append-only: history is never edited or deleted.**

### 10. Configurações (`prototype/SettingsScreen.jsx`)
Tabs: **Biblioteca de Serviços** (add-to-library form with `LogoSlot` + `Textarea` descrição; library table with logo, categoria, descrição padrão, row actions upload/edit/archive; accepted payment methods card) · **Lembretes** (daily-check switch, interval checkboxes, editable email template with `[NOME]` / `[SERVIÇO]` / `[VALOR]` / `[DATA]` chips) · **Empresa** (name, sender email, company logo slot, default currency, timezone; SMTP card with test-send button).

---

## Interactions & Behavior
- **Navigation:** sidebar selects a view; table rows and service cards open detail views; detail views have a back link that returns to the originating list (or to the client when entered from there).
- **Transitions:** controls 140ms, surfaces 200ms, dialog entry 320ms, easing `cubic-bezier(.2,.6,.25,1)`. Fades and colour interpolation only — no slides on data, no springs, no bounce. `prefers-reduced-motion` reduces all durations to ~0.
- **Hover:** background one tint step darker (`--surface-hover`); interactive cards raise to `--shadow-raised`; rows highlight only when clickable. **No transforms.**
- **Press:** next tint step (`--surface-active`). No scale.
- **Focus:** `box-shadow: 0 0 0 3px rgba(51,72,216,.22)` replacing the resting shadow.
- **Disabled:** opacity .45, colours unchanged.
- **Toasts:** bottom-right, 320px, auto-dismiss ~3.2s, for renewal / payment / suspension confirmations.
- **Dialogs:** scrim `--scrim` with 2px blur, panel top-aligned at 8vh, click-outside closes, footer actions right-aligned.
- **Dark mode:** `data-theme="dark"` on `<html>`, toggled from the top bar, persisted in `localStorage["sm-theme"]`, first load follows `prefers-color-scheme`.
- **Responsive:** desktop-first. Tile grid auto-fits (6 → 2 columns); detail two-column grid should stack below ~900px; the sidebar should become a drawer on mobile (not built in the prototype); minimum touch target 44px.
- **Validation (to implement):** required cliente, serviço, valor > 0, periodicidade, data de início; vencimento must be ≥ início; email format; error text 12px `--red-600` under the field with a red border.

## State Management
Prototype state is local React state plus one fake dataset. Real implementation needs:
- **Entities** (as in the brief): `clientes`, `servicos` (assinaturas), `pagamentos`, `notificacoes`, `historico`, plus a `biblioteca_servicos` table (nome, categoria, logo, descrição padrão, arquivado).
- **Derived figures — compute once, server-side, and reuse everywhere.** The prototype does this in `prototype/data.js` (`SM_DATA.totais`): `entradas` (sum of the month's payments), `nPagamentos`, `aReceber` + `nAReceber` (services `vencido` or `a_vencer`), `emAtraso` + `nEmAtraso`, `aVencer`, `clientesActivos`, `servicosActivos`, `mrr` (monthly-normalised value of non-cancelled, non-suspended services), `arr = mrr * 12`, `esperada = entradas + aReceber`. Sidebar badges and the bell count read from the same object. **Nothing may hard-code a number.**
- **Status machine:** `activo → a_vencer → vencido → suspenso | cancelado`; `Renovar` returns to `activo` and recomputes the next due date from the periodicidade (mensal 01/09 → 01/10 → 01/11; anual 01/09/2026 → 01/09/2027).
- **Daily job:** check all services, compute upcoming due dates, send the due reminders, update statuses, log every event and every email in `historico`, and **never send the same reminder twice for the same date**.
- **Suspension record:** date, reason, note, who did it; reminders stop; nothing is deleted.

## Design Tokens
Adopt `design_system/styles.css` (imports `tokens/*.css`) verbatim. Key values:
- **Neutrals:** `#ffffff` `#fcfcfd` `#f8f9fb` `#f1f3f7` `#e5e8ef` `#d2d7e2` `#9aa2b4` `#6c7484` `#4d5464` `#343a47` `#22262f` `#14171d`
- **Accent (Cobalt):** `#3348d8` (hover `#2839b4`, press `#1f2c8c`, soft `#eef1fe`, soft border `#bcc7fb`)
- **Status:** activo `#12805c` on `#e8f6f0` · a vencer `#a76308` on `#fdf3e3` · vencido `#c4342b` on `#fdeceb` · suspenso `#22262f` on `#f1f3f7` · cancelado `#6c7484` on `#f8f9fb`. Always dot **+** word; colour alone is never the signal, and no emoji.
- **Dark theme:** app `#0d1014`, card `#15191f`, sunken `#1f242c`, borders `#252b34` / `#333a45`, text `#f2f4f8` / `#c8cdd8` / `#8f98a8`, accent `#4a5cf0`, status fg `#5fd0a4` / `#f0c073` / `#f28b83`. Filled controls keep white labels (`--on-accent`, `--on-danger`).
- **Spacing:** 2 · 4 · 6 · 8 · 12 · 16 · 20 · 24 · 32 · 40 · 56 · 72. Gutter 24, card padding 16, card gap 12.
- **Radii:** 4 checkbox · 6 controls · 9 banners · 12 cards/dialogs · 16 · pill.
- **Control heights:** 28 / 36 / 44.
- **Shadows:** `--shadow-sm` `0 1px 2px rgba(20,23,29,.05)`; `--shadow-card` `0 1px 2px rgba(20,23,29,.04), 0 1px 3px rgba(20,23,29,.05)`; `--shadow-raised` `0 2px 4px rgba(20,23,29,.05), 0 8px 20px -6px rgba(20,23,29,.10)`; `--shadow-overlay` `0 12px 32px -8px rgba(20,23,29,.22), 0 2px 6px rgba(20,23,29,.08)`.
- **Type:** **Geist** for UI, **Geist Mono** with tabular figures for *every* number. Scale 36/28/21/17/15/13/12/11(uppercase, .07em) and 30 mono for metrics; negative tracking growing with size (-.011em @17 → -.028em @36); headings weight 600.
- **Money format:** `1.500 MZN` — dot thousands separator, comma decimals, code after the number, mono tabular. Dates `dd/mm/aaaa`.

## Assets
- `design_system/assets/logos/mpesa.png`, `emola.png` — **supplied by the client**, used by `PaymentMethod`. Bank transfer and cash currently use icon tiles (no mark supplied).
- **Icons:** Lucide, loaded per glyph from `https://unpkg.com/lucide-static@0.454.0/icons/<name>.svg` as a CSS mask over `currentColor` (see `components/core/Icon.jsx`). In production, install `lucide-react` (or the framework equivalent) and keep the same names — full mapping in `design_system/readme.md` § ICONOGRAPHY. No hand-drawn SVG anywhere.
- **Fonts:** Geist / Geist Mono via Google Fonts (`tokens/fonts.css`) — substitution, no binaries were supplied.
- **Brand logo: none exists.** An "SM" monogram tile + wordmark stands in. See `design_system/guidelines/brand-brief.md` for the logo brief.
- **Service logos:** `components/data/ServiceLogo.jsx` resolves famous services by name from `LOGO_LIBRARY` (`chatgpt.svg`, `claude.svg`, `canva.svg`, `google-workspace.svg`, `microsoft-365.svg`, `github.svg`, `adobe.svg`, `dropbox.svg`, `zoom.svg`, `notion.svg`, `figma.svg`, `wordpress.svg`, `cpanel.svg`). Drop the vendors' own files into the logo folder and they appear everywhere; until then a category-tinted monogram is shown. **Third-party marks are never redrawn.**

## Files
```
(paths below are relative to the project root of this download)
styles.css + tokens/  → design tokens
components/           → the components described as design_system/components
ui_kits/subscription-manager/ → the files described as prototype/

design_system/
  styles.css                 entry point — @import list only
  tokens/*.css               colors (incl. dark theme), typography, spacing, elevation, motion, fonts, base
  components/core/           Icon Button IconButton Input Textarea SearchInput Select Checkbox Switch LogoSlot ThemeToggle Card
  components/data/           ServiceLogo ServiceCard StatCard MoneyValue PaymentMethod DataTable Timeline
  components/feedback/       StatusBadge Tag AlertBanner Dialog EmptyState Toast
  components/navigation/     SidebarNav TopBar PageHeader Tabs
  readme.md                  full brand guide: content rules, visual foundations, iconography
  guidelines/brand-brief.md  logo brief + what is still missing
  assets/logos/              mpesa.png, emola.png
prototype/
  index.html                 open this to click through the whole app
  App.jsx                    shell + routing
  data.js                    fake dataset + SM_DATA.totais (the single source of truth for figures)
  *Screen.jsx                the ten views listed above
  NewSubscriptionDialog.jsx  library picker + logo slot + descrição + method picker
  README.md                  what is clickable and what was deliberately left out
```
Each component has a sibling `.d.ts` (props contract) and `.prompt.md` (when/how to use it) — read those before porting.

## Priority order for implementation
1. Data model + statuses + due-date maths + the derived-totals query.
2. Shell (sidebar, top bar, dark theme) and Dashboard.
3. Clientes + Cliente detalhe.
4. Serviços + Serviço detalhe with Renovar / Suspender.
5. Pagamentos + Finanças.
6. Reminder engine (daily job, dedup, history log) + email templates.
7. Vencimentos calendar, Histórico, Configurações, global search.
