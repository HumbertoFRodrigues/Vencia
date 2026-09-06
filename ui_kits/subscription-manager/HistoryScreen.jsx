const { PageHeader, Card, Timeline, Tabs, Select, SearchInput, ServiceLogo, MoneyValue, PaymentMethod } = window.SubscriptionManagerDesignSystem_6a702b;

const EVENTS = [
  { date: "30/08/2026", title: "Pagamento recebido — Hospedagem, Maria Costa", description: "900 MZN · M-Pesa · 1 ano", kind: "pagamento", tipo: "pagamentos" },
  { date: "30/08/2026", title: "Assinatura renovada — Hospedagem, Maria Costa", description: "Novo vencimento: 28/08/2027", kind: "activado", tipo: "alteracoes" },
  { date: "29/08/2026", title: "Lembrete enviado — Hospedagem, João Silva", description: "3 dias antes · joao@email.com", kind: "lembrete", tipo: "emails" },
  { date: "28/08/2026", title: "Pagamento recebido — ChatGPT, Maria Costa", description: "500 MZN · Transferência · 1 mês", kind: "pagamento", tipo: "pagamentos" },
  { date: "26/08/2026", title: "Lembrete enviado — Email corporativo, Ana Mucavele", description: "15 dias antes · ana@mucavele.co.mz", kind: "lembrete", tipo: "emails" },
  { date: "05/08/2026", title: "Serviço suspenso — ChatGPT, Hélder Tembe", description: "Motivo: pagamento não renovado", kind: "suspenso", tipo: "alteracoes" },
  { date: "02/08/2026", title: "Serviço vencido — Manutenção do site, Carlos Manuel", description: "Aguarda renovação ou suspensão", kind: "vencido", tipo: "alteracoes" },
  { date: "01/08/2026", title: "Email de vencimento enviado — Manutenção do site", description: "carlos@cmdigital.co.mz", kind: "lembrete", tipo: "emails" },
  { date: "20/07/2026", title: "Serviço criado — Claude, Ana Mucavele", description: "700 MZN / mês · acesso individual", kind: "criado", tipo: "alteracoes" },
];

function HistoryScreen() {
  const [tab, setTab] = React.useState("todos");
  const [q, setQ] = React.useState("");
  const items = EVENTS.filter((e) => tab === "todos" || e.tipo === tab).filter((e) => (e.title + e.description).toLowerCase().includes(q.toLowerCase()));
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow="Registo imutável" title="Histórico" actions={<Select options={["Últimos 60 dias", "Este ano", "Tudo"]} style={{ width: 170 }} />} />
      <div style={{ display: "flex", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
        <Tabs active={tab} onSelect={setTab} style={{ flex: 1, minWidth: 280 }} tabs={[{ id: "todos", label: "Todos", count: EVENTS.length }, { id: "pagamentos", label: "Pagamentos", count: EVENTS.filter((e) => e.tipo === "pagamentos").length }, { id: "emails", label: "Emails", count: EVENTS.filter((e) => e.tipo === "emails").length }, { id: "alteracoes", label: "Alterações", count: EVENTS.filter((e) => e.tipo === "alteracoes").length }]} />
        <SearchInput width={250} placeholder="Cliente, serviço ou evento" shortcut={null} value={q} onChange={(e) => setQ(e.target.value)} />
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)", gap: 12, alignItems: "start" }}>
        <Card title="Eventos" subtitle="Nada é apagado — apenas acrescentado"><Timeline items={items} /></Card>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          <Card title="Últimos pagamentos">
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              {window.SM_DATA.pagamentos.slice(0, 4).map((p) => {
                const s = window.SM_DATA.servicos.find((x) => x.id === p.servicoId) || {};
                return (
                  <div key={p.id} style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <ServiceLogo name={s.nome} category={s.categoria} size={28} assetsBase={window.LOGO_BASE} />
                    <div style={{ flex: 1, minWidth: 0, display: "flex", flexDirection: "column" }}>
                      <span style={{ fontSize: "var(--text-sm)", color: "var(--text-strong)", fontWeight: "var(--weight-medium)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{s.nome}</span>
                      <PaymentMethod method={p.metodo} size={16} assetsBase={window.LOGO_BASE} />
                    </div>
                    <MoneyValue amount={p.valor} size="sm" tone="in" />
                  </div>
                );
              })}
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
Object.assign(window, { HistoryScreen });
