import * as React from "react";
/**
 * Dashboard metric tile.
 */
export interface StatCardProps {
  /** Uppercase eyebrow label, e.g. "Receita deste mês". */
  label?: string;
  /** Number (money) or any node (counts). */
  value?: number | string;
  /** Pass "MZN" to render the value as money. */
  currency?: string;
  period?: string;
  /** Short comparison string, e.g. "+12% vs Julho". */
  delta?: string;
  deltaTone?: "neutral" | "in" | "out";
  icon?: string;
  tone?: "neutral" | "in" | "out" | "due";
  footnote?: React.ReactNode;
  onClick?: () => void;
  style?: React.CSSProperties;
}
export declare function StatCard(props: StatCardProps): JSX.Element;
