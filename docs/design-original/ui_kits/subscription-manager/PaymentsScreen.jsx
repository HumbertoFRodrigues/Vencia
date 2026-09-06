const { PageHeader, Button, Card, DataTable, MoneyValue, Select, SearchInput, StatCard, ServiceLogo, Tag, PaymentMethod } = window.SubscriptionManagerDesignSystem_6a702b;

const fmtMZN = (n) => String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ".");

function PaymentsScreen() {
  const { pagamentos, clientesById, servicos, totais } = window.SM_DATA;
  const [metodo, setMetodo] = React.useState("todos");
  const METODOS = [{ value: "todos", label: "Todos os métodos" }, { value: "mpesa", label: "M-Pesa" }, { value: "emola", label: "e-Mola" }, { value: "transferencia", label: "Transferência" }, { value: "dinheiro", label: "Dinheiro" }];
  const rows = pagamentos.filter((p) => metodo === "todos" || p.metodo === metodo);
  const total = rows.reduce((t, p) => t + p.valor, 0);
  const svc = (id) => servicos.find((s) => s.id === id) || {};
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow={totais.mes} title="Pagamentos" actions={<><Button icon="download">Exportar CSV</Button><Button variant="primary" icon="plus">Registrar pagamento</Button></>} />
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(190px,1fr))", gap: 12 }}>
        <StatCard label="Entradas do período" value={total} currency="MZN" tone="in" icon="arrow-down-to-line" footnote={rows.length + " pagamentos"} />
        <StatCard label="A receber" value={totais.aReceber} currency="MZN" tone="out" icon="hourglass" footnote={totais.nAReceber + " serviços"} />
        <StatCard label="MRR" value={totais.mrr} currency="MZN" icon="repeat" />
        <StatCard label="ARR" value={totais.arr} currency="MZN" icon="chart-line" />
      </div>
      <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
        {METODOS.slice(1).map((m) => {
          const on = m.value === metodo;
          return (
            <button key={m.value} onClick={() => setMetodo(on ? "todos" : m.value)}
              style={{ display: "inline-flex", alignItems: "center", padding: "5px 11px 5px 7px", background: on ? "var(--accent-soft)" : "var(--surface-card)", border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"), borderRadius: "var(--radius-pill)", cursor: "pointer", font: "inherit", transition: "var(--transition-control)" }}>
              <PaymentMethod method={m.value} size={22} assetsBase={window.LOGO_BASE} />
            </button>
          );
        })}
      </div>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 10, flexWrap: "wrap" }}>
        <Select options={[totais.mes, "Julho 2026", "Junho 2026"]} style={{ width: 160 }} />
        <Select options={METODOS} value={metodo} onChange={(e) => setMetodo(e.target.value)} style={{ width: 180 }} />
        <Select options={["Todos os clientes", "João da Silva", "Maria Costa", "Carlos Manuel"]} style={{ width: 190 }} />
        <SearchInput width={220} placeholder="Pesquisar pagamento" shortcut={null} style={{ marginLeft: "auto" }} />
      </div>
      <Card padding={0} title={"Total recebido no período"} subtitle={fmtMZN(total) + " MZN"} action={<Tag tone="accent">{rows.length} registos</Tag>}>
        <DataTable rows={rows} columns={[
          { key: "data", header: "Data", render: (r) => <span className="num" style={{ fontSize: "var(--text-xs)" }}>{r.data}</span> },
          { key: "cliente", header: "Cliente", render: (r) => <span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{clientesById[r.clienteId].nome}</span> },
          { key: "servico", header: "Serviço", render: (r) => { const s = svc(r.servicoId); return <span style={{ display: "flex", alignItems: "center", gap: 8 }}><ServiceLogo name={s.nome} category={s.categoria} size={24} assetsBase={window.LOGO_BASE} />{s.nome}</span>; } },
          { key: "periodo", header: "Período", render: (r) => <Tag tone="outline">{r.periodo}</Tag> },
          { key: "metodo", header: "Método", render: (r) => <PaymentMethod method={r.metodo} size={22} assetsBase={window.LOGO_BASE} /> },
          { key: "valor", header: "Valor", align: "right", render: (r) => <MoneyValue amount={r.valor} tone="in" size="sm" /> },
        ]} />
      </Card>
    </div>
  );
}
Object.assign(window, { PaymentsScreen });
