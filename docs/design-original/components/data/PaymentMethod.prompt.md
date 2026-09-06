Payment-method badge for the ledger, renewal dialog and client history.

M-Pesa and e-Mola use the real logo files supplied by the user (`assets/logos/mpesa.png`, `emola.png`). Bank transfer and cash have no supplied mark, so they use a neutral icon tile — swap in a bank logo by adding `logo` to `PAYMENT_METHODS`.

```jsx
<PaymentMethod method="mpesa" />
<PaymentMethod method="transferencia" size={20} />
<PaymentMethod method="dinheiro" showLabel={false} />
```

Pass `assetsBase` when the page is not served from the project root (e.g. `"../../assets/logos/"`).
