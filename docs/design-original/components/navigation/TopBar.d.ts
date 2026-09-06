import * as React from "react";
export interface TopBarProps {
  title?: string;
  search?: boolean;
  onSearch?: (value: string) => void;
  /** Buttons placed left of the bell. */
  actions?: React.ReactNode;
  /** Admin avatar image URL. Omit to show a generic person icon. */
  avatarSrc?: string;
  /** Show the light/dark toggle (default true). */
  themeToggle?: boolean;
  /** Red count bubble on the bell. */
  alertCount?: number;
  style?: React.CSSProperties;
}
export declare function TopBar(props: TopBarProps): JSX.Element;
