import * as React from "react";
export type ServiceStatus = "activo" | "a_vencer" | "vencido" | "suspenso" | "cancelado";
/**
 * The canonical status pill. Never invent other status colours.
 */
export interface StatusBadgeProps {
  status?: ServiceStatus;
  /** Overrides the default Portuguese label (e.g. "Vencido há 2 dias"). */
  label?: string;
  size?: "sm" | "md";
  style?: React.CSSProperties;
}
export declare function StatusBadge(props: StatusBadgeProps): JSX.Element;
export declare const STATUS: Record<ServiceStatus, { label: string; fg: string; bg: string; dot: string }>;
