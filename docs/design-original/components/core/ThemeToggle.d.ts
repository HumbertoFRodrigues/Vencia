import * as React from "react";
/** Light/dark switch. Writes data-theme on <html> and remembers the choice in localStorage ("sm-theme"). */
export interface ThemeToggleProps { size?: number; style?: React.CSSProperties }
export declare function ThemeToggle(props: ThemeToggleProps): JSX.Element;
/** [theme, setTheme, toggle] — initialises from localStorage, then prefers-color-scheme. */
export declare function useTheme(): [string, (t: string) => void, () => void];
