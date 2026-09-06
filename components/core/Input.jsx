import React from "react";
import { Icon } from "./Icon.jsx";
export function Input({ label, hint, error, icon, suffix, size = "md", id, style, ...rest }) {
  const [focus, setFocus] = React.useState(false);
  const uid = id || React.useMemo(() => "in-" + Math.random().toString(36).slice(2, 7), []);
  const h = size === "lg" ? "var(--control-h-lg)" : size === "sm" ? "var(--control-h-sm)" : "var(--control-h-md)";
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 6, ...style }}>
      {label ? <label htmlFor={uid} style={{ fontSize: "var(--text-sm)", fontWeight: "var(--weight-medium)", color: "var(--text-strong)" }}>{label}</label> : null}
      <div style={{ display: "flex", alignItems: "center", gap: 8, height: h, padding: "0 10px", background: "var(--surface-card)", border: "1px solid " + (error ? "var(--red-600)" : focus ? "var(--border-focus)" : "var(--border-default)"), borderRadius: "var(--radius-sm)", boxShadow: focus ? "var(--ring)" : "var(--shadow-sm)", transition: "var(--transition-control)" }}>
        {icon ? <Icon name={icon} size={15} color="var(--text-faint)" /> : null}
        <input id={uid} onFocus={() => setFocus(true)} onBlur={() => setFocus(false)} {...rest}
          style={{ flex: 1, minWidth: 0, border: "none", outline: "none", background: "transparent", font: "inherit", fontSize: "var(--text-sm)", color: "var(--text-strong)" }} />
        {suffix ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)", whiteSpace: "nowrap" }}>{suffix}</span> : null}
      </div>
      {error || hint ? <span style={{ fontSize: "var(--text-xs)", color: error ? "var(--red-600)" : "var(--text-muted)" }}>{error || hint}</span> : null}
    </div>
  );
}
