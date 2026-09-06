import * as React from "react";
export interface PageHeaderProps {
  eyebrow?: string;
  title?: React.ReactNode;
  /** Inline metadata row (email, phone, tags). */
  meta?: React.ReactNode;
  actions?: React.ReactNode;
  /** Back-link label; renders instead of the eyebrow. */
  back?: string;
  onBack?: () => void;
  style?: React.CSSProperties;
}
export declare function PageHeader(props: PageHeaderProps): JSX.Element;
