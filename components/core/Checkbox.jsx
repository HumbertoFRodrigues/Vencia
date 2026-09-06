import React from "react";
import { Icon } from "./Icon.jsx";
export function Checkbox({ label, description, checked, onChange, disabled, style }) {
  return (
    <label style={{ display: "inline-flex", alignItems: description ? "flex-start" : "center", gap: 9, cursor: disabled ? "not-allowed" : "pointer", opacity: disabled ? 0.5 : 1, ...style }}>
      <input type="checkbox" checked={!!checked} disabled={disabled} onChange={(e) => onChange && onChange(e.target.checked, e)} style={{ position: "absolute", opacity: 0, width: 0, height: 0 }} />
      <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 17, height: 17, marginTop: description ? 2 : 0, flex: "0 0 auto", borderRadius: "var(--radius-xs)", background: checked ? "var(--accent)" : "var(--surface-card)", border: "1px solid " + (checked ? "var(--accent)" : "var(--border-default)"), boxShadow: "var(--shadow-sm)", transition: "var(--transition-control)" }}>
        {checked ? <Icon name="check" size={12} color="var(--on-accent)" /> : null}
      </span>
      {label ? (
        <span style={{ display: "flex", flexDirection: "column", gap: 2 }}>
          <span style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)" }}>{label}</span>
          {description ? <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{description}</span> : null}
        </span>
      ) : null}
    </label>
  );
}
