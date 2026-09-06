Every list in the product: vencimentos, pagamentos, clientes. Money columns are right-aligned; status is the last column before actions.

```jsx
<DataTable onRowClick={openService} columns={[
  { key: "cliente", header: "Cliente" },
  { key: "servico", header: "Serviço" },
  { key: "vencimento", header: "Vencimento" },
  { key: "valor", header: "Valor", align: "right", render: r => <MoneyValue amount={r.valor} size="sm" /> },
  { key: "estado", header: "Estado", render: r => <StatusBadge status={r.estado} size="sm" /> },
]} rows={rows} />
```
