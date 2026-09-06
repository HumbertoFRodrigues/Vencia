import React from "react";
import { SearchInput } from "../core/SearchInput.jsx";
import { IconButton } from "../core/IconButton.jsx";
import { ThemeToggle } from "../core/ThemeToggle.jsx";
import { Icon } from "../core/Icon.jsx";
export function TopBar({ title, search = true, onSearch, actions, alertCount, themeToggle = true, avatarSrc, style }) {
  return (
    <header style={{ display: "flex", alignItems: "center", gap: 12, height: "var(--topbar-h)", flex: "0 0 auto", padding: "0 var(--gutter)", background: "var(--topbar-bg)", backdropFilter: "blur(8px)", borderBottom: "1px solid var(--border-subtle)", ...style }}>
      {title ? <strong style={{ fontSize: "var(--text-h3)", color: "var(--text-strong)", letterSpacing: "var(--text-h3-ls)" }}>{title}</strong> : null}
      {search ? <SearchInput width={400} onChange={onSearch ? (e) => onSearch(e.target.value) : undefined} style={{ marginLeft: title ? 8 : 0 }} /> : null}
      <div style={{ marginLeft: "auto", display: "flex", alignItems: "center", gap: 6 }}>
        {actions}
        {themeToggle ? <ThemeToggle /> : null}
        <div style={{ position: "relative", display: "flex" }}>
          <IconButton icon="bell" label="Notificações" />
          {alertCount ? <span style={{ position: "absolute", top: 4, right: 4, minWidth: 15, height: 15, padding: "0 3px", display: "flex", alignItems: "center", justifyContent: "center", borderRadius: "var(--radius-pill)", background: "var(--red-600)", color: "var(--on-danger)", fontFamily: "var(--font-mono)", fontSize: 9, fontWeight: "var(--weight-semibold)" }}>{alertCount}</span> : null}
        </div>
        <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 28, height: 28, borderRadius: "50%", background: "var(--surface-sunken)", color: "var(--text-muted)", overflow: "hidden", flex: "0 0 auto" }}>
          {avatarSrc ? <img src={avatarSrc} alt="" style={{ width: "100%", height: "100%", objectFit: "cover" }} /> : <Icon name="circle-user-round" size={20} />}
        </span>
      </div>
    </header>
  );
}
