Visual identity for a service. Famous services resolve automatically from the logo library by name (ChatGPT, Claude, Canva, Google Workspace, Microsoft 365, GitHub, Adobe, Dropbox, Zoom, Notion, Figma, WordPress, cPanel) as soon as the file exists in `assets/logos/`; anything else falls back to a category-tinted monogram. Third-party marks are never redrawn.

```jsx
<ServiceLogo name="ChatGPT" category="ia" size={40} />
<ServiceLogo name="exemplo.co.mz" category="dominio" size={32} />
<ServiceLogo name="Canva" category="software" src={uploadedUrl} />
```

Pass `assetsBase` when the page is not served from the project root (e.g. `"../../assets/logos/"`).
