import React from "react";
import { IconButton } from "../core/IconButton.jsx";
export function Dialog({ open = true, title, description, children, footer, width = 460, onClose, style }) {
  if (!open) return null;
  return (
    <div onClick={onClose} style={{ position: "fixed", inset: 0, zIndex: 60, display: "flex", alignItems: "flex-start", justifyContent: "center", padding: "8vh 16px", background: "var(--scrim)", backdropFilter: "blur(2px)" }}>
      <div onClick={(e) => e.stopPropagation()} role="dialog" aria-modal="true"
        style={{ width, maxWidth: "100%", maxHeight: "84vh", display: "flex", flexDirection: "column", background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-lg)", boxShadow: "var(--shadow-overlay)", animation: "none", ...style }}>
        <div style={{ display: "flex", alignItems: "flex-start", gap: 12, padding: "16px 16px 0", flex: "0 0 auto" }}>
          <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 3 }}>
            <h3 style={{ fontSize: "var(--text-h3)", letterSpacing: "var(--text-h3-ls)" }}>{title}</h3>
            {description ? <p style={{ margin: 0, fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{description}</p> : null}
          </div>
          {onClose ? <IconButton icon="x" label="Fechar" size="sm" onClick={onClose} /> : null}
        </div>
        <div style={{ padding: 16, overflowY: "auto", flex: "1 1 auto", minHeight: 0 }}>{children}</div>
        {footer ? <div style={{ display: "flex", justifyContent: "flex-end", gap: 8, padding: "12px 16px", borderTop: "1px solid var(--border-subtle)", background: "var(--table-head-bg)", borderRadius: "0 0 var(--radius-lg) var(--radius-lg)", flex: "0 0 auto" }}>{footer}</div> : null}
      </div>
    </div>
  );
}
