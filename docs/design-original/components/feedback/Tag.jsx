import React from "react";
export function Tag({ children, tone = "neutral", icon, style }) {
  const tones = { neutral: ["var(--surface-sunken)", "var(--text-body)"], accent: ["var(--accent-soft)", "var(--text-accent)"], outline: ["transparent", "var(--text-muted)"] };
  const [bg, fg] = tones[tone] || tones.neutral;
  return (
    <span style={{ display: "inline-flex", alignItems: "center", gap: 5, height: 22, padding: "0 8px", borderRadius: "var(--radius-xs)", background: bg, color: fg, border: tone === "outline" ? "1px solid var(--border-subtle)" : "1px solid transparent", fontSize: "var(--text-xs)", fontWeight: "var(--weight-medium)", whiteSpace: "nowrap", ...style }}>{icon}{children}</span>
  );
}
