import React from "react";
import { Icon } from "../core/Icon.jsx";
export const NAV_ITEMS = [
  { id: "dashboard", label: "Dashboard", icon: "layout-dashboard" },
  { id: "clientes", label: "Clientes", icon: "users" },
  { id: "servicos", label: "Serviços", icon: "package" },
  { id: "vencimentos", label: "Vencimentos", icon: "calendar-clock" },
  { id: "pagamentos", label: "Pagamentos", icon: "banknote" },
  { id: "financas", label: "Finanças", icon: "chart-line" },
  { id: "historico", label: "Histórico", icon: "history" },
  { id: "configuracoes", label: "Configurações", icon: "settings" },
];
function Item({ item, active, onSelect, collapsed }) {
  const [hover, setHover] = React.useState(false);
  return (
    <button type="button" title={collapsed ? item.label : undefined} onClick={() => onSelect && onSelect(item.id)} onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{ display: "flex", alignItems: "center", gap: 10, width: "100%", height: 34, padding: collapsed ? 0 : "0 10px", justifyContent: collapsed ? "center" : "flex-start", border: "none", borderRadius: "var(--radius-sm)", background: active ? "var(--surface-card)" : hover ? "var(--nav-hover)" : "transparent", boxShadow: active ? "var(--shadow-sm)" : "none", color: active ? "var(--text-strong)" : "var(--text-body)", font: "inherit", fontSize: "var(--text-sm)", fontWeight: active ? "var(--weight-medium)" : "var(--weight-regular)", whiteSpace: "nowrap", cursor: "pointer", textAlign: "left", transition: "var(--transition-control)" }}>
      <Icon name={item.icon} size={16} color={active ? "var(--accent)" : "var(--text-muted)"} style={{ flex: "0 0 auto" }} />
      {collapsed ? null : <span style={{ flex: 1 }}>{item.label}</span>}
      {!collapsed && item.badge ? <span style={{ fontFamily: "var(--font-mono)", fontSize: "var(--text-xs)", color: "var(--status-due-fg)", background: "var(--status-due-bg)", borderRadius: "var(--radius-pill)", padding: "1px 6px" }}>{item.badge}</span> : null}
    </button>
  );
}
export function SidebarNav({ items = NAV_ITEMS, active = "dashboard", onSelect, brand = "Vencia", logoSrc = "/assets/logo-mark.png", footer, collapsed = false, onToggleCollapse, style }) {
  return (
    <nav style={{ display: "flex", flexDirection: "column", gap: 14, width: collapsed ? "var(--sidebar-w-collapsed, 60px)" : "var(--sidebar-w)", flex: "0 0 auto", height: "100%", padding: collapsed ? "14px 8px" : "14px 12px", background: "var(--surface-app)", borderRight: "1px solid var(--border-subtle)", transition: "width var(--transition-control, 0.15s ease)", ...style }}>
      <div style={{ display: "flex", alignItems: "center", gap: 9, padding: "4px 6px", justifyContent: collapsed ? "center" : "flex-start" }}>
        <img src={logoSrc} alt="" style={{ width: 26, height: 26, borderRadius: "var(--radius-sm)", objectFit: "contain", flex: "0 0 auto" }} />
        {collapsed ? null : <span style={{ flex: 1, fontSize: "var(--text-sm)", fontWeight: "var(--weight-semibold)", color: "var(--text-strong)", letterSpacing: "-0.015em", whiteSpace: "nowrap", overflow: "hidden" }}>{brand}</span>}
      </div>
      <div style={{ display: "flex", flexDirection: "column", gap: 2 }}>
        {items.map((it) => <Item key={it.id} item={it} active={it.id === active} onSelect={onSelect} collapsed={collapsed} />)}
      </div>
      <div style={{ marginTop: "auto", display: "flex", flexDirection: "column", gap: 8 }}>
        {collapsed ? null : footer}
        {onToggleCollapse ? (
          <button type="button" title={collapsed ? "Expandir menu" : "Ocultar menu"} onClick={onToggleCollapse}
            style={{ display: "flex", alignItems: "center", justifyContent: collapsed ? "center" : "flex-start", gap: 8, height: 30, padding: collapsed ? 0 : "0 10px", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-sm)", background: "var(--surface-card)", color: "var(--text-muted)", font: "inherit", fontSize: "var(--text-xs)", cursor: "pointer" }}>
            <Icon name={collapsed ? "panel-left-open" : "panel-left-close"} size={15} />
            {collapsed ? null : <span>Ocultar menu</span>}
          </button>
        ) : null}
      </div>
    </nav>
  );
}
