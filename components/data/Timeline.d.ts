import * as React from "react";
export interface TimelineItem {
  /** dd/mm/yyyy, mono-rendered. */
  date?: string;
  title?: string;
  description?: string;
  kind?: "criado" | "pagamento" | "activado" | "lembrete" | "vencido" | "suspenso" | "alterado" | "cancelado";
}
export interface TimelineProps { items?: TimelineItem[]; style?: React.CSSProperties }
export declare function Timeline(props: TimelineProps): JSX.Element;
