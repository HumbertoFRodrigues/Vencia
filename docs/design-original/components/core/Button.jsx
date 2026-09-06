import React from "react";
import { Icon } from "./Icon.jsx";
const SIZES = { sm: { h: "var(--control-h-sm)", px: 10, fs: "var(--text-sm)", gap: 6 }, md: { h: "var(--control-h-md)", px: 14, fs: "var(--text-sm)", gap: 7 }, lg: { h: "var(--control-h-lg)", px: 18, fs: "var(--text-body-size)", gap: 8 } };
const VARIANTS = {
  primary: { background: "var(--accent)", color: "var(--on-accent)", border: "1px solid var(--accent)", boxShadow: "var(--shadow-sm), inset 0 1px 0 var(--inset-highlight)" },
  secondary: { background: "var(--surface-card)", color: "var(--text-strong)", border: "1px solid var(--border-default)", boxShadow: "var(--shadow-sm)" },
  ghost: { background: "transparent", color: "var(--text-body)", border: "1px solid transparent" },
  danger: { background: "var(--red-600)", color: "var(--on-danger)", border: "1px solid var(--red-600)", boxShadow: "var(--shadow-sm), inset 0 1px 0 var(--inset-highlight)" },
  quiet: { background: "var(--surface-sunken)", color: "var(--text-strong)", border: "1px solid transparent" },
};
const HOVER = { primary: "var(--accent-hover)", secondary: "var(--surface-hover)", ghost: "var(--surface-hover)", danger: "var(--red-700)", quiet: "var(--surface-active)" };
export function Button({ variant = "secondary", size = "md", icon, iconEnd, fullWidth, disabled, children, style, onMouseEnter, onMouseLeave, ...rest }) {
  const [hover, setHover] = React.useState(false);
  const s = SIZES[size] || SIZES.md, v = VARIANTS[variant] || VARIANTS.secondary;
  return (
    <button type="button" disabled={disabled}
      onMouseEnter={(e) => { setHover(true); onMouseEnter && onMouseEnter(e); }}
      onMouseLeave={(e) => { setHover(false); onMouseLeave && onMouseLeave(e); }}
      {...rest}
      style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", gap: s.gap, height: s.h, padding: `0 ${s.px}px`, width: fullWidth ? "100%" : undefined, borderRadius: "var(--radius-sm)", fontFamily: "var(--font-sans)", fontSize: s.fs, fontWeight: "var(--weight-medium)", lineHeight: 1, whiteSpace: "nowrap", cursor: disabled ? "not-allowed" : "pointer", opacity: disabled ? 0.45 : 1, transition: "var(--transition-control), transform var(--dur-instant) var(--ease-standard)", ...v, ...(hover && !disabled ? { background: HOVER[variant] } : null), ...style }}>
      {icon ? <Icon name={icon} size={size === "lg" ? 17 : 15} /> : null}
      {children}
      {iconEnd ? <Icon name={iconEnd} size={size === "lg" ? 17 : 15} /> : null}
    </button>
  );
}
