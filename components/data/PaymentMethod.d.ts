import * as React from "react";
export type PaymentMethodId = "mpesa" | "emola" | "transferencia" | "dinheiro" | "outro";
export interface PaymentMethodProps {
  method?: PaymentMethodId;
  /** Square badge size in px. 20 in tables, 22 default, 28 in forms. */
  size?: number;
  showLabel?: boolean;
  /** Path prefix for the logo files. Default "/assets/logos/". */
  assetsBase?: string;
  style?: React.CSSProperties;
}
export declare function PaymentMethod(props: PaymentMethodProps): JSX.Element;
export declare const PAYMENT_METHODS: Record<PaymentMethodId, { label: string; logo?: string; icon?: string; bg: string; fg?: string }>;
