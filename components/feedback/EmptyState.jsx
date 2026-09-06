import React from "react";
import { Icon } from "../core/Icon.jsx";
export function EmptyState({ icon = "inbox", title, description, action, style }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", alignItems: "center", gap: 8, padding: "40px 24px", textAlign: "center", ...style }}>
      <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 40, height: 40, borderRadius: "var(--radius-md)", background: "var(--surface-sunken)" }}>
        <Icon name={icon} size={20} color="var(--text-faint)" />
      </span>
      <strong style={{ fontSize: "var(--text-h3)", color: "var(--text-strong)" }}>{title}</strong>
      {description ? <p style={{ margin: 0, maxWidth: 320, fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{description}</p> : null}
      {action ? <div style={{ marginTop: 6 }}>{action}</div> : null}
    </div>
  );
}
