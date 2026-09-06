# Vencia — Design System

A design system for **Vencia**: a single-admin web tool for managing clients, recurring services (domains, hosting, corporate email, AI tool access, maintenance), payments, renewals and expiry dates. Currency is the Mozambican metical (MZN); the interface language is **European Portuguese**.

## Sources

- **Written product brief only** — pasted into chat on 30/08/2026 (structure, screens, data model, states, email copy, and the "IDENTIDADE VISUAL DOS SERVIÇOS" section on service logos).
- **No codebase, Figma file, screenshots, deck, logo or font binaries were provided.** Every visual decision here — palette, type, spacing, elevation, motion — was authored for this system and should be treated as a proposal to confirm, not a recreation of an existing brand.

## Product surfaces

One product, one surface: a desktop-first responsive admin app with eight destinations — Dashboard, Clientes, Serviços, Vencimentos, Pagamentos, Finanças, Histórico, Configurações. There is no marketing site, no client-facing portal and no mobile app in the brief; the only outbound artefact is transactional email (renewal / expiry / expired notices).

The core loop the design serves: **cliente → vários serviços → pagamentos → vencimentos → lembretes → histórico**. Every screen is judged by one test from the brief — can the admin answer "who paid me, what expires next, who is overdue, what must I suspend" without hunting?

---

## CONTENT FUNDAMENTALS

**Language.** European Portuguese (pt-PT), Mozambican conventions: *activo*, *serviço*, *periodicidade*, *vencimento*, *contacto*. Dates are `dd/mm/aaaa`, always zero-padded (`02/09/2026`). Money is `1.500 MZN` — dot thousands separator, currency code after the number, never a symbol, never "MZN 1500".

**Casing.** Sentence case everywhere: page titles ("Próximos vencimentos"), buttons ("Registrar pagamento"), field labels ("Data de vencimento"). The only uppercase is the 11px eyebrow/table-header label, letter-spaced. No Title Case, no ALL CAPS headings.

**Voice, admin-facing.** Terse and factual, noun-first for labels, imperative verbs for actions: *Renovar*, *Suspender*, *Registrar pagamento*, *Adicionar logo personalizado*. The UI never says "I" and rarely says "you" — it states facts about objects: "Vence em 5 dias", "Vencido há 2 dias", "Suspenso em 05/10/2026 — pagamento não renovado". Alerts count things: "3 serviços vencem hoje", "2 pagamentos estão pendentes".

**Voice, client-facing email.** Formal *você*-neutral Portuguese with polite distance, opening "Olá, [NOME]." and closing "Atenciosamente, [NOME DA EMPRESA]". Placeholders are square-bracket uppercase tokens — `[NOME]`, `[SERVIÇO]`, `[VALOR]`, `[DATA]` — and render as cobalt-tinted chips in the template editor. Emails state the fact, then the consequence, then the ask; they never threaten or use urgency language ("ÚLTIMA CHANCE").

**Numbers carry the message.** A screen leads with a figure, not a sentence: metric tiles are a label plus one number. Prose is a supporting line under the number ("2.500 em falta"), max one line.

**Emoji.** The brief sketches states with 🟢🟡🔴⚫⚪. In the product these become the coloured dot inside `StatusBadge` — **no emoji ship in the UI, in email, or in this design system.** Coloured dot + word, always both, never colour alone.

**Empty and error copy.** Short and instructive: "Sem clientes — Adicione o primeiro cliente para começar." Errors name the field problem: "Número inválido".

---

## VISUAL FOUNDATIONS

**Feel.** Quiet operational software: white cards on a very light grey field, hairline borders, one saturated accent, and colour reserved almost entirely for state. Nothing decorative competes with the numbers. Density is medium — comfortable rows (11px vertical padding), 16px card padding, 12px between cards, 24px page gutter.

**Colour.** A cool neutral ink ramp (`--ink-0` → `--ink-900`) does ~90% of the work: `--surface-app` #f8f9fb behind, `--surface-card` white on top, `--border-subtle` #e5e8ef for every rule. One brand accent, **Cobalt** `--accent` #3348d8, used only for: the primary button, the active nav icon, the active tab underline, focus rings, selected filters, and email placeholder chips. Four status hues never used decoratively — green #12805c (activo), amber #a76308 (a vencer), red #c4342b (vencido), ink #22262f (suspenso), grey (cancelado). Money in is green, money out/overdue is red, everything else is ink. Max two background colours per screen (app grey + card white); tinted surfaces exist only as status/alert backgrounds at 50-level tints.

**Type.** Two families. **Geist** for all UI text — 300–700, tight negative tracking that increases with size (-0.011em at 17px, -0.028em at 36px), semibold for headings and values. **Geist Mono** with tabular figures for every number: money, dates, counts, MRR/ARR, kbd chips. This mono/sans split *is* the typographic signature — if a figure is not mono, it is a bug. Scale: 36 display / 28 h1 / 21 h2 / 17 h3 / 15 body / 13 dense-UI / 12 meta / 11 uppercase label; 30px mono for metrics.

**Dark mode.** Fully supported and token-only: `[data-theme="dark"]` in `tokens/colors.css` re-points the *semantic aliases* (surfaces, text, borders, status, category tints, chrome) and nothing else, so every component follows without a single theme prop. Dark surfaces are neutral near-blacks (#0d1014 app, #15191f card) — never pure black, never tinted blue. Status hues get lighter, less saturated variants so they stay readable on dark; filled buttons keep white labels via `--on-accent` / `--on-danger`; uploaded service logos sit on a white plate (`img[data-logo]`) so dark PNGs stay legible. `ThemeToggle` writes `data-theme` on `<html>` and remembers the choice; the first load follows `prefers-color-scheme`.

**Backgrounds.** Flat colour only. No photography, no illustration, no gradient fills, no patterns, no texture, no grain, no noise. The only non-flat surface in the system is the top bar's translucent white (`rgba(255,255,255,.82)`) with `backdrop-filter: blur(8px)`, plus the dialog scrim (ink at 38% with a 2px blur). Transparency and blur appear **only** in those two places — never on cards, never behind text.

**Borders and radii.** 1px `--border-subtle` on every card, table rule and divider; `--border-default` #d2d7e2 on controls; no double borders, no vertical rules inside tables. Radii: 4px checkbox, 6px controls and buttons, 9px alert banners, 12px cards and dialogs, pill for status badges and count bubbles. Never a card with only a coloured left border.

**Elevation.** Near-flat by design. `--shadow-card` is two barely-visible layers at 4–5% ink; hover on an interactive card lifts to `--shadow-raised`; only dialogs and toasts get real depth (`--shadow-overlay`). Filled buttons carry a 10% white inset top highlight. Borders, not shadows, do the separating.

**States.** Hover = background one tint step darker (`--surface-hover`), never a colour change and never a transform. Press = the next tint step (`--surface-active`) — no scale, no shrink, no bounce. Focus = 3px cobalt ring at 22% alpha, replacing (not adding to) the shadow. Disabled = opacity 0.45 with colours unchanged. Rows highlight on hover only when clickable. Selected nav item = white pill with `--shadow-sm` on the grey field, cobalt icon — not a filled block.

**Motion.** Functional and short: 140ms for controls (hover, focus, checked), 200ms for surfaces (card shadow, panels), 320ms reserved for dialog entry, all on `cubic-bezier(.2,.6,.25,1)`. Fades and colour interpolation only — no slide-ins on data, no springs, no bounce, no attention-seeking loops. `prefers-reduced-motion` zeroes every duration.

**Layout rules.** Fixed 236px sidebar, sticky 60px top bar, scrolling content column capped at 1320px. Detail pages are a two-column grid: primary content (~1.9fr) plus a ≥280px rail for summaries and histórico. Metric tiles are an auto-fit grid (min 178px), so six across on desktop collapses gracefully to two on a phone. Money columns are right-aligned; status is the last data column; row actions sit right of status.

**Imagery.** There is none, by intent. The only "images" are service marks: an uploaded logo (PNG/JPG/SVG/WebP, contained in a bordered white tile with 12% padding) or a category-tinted monogram. Tints are desaturated and cool-leaning; nothing warm, nothing photographic.

---

## ICONOGRAPHY

- **Set:** [Lucide](https://lucide.dev) — outline, ~1.75px stroke, rounded caps, 24px grid. **Flagged substitution:** the brief specifies no icon set, so Lucide was chosen for its neutral operational tone. Swap it by changing the single `BASE` URL in `components/core/Icon.jsx`.
- **Delivery:** loaded per-glyph from the pinned CDN (`lucide-static@0.454.0`) and applied as a CSS `mask-image` over `currentColor`, so every icon inherits its label's colour automatically. No icon font, no sprite sheet, no inline SVG in product code, and **no hand-drawn SVG anywhere in this system**.
- **Sizes:** 14px inline with 12–13px text, 15–16px in buttons and table cells, 16px in the sidebar, 17–18px in alert banners, 20px in empty-state tiles.
- **Canonical mapping:** dashboard `layout-dashboard` · clientes `users` · serviços `package` · vencimentos `calendar-clock` · pagamentos `banknote` · finanças `chart-line` · histórico `history` · configurações `settings` · search `search` · notifications `bell` · renew `refresh-cw` · suspend `pause` · overdue `circle-alert` · paid `circle-check` · email `mail` · domínio `globe` · hospedagem `server` · IA `sparkles` · manutenção `wrench`.
- **Emoji and unicode:** never as iconography (see CONTENT FUNDAMENTALS). Currency and separators are plain text; the only unicode furniture is the em dash and middle dot in metadata rows.
- **Logo:** supplied by the user — a 3D gradient "S" mark combining a calendar and a coin stack (`assets/logo-mark.png`, square) and the full lockup with wordmark (`assets/logo.png`). Note this mark uses gradients and dimensional shading, which sits outside this system's flat-colour rule (see Visual Foundations) — treated as an exception because it's the supplied brand asset, not something to imitate elsewhere. Third-party service logos (ChatGPT, Claude, Canva, Google Workspace, Microsoft 365, GitHub, Adobe, Dropbox, Zoom…) are **never redrawn**: the service library expects real uploaded files, and until one exists a category-tinted monogram stands in.

---

## Index

| Path | What it is |
| --- | --- |
| `styles.css` | Global entry point — `@import`s only. Consumers link this one file. |
| `tokens/` | `fonts.css` `colors.css` `typography.css` `spacing.css` `elevation.css` `motion.css` `base.css` |
| `guidelines/` | 19 specimen cards (Colors incl. dark theme, Type, Spacing, Brand) rendered in the Design System tab |
| `components/core/` | Icon, Button, IconButton, Input, Textarea, SearchInput, Select, Checkbox, Switch, LogoSlot, ThemeToggle, Card |
| `components/data/` | ServiceLogo, ServiceCard, StatCard, MoneyValue, PaymentMethod, DataTable, Timeline |
| `components/feedback/` | StatusBadge, Tag, AlertBanner, Dialog, EmptyState, Toast |
| `components/navigation/` | SidebarNav, TopBar, PageHeader, Tabs |
| `ui_kits/subscription-manager/` | Click-through admin app — see its own README |
| `templates/admin-dashboard/` | "Admin dashboard" template consuming projects can start from |
| `thumbnail.html` | Homepage tile |
| `SKILL.md` | Agent-skill entry point |

### Components

Each has a sibling `.d.ts` (props contract) and `.prompt.md` (when/how to use).

**Core** — `Icon`, `Button`, `IconButton`, `Input`, `Textarea`, `SearchInput`, `Select`, `Checkbox`, `Switch`, `LogoSlot`, `ThemeToggle`, `Card`
**Data** — `ServiceLogo`, `ServiceCard`, `StatCard`, `MoneyValue`, `PaymentMethod`, `DataTable`, `Timeline`
**Feedback** — `StatusBadge`, `Tag`, `AlertBanner`, `Dialog`, `EmptyState`, `Toast`
**Navigation** — `SidebarNav`, `TopBar`, `PageHeader`, `Tabs`

### Intentional additions

No source defined a component inventory, so this is an authored standard set sized to the brief. Three entries exist specifically for this product's problems, and are worth calling out:

- **`StatusBadge`** — encodes the brief's five states as the only sanctioned status vocabulary, replacing its emoji sketch.
- **`MoneyValue`** — guarantees one MZN format (mono, tabular, pt-PT separators) across dashboards, tables and cards.
- **`ServiceLogo` / `ServiceCard`** — implement the "IDENTIDADE VISUAL DOS SERVIÇOS" requirement without redrawing third-party marks. `ServiceCard` also carries the service's *breve descrição* (clamped to two lines).
- **`PaymentMethod`** — badge for M-Pesa, e-Mola, transferência bancária, dinheiro and outro, so the ledger and the renewal dialog show *how* the money arrived.
- **`LogoSlot`** — the "Adicionar logo personalizado" upload target (PNG/JPG/SVG/WebP, always contained in a fixed square).
- **`Textarea`** — the short per-client service description.
- **`ThemeToggle`** — light/dark switch; dark mode itself is pure tokens.

Two omissions worth confirming: no Tooltip (titles carry the job today) and no Pagination (lists are short in the brief's scenarios).

### Open questions / substitutions to confirm

1. **Fonts** — Geist + Geist Mono are loaded from Google Fonts because no binaries were supplied. Send real files (or name the licensed family) and `tokens/fonts.css` is the only file that changes.
2. **Colour** — Cobalt is an authored accent, not a brand colour. If the business has a brand palette, `tokens/colors.css` semantic aliases absorb it without touching components.
3. **Logo** — needed as SVG; the SM monogram is a placeholder.
4. **Service logo library** — `ServiceLogo` already resolves famous services by name (`LOGO_LIBRARY` in `components/data/ServiceLogo.jsx`: chatgpt.svg, claude.svg, canva.svg, google-workspace.svg, microsoft-365.svg, github.svg, adobe.svg, dropbox.svg, zoom.svg, notion.svg, figma.svg, wordpress.svg, cpanel.svg). **Drop those files into `assets/logos/` and every screen picks them up with no code change**; until then the monogram tile is shown. I did not draw them — third-party marks must come from the vendors' own brand assets.
5. **Payment logos** — M-Pesa and e-Mola use the two files you supplied (`assets/logos/mpesa.png`, `emola.png`). Bank transfer and cash have no supplied mark; send a bank logo (or per-bank logos: BCI, BIM/Millennium, Standard Bank, Absa) and I will wire them into `PAYMENT_METHODS`.
6. **Icons** — Lucide is a substitution, swappable in one line.
