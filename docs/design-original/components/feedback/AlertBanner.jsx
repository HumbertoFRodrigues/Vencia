import React from "react";
import { Icon } from "../core/Icon.jsx";
const TONES = {
  info: { bg: "var(--accent-soft)", bd: "var(--accent-soft-border)", fg: "var(--text-accent)", icon: "info" },
  warning: { bg: "var(--status-due-bg)", bd: "var(--amber-100)", fg: "var(--status-due-fg)", icon: "triangle-alert" },
  danger: { bg: "var(--status-overdue-bg)", bd: "var(--red-100)", fg: "var(--status-overdue-fg)", icon: "circle-alert" },
  success: { bg: "var(--status-active-bg)", bd: "var(--green-100)", fg: "var(--status-active-fg)", icon: "circle-check" },
};
export function AlertBanner({ tone = "info", title, children, action, style }) {
  const t = TONES[tone] || TONES.info;
  return (
    <div style={{ display: "flex", alignItems: "flex-start", gap: 10, padding: "12px 14px", background: t.bg, border: "1px solid " + t.bd, borderRadius: "var(--radius-md)", ...style }}>
      <Icon name={t.icon} size={17} color={t.fg} style={{ marginTop: 1 }} />
      <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 2 }}>
        {title ? <strong style={{ fontSize: "var(--text-sm)", color: t.fg, fontWeight: "var(--weight-semibold)" }}>{title}</strong> : null}
        {children ? <div style={{ fontSize: "var(--text-sm)", color: "var(--text-body)" }}>{children}</div> : null}
      </div>
      {action}
    </div>
  );
}
