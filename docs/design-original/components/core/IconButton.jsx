import React from "react";
import { Icon } from "./Icon.jsx";
const SZ = { sm: 28, md: 36, lg: 44 };
export function IconButton({ icon, size = "md", variant = "ghost", label, disabled, style, ...rest }) {
  const [hover, setHover] = React.useState(false);
  const d = SZ[size] || SZ.md;
  const base = variant === "secondary"
    ? { background: "var(--surface-card)", border: "1px solid var(--border-default)", boxShadow: "var(--shadow-sm)" }
    : { background: "transparent", border: "1px solid transparent" };
  return (
    <button type="button" aria-label={label} title={label} disabled={disabled}
      onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)} {...rest}
      style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: d, height: d, borderRadius: "var(--radius-sm)", color: "var(--text-muted)", cursor: disabled ? "not-allowed" : "pointer", opacity: disabled ? 0.45 : 1, transition: "var(--transition-control)", ...base, ...(hover && !disabled ? { background: "var(--surface-hover)", color: "var(--text-strong)" } : null), ...style }}>
      <Icon name={icon} size={size === "sm" ? 15 : 17} />
    </button>
  );
}
