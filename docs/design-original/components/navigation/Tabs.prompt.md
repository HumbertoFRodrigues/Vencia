Underlined filter tabs — the status filters (Todos / Activos / A vencer / Vencidos / Cancelados) and detail-page sections.

```jsx
<Tabs active={f} onSelect={setF} tabs={[
  { id: "todos", label: "Todos", count: 31 },
  { id: "a_vencer", label: "A vencer", count: 5 },
  { id: "vencido", label: "Vencidos", count: 4 },
]} />
```
