import React from "react";
export function Tabs({ tabs = [], active, onSelect, style }) {
  return (
    <div role="tablist" style={{ display: "flex", alignItems: "center", gap: 2, borderBottom: "1px solid var(--border-subtle)", ...style }}>
      {tabs.map((t) => {
        const id = typeof t === "string" ? t : t.id;
        const label = typeof t === "string" ? t : t.label;
        const count = typeof t === "string" ? null : t.count;
        const on = id === active;
        return (
          <button key={id} role="tab" aria-selected={on} onClick={() => onSelect && onSelect(id)}
            style={{ display: "inline-flex", alignItems: "center", gap: 6, height: 36, padding: "0 12px", border: "none", background: "none", borderBottom: "2px solid " + (on ? "var(--accent)" : "transparent"), marginBottom: -1, color: on ? "var(--text-strong)" : "var(--text-muted)", font: "inherit", fontSize: "var(--text-sm)", fontWeight: on ? "var(--weight-medium)" : "var(--weight-regular)", whiteSpace: "nowrap", cursor: "pointer", transition: "var(--transition-control)" }}>
            {label}
            {count != null ? <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--text-xs)", color: "var(--text-faint)" }}>{count}</span> : null}
          </button>
        );
      })}
    </div>
  );
}
