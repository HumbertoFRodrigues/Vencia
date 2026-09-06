const { PageHeader, Button, Card, ServiceCard, Tabs, SearchInput, Select, EmptyState, Tag, ServiceLogo } = window.SubscriptionManagerDesignSystem_6a702b;

function ServicesScreen({ onOpenService, onNew }) {
  const { servicos, clientesById } = window.SM_DATA;
  const [tab, setTab] = React.useState("todos");
  const [cat, setCat] = React.useState("Todas as categorias");
  const [q, setQ] = React.useState("");
  const rows = servicos
    .filter((s) => tab === "todos" || s.status === tab)
    .filter((s) => cat.startsWith("Todas") || s.categoria === cat.toLowerCase())
    .filter((s) => (s.nome + s.plano + clientesById[s.clienteId].nome).toLowerCase().includes(q.toLowerCase()));
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow={servicos.length + " serviços"} title="Serviços" actions={<><Button icon="library-big">Biblioteca</Button><Button variant="primary" icon="plus" onClick={onNew}>Nova assinatura</Button></>} />
      <div style={{ display: "flex", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
        <Tabs active={tab} onSelect={setTab} style={{ flex: 1, minWidth: 300 }} tabs={[{ id: "todos", label: "Todos", count: servicos.length }, { id: "activo", label: "Activos", count: servicos.filter((s) => s.status === "activo").length }, { id: "a_vencer", label: "A vencer", count: servicos.filter((s) => s.status === "a_vencer").length }, { id: "vencido", label: "Vencidos", count: servicos.filter((s) => s.status === "vencido").length }, { id: "suspenso", label: "Suspensos", count: servicos.filter((s) => s.status === "suspenso").length }]} />
        <Select options={["Todas as categorias", "Dominio", "Hospedagem", "Email", "IA", "Software", "Manutencao"]} value={cat} onChange={(e) => setCat(e.target.value)} style={{ width: 190 }} />
        <SearchInput width={230} placeholder="Serviço ou cliente" shortcut={null} value={q} onChange={(e) => setQ(e.target.value)} />
      </div>
      {rows.length === 0 ? <Card><EmptyState icon="search-x" title="Sem serviços" description="Nenhum serviço corresponde aos filtros aplicados." /></Card> : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill,minmax(268px,1fr))", gap: 12 }}>
          {rows.map((s) => (
            <div key={s.id} style={{ display: "flex", flexDirection: "column" }}>
              <ServiceCard name={s.nome} client={clientesById[s.clienteId].nome} category={s.categoria} assetsBase={window.LOGO_BASE} description={s.descricao} amount={s.valor} period={s.periodo}
                dueLabel={s.dias < 0 ? "Venceu em " + s.vencimento : s.dias <= 10 ? "Vence em " + s.dias + " dias" : "Vence: " + s.vencimento}
                status={s.status} onAction={() => onOpenService(s.id)} style={{ flex: 1 }} />
            </div>
          ))}
        </div>
      )}
      <Card title="Biblioteca de serviços" subtitle="Serviços conhecidos com logo pronto — selecione um ao criar a assinatura" action={<Button size="sm" icon="plus">Adicionar serviço</Button>}>
        <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
          {window.SERVICE_LIBRARY.map((l) => (
            <span key={l.nome} style={{ display: "inline-flex", alignItems: "center", gap: 8, padding: "6px 12px 6px 8px", background: "var(--surface-sunken)", borderRadius: "var(--radius-pill)", fontSize: "var(--text-sm)", color: "var(--text-body)", whiteSpace: "nowrap" }}>
              <ServiceLogo name={l.nome} category={l.categoria} size={22} assetsBase={window.LOGO_BASE} />{l.nome}
            </span>
          ))}
        </div>
      </Card>
    </div>
  );
}
Object.assign(window, { ServicesScreen });
