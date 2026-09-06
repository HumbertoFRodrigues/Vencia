const { PageHeader, Card, Button, Input, Select, Switch, Checkbox, Tabs, ServiceLogo, Tag, IconButton, DataTable, LogoSlot, Textarea, PaymentMethod } = window.SubscriptionManagerDesignSystem_6a702b;

const LIBRARY = window.SERVICE_LIBRARY;
const DESCRICOES = {
  "ChatGPT": "Acesso a conta de equipe ou individual, cobrado mensalmente.",
  "Claude": "Acesso individual, cobrado mensalmente.",
  "Canva": "Licença Pro para materiais gráficos.",
  "Google Workspace": "Email profissional e Drive por utilizador.",
  "Microsoft 365": "Office e email corporativo por utilizador.",
  "GitHub": "Repositórios privados e CI.",
  "Adobe": "Creative Cloud, licença única ou equipe.",
  "Dropbox": "Armazenamento partilhado com o cliente.",
  "Zoom": "Reuniões sem limite de tempo.",
  "Domínio .co.mz": "Registo e renovação anual, DNS gerido.",
  "Hospedagem Business": "Alojamento SSD com backups semanais.",
  "Email corporativo": "Contas de email no domínio do cliente.",
  "Manutenção de site": "Actualizações, backups e horas de alteração por mês.",
};

function SettingsScreen() {
  const [tab, setTab] = React.useState("biblioteca");
  const [auto, setAuto] = React.useState(true);
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow="Administração" title="Configurações" />
      <Tabs active={tab} onSelect={setTab} tabs={[{ id: "biblioteca", label: "Biblioteca de Serviços", count: LIBRARY.length }, { id: "lembretes", label: "Lembretes" }, { id: "empresa", label: "Empresa" }]} />
      {tab === "biblioteca" ? (
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        <Card title="Adicionar serviço à biblioteca" subtitle="Logo, categoria e descrição ficam guardados para reutilizar em qualquer cliente">
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, alignItems: "start" }}>
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <Input label="Nome do serviço" placeholder="Ex.: Notion" />
              <Select label="Categoria" options={["IA / Software", "Domínio", "Hospedagem", "Email", "Manutenção", "Desenvolvimento", "Outro"]} />
            </div>
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <LogoSlot size={64} label="Adicionar logo personalizado" hint="PNG, JPG, SVG ou WebP — redimensionado automaticamente" />
              <Textarea label="Breve descrição" rows={2} placeholder="Uma linha que explica o que o cliente recebe." />
            </div>
          </div>
        </Card>
        <Card title="Biblioteca de Serviços" subtitle="Preenche automaticamente nome, logo e categoria numa nova assinatura" action={<Button variant="primary" size="sm" icon="plus">Adicionar serviço</Button>} padding={0}>
          <DataTable rows={LIBRARY.map((l, i) => ({ id: i, ...l }))} dense columns={[
            { key: "nome", header: "Serviço", render: (r) => <span style={{ display: "flex", alignItems: "center", gap: 9 }}><ServiceLogo name={r.nome} category={r.categoria} size={26} assetsBase={window.LOGO_BASE} /><span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{r.nome}</span></span> },
            { key: "categoria", header: "Categoria", render: (r) => <Tag>{r.categoria}</Tag> },
            { key: "descricao", header: "Descrição padrão", wrap: true, render: (r) => <span style={{ fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{DESCRICOES[r.nome] || "—"}</span> },
            { key: "acoes", header: "", align: "right", render: () => <span style={{ display: "inline-flex", gap: 2 }}><IconButton icon="upload" label="Alterar logo" size="sm" /><IconButton icon="pencil" label="Editar" size="sm" /><IconButton icon="archive" label="Arquivar" size="sm" /></span> },
          ]} />
        </Card>
        <Card title="Métodos de pagamento aceites" subtitle="Aparecem na renovação e no registo de pagamento">
          <div style={{ display: "flex", gap: 16, flexWrap: "wrap", alignItems: "center" }}>
            {["mpesa", "emola", "transferencia", "dinheiro", "outro"].map((m) => <PaymentMethod key={m} method={m} size={28} assetsBase={window.LOGO_BASE} />)}
            <Button size="sm" variant="ghost" icon="plus">Adicionar método</Button>
          </div>
        </Card>
        </div>
      ) : tab === "lembretes" ? (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(300px,1fr))", gap: 12, alignItems: "start" }}>
          <Card title="Intervalos padrão">
            <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
              <Switch checked={auto} onChange={setAuto} label="Verificação diária automática" />
              {["30 dias antes", "15 dias antes", "7 dias antes", "3 dias antes", "1 dia antes", "No dia do vencimento", "Depois do vencimento"].map((l, i) => <Checkbox key={l} label={l} checked={i !== 1} />)}
            </div>
          </Card>
          <Card title="Template — Aviso de renovação" action={<Button size="sm" icon="pencil">Editar</Button>}>
            <div style={{ fontSize: "var(--text-sm)", color: "var(--text-body)", lineHeight: 1.65, background: "var(--surface-sunken)", padding: 12, borderRadius: "var(--radius-sm)" }}>
              Olá, <mark style={{ background: "var(--accent-soft)", color: "var(--text-accent)", padding: "0 3px", borderRadius: 3 }}>[NOME]</mark>.<br /><br />
              O seu serviço <mark style={{ background: "var(--accent-soft)", color: "var(--text-accent)", padding: "0 3px", borderRadius: 3 }}>[SERVIÇO]</mark> será renovado em <mark style={{ background: "var(--accent-soft)", color: "var(--text-accent)", padding: "0 3px", borderRadius: 3 }}>[DATA]</mark>.<br /><br />
              Valor da renovação: <mark style={{ background: "var(--accent-soft)", color: "var(--text-accent)", padding: "0 3px", borderRadius: 3 }}>[VALOR]</mark>.<br /><br />
              Atenciosamente,<br />[NOME DA EMPRESA]
            </div>
          </Card>
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(300px,1fr))", gap: 12, alignItems: "start" }}>
          <Card title="Empresa">
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <Input label="Nome da empresa" defaultValue="Exemplo Serviços, Lda" />
              <Input label="Email remetente" icon="mail" defaultValue="cobrancas@example.com" />
              <Select label="Moeda padrão" options={["MZN — Metical", "USD — Dólar", "ZAR — Rand"]} />
              <Select label="Fuso horário" options={["Africa/Maputo (CAT)", "UTC"]} />
              <LogoSlot size={56} label="Logo da empresa" hint="Usado nos emails enviados ao cliente" />
            </div>
          </Card>
          <Card title="Envio de email (SMTP)">
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <Input label="Servidor" defaultValue="smtp.example.com" />
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}><Input label="Porta" defaultValue="587" /><Select label="Segurança" options={["STARTTLS", "SSL", "Nenhuma"]} /></div>
              <Input label="Utilizador" defaultValue="cobrancas@example.com" />
              <Button variant="secondary" icon="send">Enviar email de teste</Button>
            </div>
          </Card>
        </div>
      )}
    </div>
  );
}
Object.assign(window, { SettingsScreen });
