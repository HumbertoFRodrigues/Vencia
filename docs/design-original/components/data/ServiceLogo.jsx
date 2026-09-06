import React from "react";
import { Icon } from "../core/Icon.jsx";
const CATEGORY = {
  dominio: { icon: "globe", bg: "var(--cat-dominio-bg)", fg: "var(--cat-dominio-fg)" },
  hospedagem: { icon: "server", bg: "var(--cat-hospedagem-bg)", fg: "var(--cat-hospedagem-fg)" },
  email: { icon: "mail", bg: "var(--cat-email-bg)", fg: "var(--cat-email-fg)" },
  ia: { icon: "sparkles", bg: "var(--cat-ia-bg)", fg: "var(--cat-ia-fg)" },
  software: { icon: "app-window", bg: "var(--cat-software-bg)", fg: "var(--cat-software-fg)" },
  manutencao: { icon: "wrench", bg: "var(--cat-manutencao-bg)", fg: "var(--cat-manutencao-fg)" },
  desenvolvimento: { icon: "code", bg: "var(--cat-desenvolvimento-bg)", fg: "var(--cat-desenvolvimento-fg)" },
  outro: { icon: "package", bg: "var(--cat-outro-bg)", fg: "var(--cat-outro-fg)" },
};
/** Known services → file name (served from assets/logos/). Add a local assets/logos/<file>
 *  and its entry here to switch a service over from the monogram fallback to a real logo. */
export const LOGO_LIBRARY = {};
export function resolveServiceLogo(name = "", assetsBase = "/assets/logos/") {
  const key = String(name).trim().toLowerCase();
  const file = LOGO_LIBRARY[key] || Object.entries(LOGO_LIBRARY).find(([k]) => key.startsWith(k))?.[1];
  if (!file) return null;
  return /^https?:\/\//.test(file) ? file : assetsBase + file;
}
export function ServiceLogo({ name = "", category = "outro", src, size = 40, assetsBase = "/assets/logos/", style }) {
  const c = CATEGORY[category] || CATEGORY.outro;
  const radius = size >= 40 ? "var(--radius-md)" : "var(--radius-sm)";
  const [broken, setBroken] = React.useState(false);
  const url = src || resolveServiceLogo(name, assetsBase);
  if (url && !broken) return <img src={url} data-logo alt={name} title={name} onError={() => setBroken(true)} style={{ width: size, height: size, flex: "0 0 auto", objectFit: "contain", background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: radius, padding: Math.round(size * 0.12), ...style }} />;
  const initials = name.replace(/[^\p{L}\p{N} ]/gu, "").split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
  return (
    <span title={name} style={{ position: "relative", display: "inline-flex", alignItems: "center", justifyContent: "center", width: size, height: size, flex: "0 0 auto", borderRadius: radius, background: c.bg, color: c.fg, fontFamily: "var(--font-sans)", fontSize: Math.round(size * 0.36), fontWeight: "var(--weight-semibold)", letterSpacing: "-0.02em", ...style }}>
      {initials || <Icon name={c.icon} size={Math.round(size * 0.5)} />}
      {initials ? <Icon name={c.icon} size={Math.max(9, Math.round(size * 0.26))} style={{ position: "absolute", right: -2, bottom: -2, background: "var(--surface-card)", borderRadius: "var(--radius-pill)", padding: 2, boxShadow: "0 0 0 1px var(--border-subtle)", color: c.fg }} /> : null}
    </span>
  );
}
export const SERVICE_CATEGORIES = CATEGORY;
