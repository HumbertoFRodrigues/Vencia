import * as React from "react";
export interface CardProps {
  title?: string;
  subtitle?: string;
  /** Header-right control. */
  action?: React.ReactNode;
  /** Body padding in px; pass 0 when the body is a full-bleed table. */
  padding?: number | string;
  interactive?: boolean;
  children?: React.ReactNode;
  style?: React.CSSProperties;
}
export declare function Card(props: CardProps): JSX.Element;
