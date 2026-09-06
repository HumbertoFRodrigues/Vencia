Light/dark toggle. Dark mode is entirely token-driven (`[data-theme="dark"]` in `tokens/colors.css`), so components need no theme props.

```jsx
<ThemeToggle />                       // lives in the TopBar by default
const [theme, setTheme, toggle] = useTheme();
```

To force a theme without the toggle, set `document.documentElement.dataset.theme = "dark"` (or `data-theme="dark"` on any subtree).
