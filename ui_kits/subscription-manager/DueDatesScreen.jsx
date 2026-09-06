const { PageHeader, Button, Card, Tabs, DataTable, MoneyValue, StatusBadge, ServiceLogo } = window.SubscriptionManagerDesignSystem_6a702b;

function CalendarMonth({ onOpen }) {
  const { servicos, clientesById } = window.SM_DATA;
  const events = {};
  servicos.forEach((s) => { const [d, m] = s.vencimento.split("/"); if (m === "09") (events[+d] = events[+d] || []).push(s); });
  const first = 2; // 01/09/2026 is a Tuesday
  const cells = [];
  for (let i = 0; i < first; i++) cells.push(null);
  for (let d = 1; d <= 30; d++) cells.push(d);
  const dows = ["Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"];
  return (
    <div style={{ display: "grid", gridTemplateColumns: "repeat(7,1fr)", gap: 1, background: "var(--border-subtle)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-md)", overflow: "hidden" }}>
      {dows.map((d) => <div key={d} style={{ background: "var(--ink-25)", padding: "7px 9px", fontSize: "var(--text-label)", letterSpacing: "var(--text-label-ls)", textTransform: "uppercase", fontWeight: "var(--weight-semibold)", color: "var(--text-muted)" }}>{d}</div>)}
      {cells.map((d, i) => (
        <div key={i} style={{ minHeight: 78, background: d ? "var(--surface-card)" : "var(--ink-25)", padding: "6px 7px", display: "flex", flexDirection: "column", gap: 4 }}>
          {d ? <span className="num" style={{ fontSize: "var(--text-xs)", color: d === 30 ? "var(--text-strong)" : "var(--text-faint)", fontWeight: d === 30 ? 600 : 400 }}>{String(d).padStart(2, "0")}</span> : null}
          {(events[d] || []).map((s) => (
            <button key={s.id} onClick={() => onOpen(s.id)} style={{ display: "flex", alignItems: "center", gap: 5, border: "none", textAlign: "left", background: s.status === "vencido" ? "var(--status-overdue-bg)" : s.status === "a_vencer" ? "var(--status-due-bg)" : "var(--status-active-bg)", color: s.status === "vencido" ? "var(--status-overdue-fg)" : s.status === "a_vencer" ? "var(--status-due-fg)" : "var(--status-active-fg)", borderRadius: "var(--radius-xs)", padding: "3px 5px", font: "inherit", fontSize: 11, cursor: "pointer", overflow: "hidden" }}>
              <ServiceLogo name={s.nome} category={s.categoria} size={14} assetsBase={window.LOGO_BASE} />
              <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{clientesById[s.clienteId].nome.split(" ")[0]} — {s.nome}</span>
            </button>
          ))}
        </div>
      ))}
    </div>
  );
}

function DueDatesScreen({ onOpenService }) {
  const { servicos, clientesById } = window.SM_DATA;
  const [view, setView] = React.useState("lista");
  const [tab, setTab] = React.useState("todos");
  const rows = servicos.filter((s) => tab === "todos" || s.status === tab).sort((a, b) => a.dias - b.dias);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow="Setembro 2026" title="Vencimentos" actions={<>
        <Button variant={view === "lista" ? "primary" : "secondary"} icon="list" onClick={() => setView("lista")}>Lista</Button>
        <Button variant={view === "calendario" ? "primary" : "secondary"} icon="calendar" onClick={() => setView("calendario")}>Calendário</Button></>} />
      {view === "lista" ? (
        <>
          <Tabs active={tab} onSelect={setTab} tabs={[{ id: "todos", label: "Todos", count: servicos.length }, { id: "activo", label: "Activos", count: servicos.filter((s) => s.status === "activo").length }, { id: "a_vencer", label: "A vencer", count: servicos.filter((s) => s.status === "a_vencer").length }, { id: "vencido", label: "Vencidos", count: servicos.filter((s) => s.status === "vencido").length }, { id: "suspenso", label: "Suspensos", count: servicos.filter((s) => s.status === "suspenso").length }]} />
          <Card padding={0}>
            <DataTable rows={rows} onRowClick={(r) => onOpenService(r.id)} columns={[
              { key: "nome", header: "Serviço", render: (r) => <span style={{ display: "flex", alignItems: "center", gap: 9 }}><ServiceLogo name={r.nome} category={r.categoria} size={26} assetsBase={window.LOGO_BASE} /><span style={{ display: "flex", flexDirection: "column" }}><span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{r.nome}</span><span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{r.plano}</span></span></span> },
              { key: "cliente", header: "Cliente", render: (r) => clientesById[r.clienteId].nome },
              { key: "periodicidade", header: "Período" },
              { key: "vencimento", header: "Vencimento", render: (r) => <span className="num" style={{ fontSize: "var(--text-xs)" }}>{r.vencimento}</span> },
              { key: "valor", header: "Valor", align: "right", render: (r) => <MoneyValue amount={r.valor} size="sm" /> },
              { key: "status", header: "Estado", render: (r) => <StatusBadge status={r.status} size="sm" label={r.status === "a_vencer" ? "Vence em " + r.dias + " dias" : r.status === "vencido" ? "Vencido há " + Math.abs(r.dias) + " dia" : undefined} /> },
            ]} />
          </Card>
        </>
      ) : <Card title="Setembro 2026" subtitle="Clique num evento para abrir o serviço"><CalendarMonth onOpen={onOpenService} /></Card>}
    </div>
  );
}
Object.assign(window, { DueDatesScreen });
