import * as React from "react";
import { ServiceStatus } from "../feedback/StatusBadge";
import { ServiceCategory } from "./ServiceLogo";
/**
 * The service tile used in service grids and on the client page.
 */
export interface ServiceCardProps {
  name?: string;
  /** Client's name, shown under the service name. */
  client?: string;
  category?: ServiceCategory;
  logoSrc?: string;
  /** Path prefix for logo-library files (see ServiceLogo). */
  assetsBase?: string;
  /** Breve descrição — clamped to two lines under the header. */
  description?: string;
  amount?: number;
  currency?: string;
  /** "mês" | "ano" | "trimestre" … */
  period?: string;
  /** Human due phrase, e.g. "Vence em 5 dias" or "Vence: 05/09/2026". */
  dueLabel?: string;
  status?: ServiceStatus;
  statusLabel?: string;
  action?: string;
  onAction?: () => void;
  style?: React.CSSProperties;
}
export declare function ServiceCard(props: ServiceCardProps): JSX.Element;
