const { Card, DataTable, PageHeader, Button, Tabs, StatusBadge, MoneyValue, SearchInput, Tag, EmptyState } = window.SubscriptionManagerDesignSystem_6a702b;

function ClientsScreen({ onOpenClient, onNew }) {
  const { clientes, servicos } = window.SM_DATA;
  const [tab, setTab] = React.useState("todos");
  const [q, setQ] = React.useState("");
  const rows = clientes
    .filter((c) => (tab === "todos" ? true : c.status === tab))
    .filter((c) => (c.nome + c.email + (c.empresa || "")).toLowerCase().includes(q.toLowerCase()));
  const count = (id) => servicos.filter((s) => s.clienteId === id).length;
  const mrr = (id) => servicos.filter((s) => s.clienteId === id).reduce((t, s) => t + (s.periodo === "mês" ? s.valor : s.valor / 12), 0);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow={clientes.length + " clientes"} title="Clientes" actions={<Button variant="primary" icon="plus" onClick={onNew}>Nova assinatura</Button>} />
      <div style={{ display: "flex", alignItems: "center", gap: 14, flexWrap: "wrap" }}>
        <Tabs active={tab} onSelect={setTab} style={{ flex: 1, minWidth: 260 }} tabs={[{ id: "todos", label: "Todos", count: clientes.length }, { id: "activo", label: "Activos", count: clientes.filter((c) => c.status === "activo").length }, { id: "inactivo", label: "Inactivos", count: clientes.filter((c) => c.status === "inactivo").length }]} />
        <SearchInput width={260} placeholder="Nome, email ou empresa" shortcut={null} value={q} onChange={(e) => setQ(e.target.value)} />
      </div>
      <Card padding={0}>
        <DataTable rows={rows} onRowClick={(r) => onOpenClient(r.id)}
          emptyState={<EmptyState icon="search-x" title="Sem resultados" description={"Nenhum cliente corresponde a “" + q + "”."} />}
          columns={[
            { key: "nome", header: "Cliente", render: (r) => (
              <span style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <span style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: 30, height: 30, borderRadius: "50%", background: "var(--surface-sunken)", color: "var(--text-body)", fontSize: "var(--text-xs)", fontWeight: "var(--weight-semibold)" }}>{r.nome.split(" ").map((w) => w[0]).slice(0, 2).join("")}</span>
                <span style={{ display: "flex", flexDirection: "column" }}>
                  <span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{r.nome}</span>
                  <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{r.email}</span>
                </span>
              </span>) },
            { key: "empresa", header: "Empresa", render: (r) => r.empresa ? <Tag>{r.empresa}</Tag> : <span style={{ color: "var(--text-faint)" }}>—</span> },
            { key: "servicos", header: "Serviços", align: "right", render: (r) => <span className="num">{count(r.id)}</span> },
            { key: "mrr", header: "Receita / mês", align: "right", render: (r) => <MoneyValue amount={Math.round(mrr(r.id))} size="sm" tone="muted" /> },
            { key: "desde", header: "Cliente desde", render: (r) => <span className="num" style={{ fontSize: "var(--text-xs)" }}>{r.desde}</span> },
            { key: "status", header: "Estado", render: (r) => <StatusBadge size="sm" status={r.status === "activo" ? "activo" : "cancelado"} label={r.status === "activo" ? "Activo" : "Inactivo"} /> },
          ]} />
      </Card>
    </div>
  );
}
Object.assign(window, { ClientsScreen });
