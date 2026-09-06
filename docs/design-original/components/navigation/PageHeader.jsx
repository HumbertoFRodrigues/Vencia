import React from "react";
import { Icon } from "../core/Icon.jsx";
export function PageHeader({ eyebrow, title, meta, actions, back, onBack, style }) {
  return (
    <div style={{ display: "flex", alignItems: "flex-end", gap: 16, flexWrap: "wrap", ...style }}>
      <div style={{ flex: 1, minWidth: 240, display: "flex", flexDirection: "column", gap: 4 }}>
        {back ? (
          <button type="button" onClick={onBack} style={{ display: "inline-flex", alignItems: "center", gap: 5, alignSelf: "flex-start", border: "none", background: "none", padding: 0, color: "var(--text-muted)", font: "inherit", fontSize: "var(--text-sm)", cursor: "pointer" }}>
            <Icon name="arrow-left" size={14} />{back}
          </button>
        ) : eyebrow ? <span style={{ fontSize: "var(--text-label)", letterSpacing: "var(--text-label-ls)", textTransform: "uppercase", fontWeight: "var(--weight-semibold)", color: "var(--text-muted)" }}>{eyebrow}</span> : null}
        <h1 style={{ fontSize: "var(--text-h1)", lineHeight: "var(--text-h1-lh)", letterSpacing: "var(--text-h1-ls)" }}>{title}</h1>
        {meta ? <div style={{ display: "flex", alignItems: "center", gap: 10, flexWrap: "wrap", fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{meta}</div> : null}
      </div>
      {actions ? <div style={{ display: "flex", alignItems: "center", gap: 8, flexWrap: "wrap" }}>{actions}</div> : null}
    </div>
  );
}
