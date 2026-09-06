import React from "react";
import { Icon } from "./Icon.jsx";
export function SearchInput({ placeholder = "Pesquisar cliente, serviço, domínio…", shortcut = "/", width = 380, style, ...rest }) {
  const [focus, setFocus] = React.useState(false);
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 8, width, height: "var(--control-h-md)", padding: "0 10px", background: focus ? "var(--surface-card)" : "var(--surface-sunken)", border: "1px solid " + (focus ? "var(--border-focus)" : "transparent"), borderRadius: "var(--radius-sm)", boxShadow: focus ? "var(--ring)" : "none", transition: "var(--transition-control)", ...style }}>
      <Icon name="search" size={15} color="var(--text-faint)" />
      <input placeholder={placeholder} onFocus={() => setFocus(true)} onBlur={() => setFocus(false)} {...rest}
        style={{ flex: 1, minWidth: 0, border: "none", outline: "none", background: "transparent", font: "inherit", fontSize: "var(--text-sm)", color: "var(--text-strong)" }} />
      {shortcut ? <kbd style={{ fontFamily: "var(--font-mono)", fontSize: "var(--text-xs)", color: "var(--text-faint)", background: "var(--surface-card)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-xs)", padding: "1px 5px" }}>{shortcut}</kbd> : null}
    </div>
  );
}
