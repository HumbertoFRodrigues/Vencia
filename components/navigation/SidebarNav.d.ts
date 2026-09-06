import * as React from "react";
export interface NavItem { id: string; label: string; icon: string; badge?: string | number }
/**
 * The app's left navigation, 236px wide by default; pass collapsed + onToggleCollapse for an icon-only 60px rail.
 */
export interface SidebarNavProps {
  items?: NavItem[];
  active?: string;
  onSelect?: (id: string) => void;
  /** Wordmark text next to the logo mark. */
  brand?: string;
  /** Square logo mark URL. Default "/assets/logo-mark.png"; pass a relative path when the page is not served from the project root. */
  logoSrc?: string;
  footer?: React.ReactNode;
  /** Show icon-only rail (60px) instead of the full 236px sidebar. */
  collapsed?: boolean;
  /** Called when the built-in collapse/expand button is clicked. Omit the button entirely by leaving this unset. */
  onToggleCollapse?: () => void;
  style?: React.CSSProperties;
}
export declare function SidebarNav(props: SidebarNavProps): JSX.Element;
export declare const NAV_ITEMS: NavItem[];
