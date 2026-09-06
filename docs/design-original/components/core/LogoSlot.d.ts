import * as React from "react";
export interface LogoSlotProps {
  /** Existing logo URL; without it the slot shows a dashed upload target. */
  src?: string;
  /** Square size in px. Default 64. */
  size?: number;
  label?: string;
  hint?: string;
  onClick?: () => void;
  style?: React.CSSProperties;
}
export declare function LogoSlot(props: LogoSlotProps): JSX.Element;
