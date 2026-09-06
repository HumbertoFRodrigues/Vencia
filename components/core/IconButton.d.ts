import * as React from "react";
export interface IconButtonProps extends Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, "style"> {
  /** Lucide icon name. */
  icon: string;
  /** Accessible label — required, becomes both aria-label and tooltip. */
  label: string;
  size?: "sm" | "md" | "lg";
  variant?: "ghost" | "secondary";
  style?: React.CSSProperties;
}
export declare function IconButton(props: IconButtonProps): JSX.Element;
