export interface MoneyValueProps {
  amount?: number;
  /** Currency code shown after the number. Default "MZN". */
  currency?: string;
  /** Periodicity suffix, e.g. "mês", "ano". */
  period?: string;
  size?: "sm" | "md" | "lg" | "metric";
  tone?: "neutral" | "in" | "out" | "muted";
  style?: React.CSSProperties;
}
export declare function MoneyValue(props: MoneyValueProps): JSX.Element;
