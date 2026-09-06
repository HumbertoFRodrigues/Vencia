import * as React from "react";
export interface DataTableColumn<T = any> {
  key: string;
  header?: React.ReactNode;
  align?: "left" | "right" | "center";
  /** Cell renderer; omit to print row[key] verbatim. */
  render?: (row: T, index: number) => React.ReactNode;
  /** Allow wrapping (default: nowrap). */
  wrap?: boolean;
  sorted?: "asc" | "desc";
}
/**
 * Flat data table — hairline rows, uppercase micro headers, no vertical rules.
 */
export interface DataTableProps<T = any> {
  columns?: DataTableColumn<T>[];
  rows?: T[];
  onRowClick?: (row: T, index: number) => void;
  dense?: boolean;
  emptyState?: React.ReactNode;
  style?: React.CSSProperties;
}
export declare function DataTable<T = any>(props: DataTableProps<T>): JSX.Element;
