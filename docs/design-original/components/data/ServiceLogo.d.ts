import * as React from "react";
export type ServiceCategory = "dominio" | "hospedagem" | "email" | "ia" | "software" | "manutencao" | "desenvolvimento" | "outro";
/**
 * Square service mark. Resolution order: explicit `src` → logo library match on the
 * service name → category-tinted monogram tile. A missing file falls back silently.
 */
export interface ServiceLogoProps {
  /** Service name — matched against LOGO_LIBRARY, then used for the monogram. */
  name?: string;
  category?: ServiceCategory;
  /** URL of an uploaded logo (PNG/JPG/SVG/WebP); wins over the library. */
  src?: string;
  /** 24 in tables, 32 in lists, 40 in cards, 56 on detail pages. */
  size?: number;
  /** Path prefix for library files. Default "/assets/logos/". */
  assetsBase?: string;
  style?: React.CSSProperties;
}
export declare function ServiceLogo(props: ServiceLogoProps): JSX.Element;
/** Known service name (lowercase) → expected file name in the logo folder. */
export declare const LOGO_LIBRARY: Record<string, string>;
export declare function resolveServiceLogo(name?: string, assetsBase?: string): string | null;
