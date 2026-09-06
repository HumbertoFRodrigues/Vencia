Centre-top modal for short flows: renovar, registrar pagamento, suspender.

```jsx
<Dialog title="Renovar serviço" description="ChatGPT — João da Silva" onClose={close}
  footer={<><Button onClick={close}>Cancelar</Button><Button variant="primary">Confirmar renovação</Button></>}>
  …campos…
</Dialog>
```
