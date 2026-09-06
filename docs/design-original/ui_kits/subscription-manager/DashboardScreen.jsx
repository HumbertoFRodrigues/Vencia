const { StatCard, Card, DataTable, MoneyValue, StatusBadge, ServiceLogo, AlertBanner, Button, PageHeader, Icon } = window.SubscriptionManagerDesignSystem_6a702b;

function AttentionList({ onOpen }) {
  const t = window.SM_DATA.totais;
  const items = [
    { icon: "calendar-clock", text: t.aVencer + " serviços vencem em breve", tone: "var(--status-due-fg)" },
    { icon: "circle-alert", text: t.nEmAtraso + (t.nEmAtraso === 1 ? " serviço está vencido" : " serviços estão vencidos"), tone: "var(--status-overdue-fg)" },
    { icon: "banknote", text: t.nAReceber + " pagamentos estão pendentes", tone: "var(--status-due-fg)" },
    { icon: "pause", text: "1 acesso aguarda suspensão", tone: "var(--text-body)" },
  ];
  return (
    <div style={{ display: "flex", flexDirection: "column" }}>
      {items.map((it, i) => (
        <button key={i} onClick={onOpen} style={{ display: "flex", alignItems: "center", gap: 9, padding: "10px 16px", border: "none", borderTop: i ? "1px solid var(--border-subtle)" : "none", background: "none", font: "inherit", fontSize: "var(--text-sm)", color: "var(--text-body)", cursor: "pointer", textAlign: "left" }}>
          <Icon name={it.icon} size={15} color={it.tone} />
          <span style={{ flex: 1 }}>{it.text}</span>
          <Icon name="chevron-right" size={14} color="var(--text-faint)" />
        </button>
      ))}
    </div>
  );
}

function DashboardScreen({ onOpenService, onNew }) {
  const { servicos, clientesById, totais } = window.SM_DATA;
  const proximos = servicos.filter((s) => s.dias >= -30 && s.status !== "suspenso" && s.status !== "cancelado").sort((a, b) => a.dias - b.dias).slice(0, 6);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
      <PageHeader eyebrow={totais.mes} title="Dashboard" actions={<><Button icon="download">Exportar</Button><Button variant="primary" icon="plus" onClick={onNew}>Nova assinatura</Button></>} />
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(178px,1fr))", gap: 12 }}>
        <StatCard label="Receita deste mês" value={totais.entradas} currency="MZN" icon="wallet" tone="in" footnote={totais.nPagamentos + " pagamentos"} />
        <StatCard label="Receita esperada" value={totais.esperada} currency="MZN" icon="target" footnote={"faltam " + String(totais.aReceber).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " MZN"} />
        <StatCard label="Em atraso" value={totais.emAtraso} currency="MZN" tone="out" icon="circle-alert" footnote={totais.nEmAtraso + (totais.nEmAtraso === 1 ? " serviço" : " serviços")} />
        <StatCard label="Clientes activos" value={totais.clientesActivos} icon="users" />
        <StatCard label="Serviços activos" value={totais.servicosActivos} icon="package" />
        <StatCard label="Vencendo em breve" value={totais.aVencer} tone="due" icon="calendar-clock" footnote="próximos 10 dias" />
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "minmax(0,1.85fr) minmax(280px,1fr)", gap: 12, alignItems: "start" }}>
        <Card title="Próximos vencimentos" subtitle="Ordenado pela data mais próxima" padding={0} action={<Button size="sm" iconEnd="arrow-right">Ver todos</Button>}>
          <DataTable rows={proximos} onRowClick={(r) => onOpenService(r.id)}
            columns={[
              { key: "nome", header: "Serviço", render: (r) => (
                <span style={{ display: "flex", alignItems: "center", gap: 9 }}>
                  <ServiceLogo name={r.nome} category={r.categoria} size={26} assetsBase={window.LOGO_BASE} />
                  <span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{r.nome}</span>
                </span>) },
              { key: "cliente", header: "Cliente", render: (r) => clientesById[r.clienteId].nome },
              { key: "vencimento", header: "Vencimento", render: (r) => <span className="num" style={{ fontSize: "var(--text-xs)" }}>{r.vencimento}</span> },
              { key: "valor", header: "Valor", align: "right", render: (r) => <MoneyValue amount={r.valor} size="sm" /> },
              { key: "status", header: "Estado", render: (r) => <StatusBadge status={r.status} size="sm" label={r.dias < 0 ? "Vencido" : r.dias <= 7 ? `Vence em ${r.dias} dias` : undefined} /> },
            ]} />
        </Card>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          <AlertBanner tone="warning" title="Requer atenção">Manutenção do site — Carlos Manuel está vencida há 1 dia.</AlertBanner>
          <Card title="Alertas" padding={0}><AttentionList onOpen={() => onOpenService("s7")} /></Card>
          <Card title="Receita recorrente">
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ display: "flex", flexDirection: "column", gap: 2 }}><span className="eyebrow">MRR</span><MoneyValue amount={totais.mrr} size="lg" /></div>
              <div style={{ display: "flex", flexDirection: "column", gap: 2, paddingTop: 12, borderTop: "1px solid var(--border-subtle)" }}><span className="eyebrow">ARR</span><MoneyValue amount={totais.arr} size="lg" /></div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
Object.assign(window, { DashboardScreen });
