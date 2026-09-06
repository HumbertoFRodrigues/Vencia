The system's surface container: white, 1px subtle border, 12px radius, near-flat shadow.

```jsx
<Card title="Próximos vencimentos" action={<Button size="sm">Ver todos</Button>} padding={0}>
  <DataTable … />
</Card>
```

Pass `padding={0}` whenever a table or list should bleed to the card edges.
