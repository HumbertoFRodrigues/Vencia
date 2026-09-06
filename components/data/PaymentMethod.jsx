import React from "react";
import { Icon } from "../core/Icon.jsx";
export const PAYMENT_METHODS = {
  mpesa: { label: "M-Pesa", logo: "mpesa.png", bg: "#e30613" },
  emola: { label: "e-Mola", logo: "emola.png", bg: "#ee7623" },
  transferencia: { label: "Transferência", icon: "landmark", bg: "var(--surface-sunken)", fg: "var(--text-body)" },
  dinheiro: { label: "Dinheiro", icon: "banknote", bg: "var(--status-active-bg)", fg: "var(--status-active-fg)" },
  outro: { label: "Outro", icon: "credit-card", bg: "var(--surface-sunken)", fg: "var(--text-muted)" },
};
export function PaymentMethod({ method = "outro", size = 22, showLabel = true, assetsBase = "/assets/logos/", style }) {
  const m = PAYMENT_METHODS[method] || PAYMENT_METHODS.outro;
  return (
    <span style={{ display: "inline-flex", alignItems: "center", gap: 8, whiteSpace: "nowrap", ...style }}>
      <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: size, height: size, flex: "0 0 auto", borderRadius: "var(--radius-xs)", overflow: "hidden", background: m.bg, boxShadow: "0 0 0 1px rgba(20,23,29,.07)" }}>
        {m.logo ? <img src={assetsBase + m.logo} alt={m.label} style={{ width: "100%", height: "100%", objectFit: "cover" }} />
          : <Icon name={m.icon} size={Math.round(size * 0.62)} color={m.fg} />}
      </span>
      {showLabel ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-body)" }}>{m.label}</span> : null}
    </span>
  );
}
