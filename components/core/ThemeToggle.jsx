import React from "react";
import { Icon } from "./Icon.jsx";
const KEY = "sm-theme";
export function useTheme() {
  const get = () => (typeof document === "undefined" ? "light" : document.documentElement.dataset.theme || "light");
  const [theme, setThemeState] = React.useState(get);
  React.useEffect(() => {
    let saved = null;
    try { saved = window.localStorage.getItem(KEY); } catch (e) {}
    const initial = saved || (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
    document.documentElement.dataset.theme = initial;
    setThemeState(initial);
  }, []);
  const setTheme = (next) => {
    document.documentElement.dataset.theme = next;
    try { window.localStorage.setItem(KEY, next); } catch (e) {}
    setThemeState(next);
  };
  return [theme, setTheme, () => setTheme(get() === "dark" ? "light" : "dark")];
}
export function ThemeToggle({ size = 36, style }) {
  const [theme, , toggle] = useTheme();
  const [hover, setHover] = React.useState(false);
  const dark = theme === "dark";
  return (
    <button type="button" onClick={toggle} aria-label={dark ? "Modo claro" : "Modo escuro"} title={dark ? "Modo claro" : "Modo escuro"}
      onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}
      style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: size, height: size, border: "1px solid transparent", borderRadius: "var(--radius-sm)", background: hover ? "var(--surface-hover)" : "transparent", color: hover ? "var(--text-strong)" : "var(--text-muted)", cursor: "pointer", transition: "var(--transition-control)", ...style }}>
      <Icon name={dark ? "sun" : "moon"} size={17} />
    </button>
  );
}
