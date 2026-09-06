import React from "react";
import { ServiceLogo } from "./ServiceLogo.jsx";
import { MoneyValue } from "./MoneyValue.jsx";
import { StatusBadge } from "../feedback/StatusBadge.jsx";
import { Button } from "../core/Button.jsx";
export function ServiceCard({ name, client, category = "outro", logoSrc, assetsBase, description, amount, currency = "MZN", period, dueLabel, status = "activo", statusLabel, action = "Ver serviço", onAction, style }) {
  const [hover, setHover] = React.useState(false);
  return (
    <div onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{ display: "flex", flexDirection: "column", gap: 12, padding: 16, background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-lg)", boxShadow: hover ? "var(--shadow-raised)" : "var(--shadow-card)", transition: "box-shadow var(--dur-base) var(--ease-standard)", ...style }}>
      <div style={{ display: "flex", alignItems: "center", gap: 11 }}>
        <ServiceLogo name={name} category={category} src={logoSrc} size={40} assetsBase={assetsBase} />
        <div style={{ minWidth: 0, display: "flex", flexDirection: "column", gap: 1 }}>
          <strong style={{ fontSize: "var(--text-h3)", color: "var(--text-strong)", letterSpacing: "var(--text-h3-ls)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{name}</strong>
          {client ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{client}</span> : null}
        </div>
      </div>
      <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
        {description ? <p style={{ margin: 0, fontSize: "var(--text-sm)", color: "var(--text-muted)", display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>{description}</p> : null}
        <MoneyValue amount={amount} currency={currency} period={period} size="lg" />
        {dueLabel ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{dueLabel}</span> : null}
      </div>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 10, paddingTop: 12, borderTop: "1px solid var(--border-subtle)" }}>
        <StatusBadge status={status} label={statusLabel} size="sm" />
        <Button size="sm" variant="ghost" iconEnd="arrow-right" onClick={onAction}>{action}</Button>
      </div>
    </div>
  );
}
