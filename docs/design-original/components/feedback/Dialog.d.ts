import * as React from "react";
export interface DialogProps {
  open?: boolean;
  title?: string;
  description?: string;
  children?: React.ReactNode;
  /** Right-aligned footer actions. */
  footer?: React.ReactNode;
  width?: number | string;
  onClose?: () => void;
  style?: React.CSSProperties;
}
export declare function Dialog(props: DialogProps): JSX.Element | null;
