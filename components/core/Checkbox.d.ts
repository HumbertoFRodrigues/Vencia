import * as React from "react";
export interface CheckboxProps {
  label?: string;
  /** Secondary line under the label. */
  description?: string;
  checked?: boolean;
  onChange?: (checked: boolean, event: React.ChangeEvent<HTMLInputElement>) => void;
  disabled?: boolean;
  style?: React.CSSProperties;
}
export declare function Checkbox(props: CheckboxProps): JSX.Element;
