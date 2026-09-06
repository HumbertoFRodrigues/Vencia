const { Dialog, Button, Input, Select, Textarea, LogoSlot, ServiceLogo, Tag, Icon, PaymentMethod } = window.SubscriptionManagerDesignSystem_6a702b;
const LOGO_BASE = "../../assets/logos/";

const LIBRARY = [
  { nome: "ChatGPT", categoria: "ia" }, { nome: "Claude", categoria: "ia" }, { nome: "Canva", categoria: "software" },
  { nome: "Google Workspace", categoria: "email" }, { nome: "Microsoft 365", categoria: "software" }, { nome: "GitHub", categoria: "software" },
  { nome: "Adobe", categoria: "software" }, { nome: "Dropbox", categoria: "software" }, { nome: "Zoom", categoria: "software" },
  { nome: "Domínio .co.mz", categoria: "dominio" }, { nome: "Hospedagem Business", categoria: "hospedagem" }, { nome: "Email corporativo", categoria: "email" },
  { nome: "Manutenção de site", categoria: "manutencao" },
];

function NewSubscriptionDialog({ onClose, onDone }) {
  const { clientes } = window.SM_DATA;
  const [pick, setPick] = React.useState(LIBRARY[0].nome);
  const svc = LIBRARY.find((l) => l.nome === pick) || LIBRARY[0];
  const [metodo, setMetodo] = React.useState("mpesa");
  const [cliente, setCliente] = React.useState(clientes[0]?.id ?? "");
  const novoCliente = cliente === "__novo";
  return (
    <Dialog width={720} title="Nova assinatura" description="Escolha um serviço da biblioteca — nome, logo e categoria são preenchidos automaticamente." onClose={onClose}
      footer={<><Button onClick={onClose}>Cancelar</Button><Button variant="primary" icon="check" onClick={onDone}>Criar assinatura</Button></>}>
      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
          <span className="eyebrow">Biblioteca de serviços</span>
          <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
            {LIBRARY.map((l) => {
              const on = l.nome === pick;
              return (
                <button key={l.nome} onClick={() => setPick(l.nome)}
                  style={{ display: "inline-flex", alignItems: "center", gap: 7, padding: "5px 10px 5px 6px", background: on ? "var(--accent-soft)" : "var(--surface-card)", border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"), borderRadius: "var(--radius-pill)", color: on ? "var(--text-accent)" : "var(--text-body)", font: "inherit", fontSize: "var(--text-sm)", fontWeight: on ? "var(--weight-medium)" : "var(--weight-regular)", cursor: "pointer", whiteSpace: "nowrap", transition: "var(--transition-control)" }}>
                  <ServiceLogo name={l.nome} category={l.categoria} size={20} assetsBase={LOGO_BASE} />{l.nome}
                </button>
              );
            })}
          </div>
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 14, padding: 12, background: "var(--surface-sunken)", borderRadius: "var(--radius-md)" }}>
          <ServiceLogo name={svc.nome} category={svc.categoria} size={44} assetsBase={LOGO_BASE} />
          <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 3 }}>
            <strong style={{ fontSize: "var(--text-h3)", color: "var(--text-strong)" }}>{svc.nome}</strong>
            <span style={{ display: "flex", alignItems: "center", gap: 6 }}><Tag>{svc.categoria}</Tag><span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>preenchido pela biblioteca</span></span>
          </div>
          <LogoSlot size={44} label="Logo personalizado" hint="PNG, JPG, SVG ou WebP" />
        </div>
        <div style={{ display: "grid", gridTemplateColumns: novoCliente ? "1fr 1fr 1fr" : "1fr", gap: 12 }}>
          <Select label="Cliente" value={cliente} onChange={(e) => setCliente(e.target.value)}
            options={[...clientes.map((c) => ({ value: c.id, label: c.nome })), { value: "__novo", label: "+ Novo cliente" }]} />
          {novoCliente ? <><Input label="Nome do novo cliente" placeholder="Nome completo" /><Input label="Email" placeholder="cliente@email.com" /></> : null}
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
          <Input label="Valor" defaultValue="500" suffix="MZN" />
          <Select label="Periodicidade" options={["Mensal", "Trimestral", "Semestral", "Anual", "Única", "Personalizada"]} />
          <Input label="Data de início" defaultValue="30/08/2026" icon="calendar" />
          <Input label="Data de vencimento" defaultValue="30/09/2026" icon="calendar-clock" />
          <Input label="Duração" defaultValue="30" suffix="dias" />
        </div>
        <Textarea label="Breve descrição do serviço para este cliente" rows={2}
          defaultValue={"Acesso partilhado à conta de equipe — 2 utilizadores."}
          hint="Aparece no cartão do serviço, na página do cliente e nos emails de aviso." />
        <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
          <span className="eyebrow">Como o cliente paga</span>
          <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
            {["mpesa", "emola", "transferencia", "dinheiro", "outro"].map((m) => {
              const on = m === metodo;
              return (
                <button key={m} onClick={() => setMetodo(m)}
                  style={{ display: "inline-flex", alignItems: "center", padding: "5px 11px 5px 7px", background: on ? "var(--accent-soft)" : "var(--surface-card)", border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"), borderRadius: "var(--radius-pill)", cursor: "pointer", font: "inherit", transition: "var(--transition-control)" }}>
                  <PaymentMethod method={m} size={22} assetsBase={LOGO_BASE} />
                </button>
              );
            })}
          </div>
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 8, fontSize: "var(--text-sm)", color: "var(--text-muted)" }}>
          <Icon name="bell" size={15} />Lembretes automáticos: 30, 15, 7, 3 e 1 dia antes, no dia e após o vencimento.
        </div>
      </div>
    </Dialog>
  );
}
Object.assign(window, { NewSubscriptionDialog, SERVICE_LIBRARY: LIBRARY, LOGO_BASE });
