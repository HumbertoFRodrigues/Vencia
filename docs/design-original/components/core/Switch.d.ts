export interface SwitchProps {
  checked?: boolean;
  onChange?: (checked: boolean) => void;
  label?: string;
  disabled?: boolean;
  style?: React.CSSProperties;
}
export declare function Switch(props: SwitchProps): JSX.Element;
