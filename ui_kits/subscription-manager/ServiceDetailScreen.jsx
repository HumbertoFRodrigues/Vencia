const { PageHeader, Button, Card, ServiceLogo, MoneyValue, StatusBadge, Timeline, AlertBanner, Dialog, Input, Select, Icon, Tag, Checkbox, PaymentMethod, ServiceCard } = window.SubscriptionManagerDesignSystem_6a702b;

function Field({ label, children }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 3 }}>
      <span className="eyebrow">{label}</span>
      <span style={{ fontSize: "var(--text-body-size)", color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{children}</span>
    </div>
  );
}

function RenewDialog({ servico, onClose, onDone }) {
  const [metodo, setMetodo] = React.useState("mpesa");
  return (
    <Dialog title="Renovar serviço" description={servico.nome + " — período " + servico.periodicidade.toLowerCase()} onClose={onClose} width={440}
      footer={<><Button onClick={onClose}>Cancelar</Button><Button variant="primary" icon="check" onClick={onDone}>Confirmar renovação</Button></>}>
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
        <Input label="Valor pago" defaultValue={String(servico.valor)} suffix="MZN" />
        <Input label="Data do pagamento" defaultValue="30/08/2026" icon="calendar" />
        <Select label="Período" options={["1 mês", "3 meses", "6 meses", "1 ano"]} defaultValue={servico.periodo === "mês" ? "1 mês" : "1 ano"} />
        <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
          <span style={{ fontSize: "var(--text-sm)", fontWeight: "var(--weight-medium)", color: "var(--text-strong)" }}>Método</span>
          <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
            {["mpesa", "emola", "transferencia", "dinheiro"].map((m) => (
              <button key={m} onClick={() => setMetodo(m)} title={m}
                style={{ display: "inline-flex", alignItems: "center", padding: 4, background: metodo === m ? "var(--accent-soft)" : "var(--surface-card)", border: "1px solid " + (metodo === m ? "var(--accent-soft-border)" : "var(--border-subtle)"), borderRadius: "var(--radius-sm)", cursor: "pointer" }}>
                <PaymentMethod method={m} size={22} showLabel={false} assetsBase={window.LOGO_BASE} />
              </button>
            ))}
          </div>
        </div>
        <Input label="Observações" placeholder="Opcional" style={{ gridColumn: "1 / -1" }} />
      </div>
      <div style={{ marginTop: 12, padding: "10px 12px", background: "var(--surface-sunken)", borderRadius: "var(--radius-sm)", display: "flex", alignItems: "center", gap: 8, fontSize: "var(--text-sm)", color: "var(--text-body)" }}>
        <Icon name="calendar-check" size={15} color="var(--text-muted)" />
        Novo vencimento calculado: <strong className="num" style={{ color: "var(--text-strong)" }}>{servico.periodo === "mês" ? "01/10/2026" : "02/09/2027"}</strong>
      </div>
    </Dialog>
  );
}

function SuspendDialog({ onClose, onDone }) {
  return (
    <Dialog title="Suspender serviço" description="O histórico é mantido; os lembretes param." onClose={onClose} width={420}
      footer={<><Button onClick={onClose}>Cancelar</Button><Button variant="danger" icon="pause" onClick={onDone}>Suspender acesso</Button></>}>
      <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        <Select label="Motivo" options={["Pagamento não renovado", "Pedido do cliente", "Serviço substituído", "Outro"]} />
        <Input label="Data da suspensão" defaultValue="30/08/2026" icon="calendar" />
        <Input label="Observação" placeholder="Registro interno" />
        <Checkbox label="Enviar email de aviso ao cliente" checked description="Utiliza o template “Serviço terminado”." />
      </div>
    </Dialog>
  );
}

function ServiceDetailScreen({ servicoId, onBack, onToast }) {
  const { servicos, clientesById, historico } = window.SM_DATA;
  const s = servicos.find((x) => x.id === servicoId) || servicos[0];
  const c = clientesById[s.clienteId];
  const [dialog, setDialog] = React.useState(null);
  const [status, setStatus] = React.useState(s.status);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
      <PageHeader back={c.nome} onBack={onBack}
        title={<span style={{ display: "inline-flex", alignItems: "center", gap: 12 }}><ServiceLogo name={s.nome} category={s.categoria} size={38} assetsBase={window.LOGO_BASE} />{s.nome}</span>}
        meta={<><Tag>{s.plano}</Tag><Tag tone="outline">{s.periodicidade}</Tag><span>{c.email}</span></>}
        actions={<><Button variant="primary" icon="refresh-cw" onClick={() => setDialog("renew")}>Renovar</Button><Button icon="banknote">Registrar pagamento</Button><Button icon="pencil">Editar</Button><Button variant="danger" icon="pause" onClick={() => setDialog("suspend")}>Suspender</Button></>} />
      {status === "vencido" ? <AlertBanner tone="danger" title="Este acesso terminou">Terminou em {s.vencimento}. É necessário renovar ou suspender.</AlertBanner> : null}
      {status === "suspenso" ? <AlertBanner tone="warning" title="Serviço suspenso em 30/08/2026">Motivo: pagamento não renovado. Os lembretes estão parados.</AlertBanner> : null}
      {status === "a_vencer" ? <AlertBanner tone="warning" title={"Vence em " + s.dias + " dias"}>Próximo aviso automático: 3 dias antes do vencimento.</AlertBanner> : null}
      <div style={{ display: "grid", gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)", gap: 12, alignItems: "start" }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          <Card title="Assinatura" subtitle={s.descricao}>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(140px,1fr))", gap: 18 }}>
              <Field label="Cliente">{c.nome}</Field>
              <Field label="Categoria">{s.categoria}</Field>
              <Field label="Método habitual"><PaymentMethod method="mpesa" size={20} assetsBase={window.LOGO_BASE} /></Field>
              <Field label="Valor"><MoneyValue amount={s.valor} period={s.periodo} /></Field>
              <Field label="Período">{s.periodicidade}</Field>
              <Field label="Início"><span className="num">{s.inicio}</span></Field>
              <Field label="Vencimento"><span className="num">{s.vencimento}</span></Field>
              <Field label="Próximo aviso"><span className="num">25/08/2027</span></Field>
              <div style={{ display: "flex", flexDirection: "column", gap: 5 }}><span className="eyebrow">Estado</span><StatusBadge status={status} /></div>
            </div>
          </Card>
          <Card title="Lembretes" subtitle="Intervalos activos para este serviço">
            <div style={{ display: "flex", gap: 16, flexWrap: "wrap" }}>
              {["30 dias antes", "15 dias antes", "7 dias antes", "3 dias antes", "1 dia antes", "No dia", "Após vencimento"].map((l, i) => <Checkbox key={l} label={l} checked={i !== 4} />)}
            </div>
          </Card>
        </div>
        <Card title="Histórico do serviço" subtitle="Registro imutável"><Timeline items={historico} /></Card>
      </div>
      {dialog === "renew" ? <RenewDialog servico={s} onClose={() => setDialog(null)} onDone={() => { setStatus("activo"); setDialog(null); onToast({ title: "Renovação registrada", body: String(s.valor).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " MZN — " + s.nome }); }} /> : null}
      {dialog === "suspend" ? <SuspendDialog onClose={() => setDialog(null)} onDone={() => { setStatus("suspenso"); setDialog(null); onToast({ title: "Serviço suspenso", body: s.nome + " — " + c.nome, tone: "danger" }); }} /> : null}
    </div>
  );
}
Object.assign(window, { ServiceDetailScreen });
