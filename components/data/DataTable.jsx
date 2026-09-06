import React from "react";
import { Icon } from "../core/Icon.jsx";
export function DataTable({ columns = [], rows = [], onRowClick, dense, emptyState, style }) {
  const [hover, setHover] = React.useState(-1);
  const pad = dense ? "8px 14px" : "11px 14px";
  return (
    <div style={{ width: "100%", overflowX: "auto", ...style }}>
      <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "var(--text-sm)" }}>
        <thead>
          <tr>
            {columns.map((c) => (
              <th key={c.key} style={{ padding: pad, textAlign: c.align || "left", fontSize: "var(--text-label)", letterSpacing: "var(--text-label-ls)", textTransform: "uppercase", fontWeight: "var(--weight-semibold)", color: "var(--text-muted)", borderBottom: "1px solid var(--border-subtle)", whiteSpace: "nowrap", background: "var(--table-head-bg)" }}>
                <span style={{ display: "inline-flex", alignItems: "center", gap: 4 }}>{c.header}{c.sorted ? <Icon name={c.sorted === "desc" ? "arrow-down" : "arrow-up"} size={11} /> : null}</span>
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.length === 0 ? (
            <tr><td colSpan={columns.length} style={{ padding: 0 }}>{emptyState}</td></tr>
          ) : rows.map((r, i) => (
            <tr key={r.id ?? i} onClick={onRowClick ? () => onRowClick(r, i) : undefined}
              onMouseEnter={() => setHover(i)} onMouseLeave={() => setHover(-1)}
              style={{ background: hover === i && onRowClick ? "var(--surface-hover)" : "transparent", cursor: onRowClick ? "pointer" : "default", transition: "background-color var(--dur-instant) var(--ease-standard)" }}>
              {columns.map((c) => (
                <td key={c.key} style={{ padding: pad, textAlign: c.align || "left", color: "var(--text-body)", borderBottom: i === rows.length - 1 ? "none" : "1px solid var(--border-subtle)", whiteSpace: c.wrap ? "normal" : "nowrap" }}>
                  {c.render ? c.render(r, i) : r[c.key]}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
