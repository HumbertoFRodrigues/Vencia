import React from "react";
export const STATUS = {
  activo:    { label: "Activo",    fg: "var(--status-active-fg)",    bg: "var(--status-active-bg)",    dot: "var(--status-active-dot)" },
  a_vencer:  { label: "A vencer",  fg: "var(--status-due-fg)",       bg: "var(--status-due-bg)",       dot: "var(--status-due-dot)" },
  vencido:   { label: "Vencido",   fg: "var(--status-overdue-fg)",   bg: "var(--status-overdue-bg)",   dot: "var(--status-overdue-dot)" },
  suspenso:  { label: "Suspenso",  fg: "var(--status-suspended-fg)", bg: "var(--status-suspended-bg)", dot: "var(--status-suspended-dot)" },
  cancelado: { label: "Cancelado", fg: "var(--status-cancelled-fg)", bg: "var(--status-cancelled-bg)", dot: "var(--status-cancelled-dot)" },
};
export function StatusBadge({ status = "activo", label, size = "md", style }) {
  const s = STATUS[status] || STATUS.activo;
  const sm = size === "sm";
  return (
    <span style={{ display: "inline-flex", alignItems: "center", gap: 6, height: sm ? 20 : 24, padding: sm ? "0 7px" : "0 9px", borderRadius: "var(--radius-pill)", background: s.bg, color: s.fg, fontSize: sm ? "var(--text-xs)" : "var(--text-sm)", fontWeight: "var(--weight-medium)", whiteSpace: "nowrap", ...style }}>
      <span style={{ width: 6, height: 6, borderRadius: "50%", background: s.dot, flex: "0 0 auto" }} />
      {label || s.label}
    </span>
  );
}
