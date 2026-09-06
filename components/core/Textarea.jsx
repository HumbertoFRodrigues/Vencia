import React from "react";
export function Textarea({ label, hint, error, rows = 3, id, style, ...rest }) {
  const [focus, setFocus] = React.useState(false);
  const uid = id || React.useMemo(() => "ta-" + Math.random().toString(36).slice(2, 7), []);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 6, ...style }}>
      {label ? <label htmlFor={uid} style={{ fontSize: "var(--text-sm)", fontWeight: "var(--weight-medium)", color: "var(--text-strong)" }}>{label}</label> : null}
      <textarea id={uid} rows={rows} onFocus={() => setFocus(true)} onBlur={() => setFocus(false)} {...rest}
        style={{ resize: "vertical", padding: "8px 10px", background: "var(--surface-card)", border: "1px solid " + (error ? "var(--red-600)" : focus ? "var(--border-focus)" : "var(--border-default)"), borderRadius: "var(--radius-sm)", boxShadow: focus ? "var(--ring)" : "var(--shadow-sm)", outline: "none", font: "inherit", fontSize: "var(--text-sm)", lineHeight: 1.5, color: "var(--text-strong)", transition: "var(--transition-control)" }} />
      {error || hint ? <span style={{ fontSize: "var(--text-xs)", color: error ? "var(--red-600)" : "var(--text-muted)" }}>{error || hint}</span> : null}
    </div>
  );
}
