import React from "react";
import { Icon } from "../core/Icon.jsx";
const KIND = { criado: ["plus", "var(--text-muted)"], pagamento: ["banknote", "var(--green-600)"], activado: ["circle-check", "var(--green-600)"], lembrete: ["mail", "var(--accent)"], vencido: ["circle-alert", "var(--red-600)"], suspenso: ["pause", "var(--ink-800)"], alterado: ["pencil", "var(--text-muted)"], cancelado: ["circle-slash", "var(--text-faint)"] };
export function Timeline({ items = [], style }) {
  return (
    <ol style={{ listStyle: "none", margin: 0, padding: 0, display: "flex", flexDirection: "column", ...style }}>
      {items.map((it, i) => {
        const [icon, color] = KIND[it.kind] || KIND.alterado;
        const last = i === items.length - 1;
        return (
          <li key={i} style={{ display: "grid", gridTemplateColumns: "26px 1fr", gap: 10 }}>
            <div style={{ display: "flex", flexDirection: "column", alignItems: "center" }}>
              <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 26, height: 26, borderRadius: "50%", background: "var(--surface-card)", border: "1px solid var(--border-subtle)" }}>
                <Icon name={icon} size={13} color={color} />
              </span>
              {!last ? <span style={{ flex: 1, width: 1, background: "var(--border-subtle)", minHeight: 14 }} /> : null}
            </div>
            <div style={{ paddingBottom: last ? 0 : 16, display: "flex", flexDirection: "column", gap: 2 }}>
              <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--text-xs)", color: "var(--text-faint)" }}>{it.date}</span>
              <span style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{it.title}</span>
              {it.description ? <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{it.description}</span> : null}
            </div>
          </li>
        );
      })}
    </ol>
  );
}
