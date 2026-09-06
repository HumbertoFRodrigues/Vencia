import React from "react";
import { Icon } from "../core/Icon.jsx";
import { MoneyValue } from "./MoneyValue.jsx";
export function StatCard({ label, value, currency, period, delta, deltaTone = "neutral", icon, tone = "neutral", footnote, onClick, style }) {
  const [hover, setHover] = React.useState(false);
  const accents = { neutral: "var(--text-strong)", in: "var(--money-in)", out: "var(--money-out)", due: "var(--status-due-fg)" };
  return (
    <div onClick={onClick} onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{ display: "flex", flexDirection: "column", gap: 8, padding: 16, background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-lg)", boxShadow: onClick && hover ? "var(--shadow-raised)" : "var(--shadow-card)", cursor: onClick ? "pointer" : "default", transition: "box-shadow var(--dur-base) var(--ease-standard)", ...style }}>
      <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
        <span style={{ flex: 1, fontSize: "var(--text-label)", letterSpacing: "var(--text-label-ls)", textTransform: "uppercase", fontWeight: "var(--weight-semibold)", color: "var(--text-muted)" }}>{label}</span>
        {icon ? <Icon name={icon} size={15} color="var(--text-faint)" /> : null}
      </div>
      {currency ? <MoneyValue amount={value} currency={currency} period={period} size="metric" tone={tone === "neutral" ? "neutral" : tone === "out" ? "out" : tone === "in" ? "in" : "neutral"} />
        : <span style={{ fontFamily: "var(--font-mono)", fontVariantNumeric: "tabular-nums", fontSize: "var(--text-metric)", lineHeight: "var(--text-metric-lh)", letterSpacing: "var(--text-metric-ls)", fontWeight: "var(--weight-semibold)", color: accents[tone] || accents.neutral }}>{value}</span>}
      {delta || footnote ? (
        <div style={{ display: "flex", alignItems: "center", gap: 6, fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>
          {delta ? <span style={{ color: deltaTone === "in" ? "var(--money-in)" : deltaTone === "out" ? "var(--money-out)" : "var(--text-muted)", fontWeight: "var(--weight-medium)" }}>{delta}</span> : null}
          {footnote}
        </div>
      ) : null}
    </div>
  );
}
