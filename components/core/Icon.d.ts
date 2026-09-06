import * as React from "react";
export interface IconProps extends React.HTMLAttributes<HTMLSpanElement> {
  /** Lucide icon name in kebab-case, e.g. "calendar-clock", "users", "credit-card". */
  name: string;
  /** Square pixel size. 14 inline, 16 default, 18–20 in nav, 24 in empty states. */
  size?: number;
  /** Any CSS colour; defaults to currentColor so icons inherit text colour. */
  color?: string;
}
export declare function Icon(props: IconProps): JSX.Element;
