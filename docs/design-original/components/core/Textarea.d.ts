import * as React from "react";
export interface TextareaProps extends Omit<React.TextareaHTMLAttributes<HTMLTextAreaElement>, "style"> {
  label?: string;
  hint?: string;
  error?: string;
  rows?: number;
  style?: React.CSSProperties;
}
export declare function Textarea(props: TextareaProps): JSX.Element;
