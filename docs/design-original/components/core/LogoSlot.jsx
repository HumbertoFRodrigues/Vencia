import React from "react";
import { Icon } from "./Icon.jsx";
export function LogoSlot({ src, size = 64, label = "Adicionar logo personalizado", hint = "PNG, JPG, SVG ou WebP", onClick, style }) {
  const [hover, setHover] = React.useState(false);
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 12, ...style }}>
      <button type="button" onClick={onClick} onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
        style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: size, height: size, flex: "0 0 auto", padding: src ? Math.round(size * 0.1) : 0, background: hover ? "var(--surface-hover)" : "var(--surface-card)", border: src ? "1px solid var(--border-subtle)" : "1px dashed " + (hover ? "var(--border-accent)" : "var(--border-default)"), borderRadius: "var(--radius-md)", cursor: "pointer", transition: "var(--transition-control)" }}>
        {src ? <img src={src} alt="" style={{ width: "100%", height: "100%", objectFit: "contain" }} /> : <Icon name="image-plus" size={Math.round(size * 0.32)} color={hover ? "var(--accent)" : "var(--text-faint)"} />}
      </button>
      <div style={{ display: "flex", flexDirection: "column", gap: 2 }}>
        <span style={{ fontSize: "var(--text-sm)", fontWeight: "var(--weight-medium)", color: "var(--text-strong)" }}>{label}</span>
        <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{hint}</span>
      </div>
    </div>
  );
}
