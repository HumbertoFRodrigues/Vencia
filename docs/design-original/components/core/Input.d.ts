import * as React from "react";
export interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "style" | "size"> {
  label?: string;
  hint?: string;
  /** Error message; also turns the border red. */
  error?: string;
  /** Lucide icon name shown at the start of the field. */
  icon?: string;
  /** Static trailing text — units like "MZN" or "dias". */
  suffix?: string;
  size?: "sm" | "md" | "lg";
  style?: React.CSSProperties;
}
export declare function Input(props: InputProps): JSX.Element;
