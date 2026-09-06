Title block at the top of every page body; actions sit right, aligned to the baseline of the title.

```jsx
<PageHeader back="Clientes" onBack={goBack} title="João da Silva"
  meta={<><span>joao@email.com</span><span>+258 84 000 0000</span></>}
  actions={<Button variant="primary" icon="plus">Novo serviço</Button>} />
```
