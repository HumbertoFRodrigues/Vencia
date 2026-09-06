# UI kit — Vencia (app admin)

Click-through recreation of the single-admin subscription/service manager described in the brief.
Everything visual comes from the design-system bundle (`_ds_bundle.js`); this kit only composes screens and holds fake data.

## Run
Open `index.html`. React, ReactDOM and Babel load from CDN; `../../_ds_bundle.js` supplies the components.

## Files
- `data.js` — fake clientes / serviços / pagamentos / histórico (`window.SM_DATA`), dated around 30/08/2026.
- `App.jsx` — shell + routing (sidebar → screens, row click → detail, toasts).
- `DashboardScreen.jsx` — 6 metric tiles, próximos vencimentos table, alerts, MRR/ARR.
- `ClientsScreen.jsx` — client list with status tabs and live search.
- `ClientDetailScreen.jsx` — client header, service card grid, financial summary, histórico.
- `ServiceDetailScreen.jsx` — subscription facts, reminder intervals, histórico, **Renovar** and **Suspender** dialogs.
- `DueDatesScreen.jsx` — list view with status tabs + month calendar view (September 2026).
- `PaymentsScreen.jsx` — payment ledger with month/method/client filters, period total, MRR/ARR.
- `SettingsScreen.jsx` — add-to-library form (logo slot + descrição), Biblioteca de Serviços, accepted payment methods, reminder intervals, email template, empresa + SMTP.
- `ServicesScreen.jsx` — full service grid with category/status filters and the library strip.
- `FinanceScreen.jsx` — entradas, a receber, MRR/ARR, breakdown by payment method, month-by-month bars.
- `HistoryScreen.jsx` — global event log with type filters.
- `NewSubscriptionDialog.jsx` — library picker, custom logo slot, breve descrição, payment method.

## What you can click
Sidebar destinations · any table row · service cards · Renovar (fills a payment, recalculates the due date, flips status to Activo, toasts) · Suspender (records reason/date, flips to Suspenso) · status tabs · payments filters · calendar events · settings tabs.

## Logos
- `assets/logos/mpesa.png` and `emola.png` (supplied) render in the ledger, the renewal dialog, Finanças and settings.
- Famous services resolve by name from `LOGO_LIBRARY`; drop the vendor file in `assets/logos/` and it replaces the monogram everywhere. Pages pass `assetsBase={window.LOGO_BASE}` (`../../assets/logos/`).
- `LogoSlot` provides the "Adicionar logo personalizado" target in the new-subscription dialog and in Configurações.

## Dark mode
The top bar's sun/moon toggle sets `data-theme` on `<html>`; the choice persists in localStorage.

## Deliberately not built
Login/auth, real email sending, edit forms (only the create flow is mocked), real file upload (the logo slots are visual targets).
