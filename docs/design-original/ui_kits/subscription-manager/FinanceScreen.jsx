const { PageHeader, Button, Card, StatCard, MoneyValue, PaymentMethod, Select, DataTable, Tag } = window.SubscriptionManagerDesignSystem_6a702b;

function Bar({ label, value, max, tone = "var(--accent)" }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
      <span style={{ width: 62, fontSize: "var(--text-xs)", color: "var(--text-muted)" }}>{label}</span>
      <span style={{ flex: 1, height: 8, background: "var(--surface-sunken)", borderRadius: "var(--radius-pill)", overflow: "hidden" }}>
        <span style={{ display: "block", width: Math.round((value / max) * 100) + "%", height: "100%", background: tone, borderRadius: "var(--radius-pill)" }} />
      </span>
      <MoneyValue amount={value} size="sm" tone="muted" style={{ width: 108, justifyContent: "flex-end" }} />
    </div>
  );
}

function FinanceScreen() {
  const { clientesById, pagamentosDoMes, aReceber, totais } = window.SM_DATA;
  const porMetodo = pagamentosDoMes.reduce((acc, p) => { acc[p.metodo] = (acc[p.metodo] || 0) + p.valor; return acc; }, {});
  const maxMetodo = Math.max(...Object.values(porMetodo));
  const meses = [["Abril", 6900], ["Maio", 7400], ["Junho", 8100], ["Julho", 9600], ["Agosto", totais.entradas]];
  const maxMes = Math.max(...meses.map((m) => m[1]));
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      <PageHeader eyebrow={totais.mes} title="Finanças" actions={<><Select options={["Agosto 2026", "Julho 2026", "Últimos 6 meses"]} style={{ width: 170 }} /><Button icon="download">Exportar</Button></>} />
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(190px,1fr))", gap: 12 }}>
        <StatCard label="Entradas" value={totais.entradas} currency="MZN" tone="in" icon="arrow-down-to-line" footnote={totais.nPagamentos + " pagamentos em " + totais.mes} />
        <StatCard label="A receber" value={totais.aReceber} currency="MZN" tone="out" icon="hourglass" footnote={totais.nAReceber + " serviços"} />
        <StatCard label="MRR" value={totais.mrr} currency="MZN" icon="repeat" footnote="receita mensal recorrente" />
        <StatCard label="ARR" value={totais.arr} currency="MZN" icon="chart-line" footnote="receita anual recorrente" />
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(320px,1fr))", gap: 12, alignItems: "start" }}>
        <Card title="Como o dinheiro entrou" subtitle={totais.mes + ", por método de pagamento"}>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {Object.entries(porMetodo).sort((a, b) => b[1] - a[1]).map(([m, v]) => (
              <div key={m} style={{ display: "flex", alignItems: "center", gap: 12 }}>
                <span style={{ width: 152 }}><PaymentMethod method={m} size={24} assetsBase={window.LOGO_BASE} /></span>
                <span style={{ flex: 1, height: 8, background: "var(--surface-sunken)", borderRadius: "var(--radius-pill)", overflow: "hidden" }}>
                  <span style={{ display: "block", width: Math.round((v / maxMetodo) * 100) + "%", height: "100%", background: "var(--green-600)", borderRadius: "var(--radius-pill)" }} />
                </span>
                <MoneyValue amount={v} size="sm" tone="in" style={{ width: 104, justifyContent: "flex-end" }} />
              </div>
            ))}
          </div>
        </Card>
        <Card title="Entradas por mês" subtitle="Últimos 5 meses">
          <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
            {meses.map(([m, v]) => <Bar key={m} label={m} value={v} max={maxMes} />)}
          </div>
        </Card>
      </div>
      <Card title="A receber" subtitle="Serviços vencidos ou a vencer, ainda não pagos" padding={0} action={<Tag tone="accent">{totais.nAReceber} serviços</Tag>}>
        <DataTable rows={aReceber} columns={[
          { key: "nome", header: "Serviço", render: (r) => <span style={{ color: "var(--text-strong)", fontWeight: "var(--weight-medium)" }}>{r.nome}</span> },
          { key: "cliente", header: "Cliente", render: (r) => clientesById[r.clienteId].nome },
          { key: "vencimento", header: "Vencimento", render: (r) => <span className="num" style={{ fontSize: "var(--text-xs)" }}>{r.vencimento}</span> },
          { key: "periodicidade", header: "Período" },
          { key: "valor", header: "Valor", align: "right", render: (r) => <MoneyValue amount={r.valor} size="sm" tone="out" /> },
        ]} />
      </Card>
    </div>
  );
}
Object.assign(window, { FinanceScreen });
