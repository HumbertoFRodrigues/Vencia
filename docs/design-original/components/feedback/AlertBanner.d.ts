import * as React from "react";
export interface AlertBannerProps {
  tone?: "info" | "warning" | "danger" | "success";
  title?: string;
  children?: React.ReactNode;
  /** Trailing control, usually a Button. */
  action?: React.ReactNode;
  style?: React.CSSProperties;
}
export declare function AlertBanner(props: AlertBannerProps): JSX.Element;
