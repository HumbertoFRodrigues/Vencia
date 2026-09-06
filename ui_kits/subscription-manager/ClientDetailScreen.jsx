const { PageHeader, Button, Card, ServiceCard, MoneyValue, Timeline, Icon, Tag } = window.SubscriptionManagerDesignSystem_6a702b;

function SummaryRow({ label, children }) {
  return (
    <div style={{ display: "flex", alignItems: "baseline", justifyContent: "space-between", gap: 12, padding: "8px 0", borderBottom: "1px solid var(--border-subtle)" }}>
      <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{label}</span>{children}
    </div>
  );
}

function ClientDetailScreen({ clienteId, onBack, onOpenService }) {
  const { clientesById, servicos, historico } = window.SM_DATA;
  const c = clientesById[clienteId];
  const list = servicos.filter((s) => s.clienteId === clienteId);
  const mrr = Math.round(list.reduce((t, s) => t + (s.periodo === "mês" ? s.valor : s.valor / 12), 0));
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
      <PageHeader back="Clientes" onBack={onBack} title={c.nome}
        meta={<><span style={{ display: "inline-flex", alignItems: "center", gap: 5 }}><Icon name="mail" size={14} assetsBase={window.LOGO_BASE} /><a href={"mailto:" + c.email}>{c.email}</a></span><span style={{ display: "inline-flex", alignItems: "center", gap: 5 }}><Icon name="phone" size={14} assetsBase={window.LOGO_BASE} />{c.tel}</span>{c.empresa ? <Tag>{c.empresa}</Tag> : null}<span>Cliente desde {c.desde}</span></>}
        actions={<><Button icon="pencil">Editar</Button><Button variant="primary" icon="plus">Novo serviço</Button></>} />
      <div style={{ display: "grid", gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)", gap: 12, alignItems: "start" }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          <div style={{ display: "flex", alignItems: "baseline", justifyContent: "space-between" }}>
            <h2 style={{ fontSize: "var(--text-h2)", letterSpacing: "var(--text-h2-ls)" }}>Serviços</h2>
            <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>{list.length} activos ou pendentes</span>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill,minmax(232px,1fr))", gap: 12 }}>
            {list.map((s) => (
              <ServiceCard key={s.id} name={s.nome} client={s.plano} category={s.categoria} assetsBase={window.LOGO_BASE} description={s.descricao} amount={s.valor} period={s.periodo}
                dueLabel={s.dias < 0 ? "Venceu em " + s.vencimento : s.dias <= 10 ? "Vence em " + s.dias + " dias" : "Vence: " + s.vencimento}
                status={s.status} onAction={() => onOpenService(s.id)} />
            ))}
          </div>
        </div>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          <Card title="Resumo financeiro">
            <div style={{ display: "flex", flexDirection: "column" }}>
              <SummaryRow label="Total pago"><MoneyValue amount={9700} tone="in" /></SummaryRow>
              <SummaryRow label="Total pendente"><MoneyValue amount={1500} tone="out" /></SummaryRow>
              <SummaryRow label="Receita mensal"><MoneyValue amount={mrr} /></SummaryRow>
              <SummaryRow label="Receita anual"><MoneyValue amount={mrr * 12} /></SummaryRow>
              <SummaryRow label="Último pagamento"><span className="num" style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)" }}>15/08/2026</span></SummaryRow>
              <div style={{ display: "flex", justifyContent: "space-between", paddingTop: 8 }}>
                <span style={{ fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>Próximo vencimento</span>
                <span className="num" style={{ fontSize: "var(--text-sm)", color: "var(--status-due-fg)", fontWeight: 500 }}>02/09/2026</span>
              </div>
            </div>
          </Card>
          <Card title="Histórico" subtitle="Nada é apagado"><Timeline items={historico.slice(0, 5)} /></Card>
        </div>
      </div>
    </div>
  );
}
Object.assign(window, { ClientDetailScreen });
