import React from "react";
import { Icon } from "./Icon.jsx";
export function Select({ label, hint, options = [], size = "md", id, style, ...rest }) {
  const uid = id || React.useMemo(() => "se-" + Math.random().toString(36).slice(2, 7), []);
  const h = size === "lg" ? "var(--control-h-lg)" : size === "sm" ? "var(--control-h-sm)" : "var(--control-h-md)";
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 6, ...style }}>
      {label ? <label htmlFor={uid} style={{ fontSize: "var(--text-sm)", fontWeight: "var(--weight-medium)", color: "var(--text-strong)" }}>{label}</label> : null}
      <div style={{ position: "relative", display: "flex", alignItems: "center" }}>
        <select id={uid} {...rest}
          style={{ appearance: "none", width: "100%", height: h, padding: "0 30px 0 10px", background: "var(--surface-card)", border: "1px solid var(--border-default)", borderRadius: "var(--radius-sm)", boxShadow: "var(--shadow-sm)", font: "inherit", fontSize: "var(--text-sm)", color: "var(--text-strong)", cursor: "pointer" }}>
          {options.map((o) => { const v = typeof o === "string" ? o : o.value, l = typeof o === "string" ? o : o.label; return <option key={v} value={v}>{l}</option>; })}
        </select>
        <Icon name="chevron-down" size={15} color="var(--text-faint)" style={{ position: "absolute", right: 9, pointerEvents: "none" }} />
      </div>
      {hint ? <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{hint}</span> : null}
    </div>
  );
}
