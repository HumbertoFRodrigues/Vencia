import React from "react";
export function Switch({ checked, onChange, label, disabled, style }) {
  return (
    <label style={{ display: "inline-flex", alignItems: "center", gap: 10, cursor: disabled ? "not-allowed" : "pointer", opacity: disabled ? 0.5 : 1, ...style }}>
      <input type="checkbox" checked={!!checked} disabled={disabled} onChange={(e) => onChange && onChange(e.target.checked, e)} style={{ position: "absolute", opacity: 0, width: 0, height: 0 }} />
      <span style={{ position: "relative", width: 36, height: 21, flex: "0 0 auto", borderRadius: "var(--radius-pill)", background: checked ? "var(--accent)" : "var(--border-default)", transition: "background-color var(--dur-fast) var(--ease-standard)" }}>
        <span style={{ position: "absolute", top: 2, left: checked ? 17 : 2, width: 17, height: 17, borderRadius: "50%", background: "var(--on-accent)", boxShadow: "var(--shadow-sm)", transition: "left var(--dur-fast) var(--ease-standard)" }} />
      </span>
      {label ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)" }}>{label}</span> : null}
    </label>
  );
}
