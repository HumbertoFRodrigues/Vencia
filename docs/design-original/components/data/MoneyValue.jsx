import React from "react";
export function MoneyValue({ amount = 0, currency = "MZN", period, size = "md", tone = "neutral", style }) {
  const sizes = { sm: "var(--text-sm)", md: "var(--text-body-size)", lg: "var(--text-h2)", metric: "var(--text-metric)" };
  const tones = { neutral: "var(--money-neutral)", in: "var(--money-in)", out: "var(--money-out)", muted: "var(--text-muted)" };
  const neg = amount < 0, abs = Math.abs(Number(amount) || 0);
  const int = Math.trunc(abs), frac = Math.round((abs - int) * 100);
  const grouped = String(int).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  const n = (neg ? "-" : "") + grouped + (frac ? "," + String(frac).padStart(2, "0") : "");
  return (
    <span style={{ display: "inline-flex", alignItems: "baseline", gap: 4, fontFamily: "var(--font-mono)", fontVariantNumeric: "tabular-nums", fontSize: sizes[size] || sizes.md, letterSpacing: size === "metric" ? "var(--text-metric-ls)" : "-0.01em", fontWeight: size === "metric" || size === "lg" ? "var(--weight-semibold)" : "var(--weight-medium)", color: tones[tone] || tones.neutral, ...style }}>
      {n}
      <span style={{ fontSize: "0.72em", fontWeight: "var(--weight-medium)", color: "var(--text-muted)" }}>{currency}{period ? " / " + period : ""}</span>
    </span>
  );
}
