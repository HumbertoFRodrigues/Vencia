import React from "react";
export function Card({ title, subtitle, action, padding = 16, children, interactive, style }) {
  const [hover, setHover] = React.useState(false);
  return (
    <section onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{ display: "flex", flexDirection: "column", background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-lg)", boxShadow: interactive && hover ? "var(--shadow-raised)" : "var(--shadow-card)", transition: "box-shadow var(--dur-base) var(--ease-standard)", overflow: "hidden", ...style }}>
      {title || action ? (
        <header style={{ display: "flex", alignItems: "center", gap: 12, padding: "14px 16px", borderBottom: "1px solid var(--border-subtle)" }}>
          <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 2 }}>
            <h3 style={{ fontSize: "var(--text-h3)", letterSpacing: "var(--text-h3-ls)" }}>{title}</h3>
            {subtitle ? <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{subtitle}</span> : null}
          </div>
          {action}
        </header>
      ) : null}
      <div style={{ padding, flex: 1, minHeight: 0 }}>{children}</div>
    </section>
  );
}
