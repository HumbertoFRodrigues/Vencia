import * as React from "react";
export interface ToastProps {
  tone?: "success" | "danger" | "info";
  title?: string;
  children?: React.ReactNode;
  onClose?: () => void;
  style?: React.CSSProperties;
}
export declare function Toast(props: ToastProps): JSX.Element;
