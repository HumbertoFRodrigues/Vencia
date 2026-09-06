import * as React from "react";
export interface TagProps {
  children?: React.ReactNode;
  /** neutral for categories, accent for the selected filter, outline for metadata. */
  tone?: "neutral" | "accent" | "outline";
  icon?: React.ReactNode;
  style?: React.CSSProperties;
}
export declare function Tag(props: TagProps): JSX.Element;
