Monochrome Lucide glyph that inherits the current text colour — the only icon primitive in this system.

```jsx
<Icon name="calendar-clock" size={16} />
<Icon name="triangle-alert" size={18} color="var(--status-overdue-dot)" />
```

Icons are rendered as a CSS mask over `currentColor`, so they always match the surrounding text. Never colour an icon differently from its label unless it is a status glyph.
