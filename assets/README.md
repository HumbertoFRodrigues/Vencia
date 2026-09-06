# assets/

Empty by design. **No logo, icon file, image or font binary was supplied with the brief.**

- Brand mark: supplied — `logo-mark.png` (square) and `logo.png` (full lockup with wordmark), wired into SidebarNav, TopBar, the wordmark card and thumbnail.
- Icons: loaded from the pinned Lucide CDN by `components/core/Icon.jsx` (no local files).
- Fonts: Geist / Geist Mono loaded from Google Fonts in `tokens/fonts.css`.
- **Supplied by the user:** `logos/mpesa.png`, `logos/emola.png` — used by `PaymentMethod`.
- Service logos: drop vendor files in `logos/` using the names in `LOGO_LIBRARY` (`chatgpt.svg`, `claude.svg`, `canva.svg`, `google-workspace.svg`, `microsoft-365.svg`, `github.svg`, `adobe.svg`, `dropbox.svg`, `zoom.svg`, `notion.svg`, `figma.svg`, `wordpress.svg`, `cpanel.svg`) and `ServiceLogo` resolves them automatically. They are never redrawn.
