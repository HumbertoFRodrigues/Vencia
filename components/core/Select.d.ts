import * as React from "react";
export interface SelectOption { value: string; label: string }
export interface SelectProps extends Omit<React.SelectHTMLAttributes<HTMLSelectElement>, "style" | "size"> {
  label?: string;
  hint?: string;
  /** Plain strings or {value,label} pairs. */
  options?: Array<string | SelectOption>;
  size?: "sm" | "md" | "lg";
  style?: React.CSSProperties;
}
export declare function Select(props: SelectProps): JSX.Element;
