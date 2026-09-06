import React from "react";
import { Icon } from "../core/Icon.jsx";
export function Toast({ tone = "success", title, children, onClose, style }) {
  const map = { success: ["circle-check", "var(--green-600)"], danger: ["circle-alert", "var(--red-600)"], info: ["info", "var(--accent)"] };
  const [icon, color] = map[tone] || map.info;
  return (
    <div role="status" style={{ display: "flex", alignItems: "flex-start", gap: 10, width: 320, padding: "12px 13px", background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-md)", boxShadow: "var(--shadow-overlay)", ...style }}>
      <Icon name={icon} size={17} color={color} style={{ marginTop: 1 }} />
      <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 2 }}>
        <strong style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)" }}>{title}</strong>
        {children ? <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{children}</span> : null}
      </div>
      {onClose ? <span onClick={onClose} style={{ cursor: "pointer", display: "flex" }}><Icon name="x" size={14} color="var(--text-faint)" /></span> : null}
    </div>
  );
}
