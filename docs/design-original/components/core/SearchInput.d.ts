import * as React from "react";
export interface SearchInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "style"> {
  placeholder?: string;
  /** Keyboard hint rendered as a kbd chip; pass null to hide. */
  shortcut?: string | null;
  width?: number | string;
  style?: React.CSSProperties;
}
export declare function SearchInput(props: SearchInputProps): JSX.Element;
