/* @ds-bundle: {"format":4,"namespace":"SubscriptionManagerDesignSystem_6a702b","components":[{"name":"Button","sourcePath":"components/core/Button.jsx"},{"name":"Card","sourcePath":"components/core/Card.jsx"},{"name":"Checkbox","sourcePath":"components/core/Checkbox.jsx"},{"name":"Icon","sourcePath":"components/core/Icon.jsx"},{"name":"IconButton","sourcePath":"components/core/IconButton.jsx"},{"name":"Input","sourcePath":"components/core/Input.jsx"},{"name":"LogoSlot","sourcePath":"components/core/LogoSlot.jsx"},{"name":"SearchInput","sourcePath":"components/core/SearchInput.jsx"},{"name":"Select","sourcePath":"components/core/Select.jsx"},{"name":"Switch","sourcePath":"components/core/Switch.jsx"},{"name":"Textarea","sourcePath":"components/core/Textarea.jsx"},{"name":"ThemeToggle","sourcePath":"components/core/ThemeToggle.jsx"},{"name":"DataTable","sourcePath":"components/data/DataTable.jsx"},{"name":"MoneyValue","sourcePath":"components/data/MoneyValue.jsx"},{"name":"PAYMENT_METHODS","sourcePath":"components/data/PaymentMethod.jsx"},{"name":"PaymentMethod","sourcePath":"components/data/PaymentMethod.jsx"},{"name":"ServiceCard","sourcePath":"components/data/ServiceCard.jsx"},{"name":"LOGO_LIBRARY","sourcePath":"components/data/ServiceLogo.jsx"},{"name":"ServiceLogo","sourcePath":"components/data/ServiceLogo.jsx"},{"name":"SERVICE_CATEGORIES","sourcePath":"components/data/ServiceLogo.jsx"},{"name":"StatCard","sourcePath":"components/data/StatCard.jsx"},{"name":"Timeline","sourcePath":"components/data/Timeline.jsx"},{"name":"AlertBanner","sourcePath":"components/feedback/AlertBanner.jsx"},{"name":"Dialog","sourcePath":"components/feedback/Dialog.jsx"},{"name":"EmptyState","sourcePath":"components/feedback/EmptyState.jsx"},{"name":"STATUS","sourcePath":"components/feedback/StatusBadge.jsx"},{"name":"StatusBadge","sourcePath":"components/feedback/StatusBadge.jsx"},{"name":"Tag","sourcePath":"components/feedback/Tag.jsx"},{"name":"Toast","sourcePath":"components/feedback/Toast.jsx"},{"name":"PageHeader","sourcePath":"components/navigation/PageHeader.jsx"},{"name":"NAV_ITEMS","sourcePath":"components/navigation/SidebarNav.jsx"},{"name":"SidebarNav","sourcePath":"components/navigation/SidebarNav.jsx"},{"name":"Tabs","sourcePath":"components/navigation/Tabs.jsx"},{"name":"TopBar","sourcePath":"components/navigation/TopBar.jsx"}],"sourceHashes":{"components/core/Button.jsx":"caee0a87bee6","components/core/Card.jsx":"96918c5d101b","components/core/Checkbox.jsx":"7469961061f2","components/core/Icon.jsx":"67411edb6e75","components/core/IconButton.jsx":"deae1ba05886","components/core/Input.jsx":"b8328ad0a3d1","components/core/LogoSlot.jsx":"a8881eb57e0f","components/core/SearchInput.jsx":"dec5326b9fd9","components/core/Select.jsx":"89d72a6fe785","components/core/Switch.jsx":"561ed21c6f2c","components/core/Textarea.jsx":"ccb841e2e6a3","components/core/ThemeToggle.jsx":"baa3add950ea","components/data/DataTable.jsx":"e7161ecaacd6","components/data/MoneyValue.jsx":"e4c1a5cf31a8","components/data/PaymentMethod.jsx":"6d152d679866","components/data/ServiceCard.jsx":"c6ca4241fa14","components/data/ServiceLogo.jsx":"389a502df026","components/data/StatCard.jsx":"dd3ad5d1cad7","components/data/Timeline.jsx":"70f96c810c44","components/feedback/AlertBanner.jsx":"3b26642e682b","components/feedback/Dialog.jsx":"199b6d2f464f","components/feedback/EmptyState.jsx":"49dffbcb2d75","components/feedback/StatusBadge.jsx":"54bd54ce9b4c","components/feedback/Tag.jsx":"d52c5168467e","components/feedback/Toast.jsx":"32b8732b8a94","components/navigation/PageHeader.jsx":"240c85132e5f","components/navigation/SidebarNav.jsx":"900397034446","components/navigation/Tabs.jsx":"edeaf3193609","components/navigation/TopBar.jsx":"7f8945fff9ad","ui_kits/subscription-manager/App.jsx":"18dea7422a25","ui_kits/subscription-manager/ClientDetailScreen.jsx":"218d4f845708","ui_kits/subscription-manager/ClientsScreen.jsx":"77edb271ffa1","ui_kits/subscription-manager/DashboardScreen.jsx":"d5a4eee2c37f","ui_kits/subscription-manager/DueDatesScreen.jsx":"2d838cdfd86d","ui_kits/subscription-manager/FinanceScreen.jsx":"9a96e9135550","ui_kits/subscription-manager/HistoryScreen.jsx":"bd97951e9833","ui_kits/subscription-manager/NewSubscriptionDialog.jsx":"2a131f39918a","ui_kits/subscription-manager/PaymentsScreen.jsx":"073ac1223076","ui_kits/subscription-manager/ServiceDetailScreen.jsx":"101fa119581d","ui_kits/subscription-manager/ServicesScreen.jsx":"309355a22686","ui_kits/subscription-manager/SettingsScreen.jsx":"48532ea68a35","ui_kits/subscription-manager/data.js":"7347428065c9"},"inlinedExternals":[],"unexposedExports":[{"name":"resolveServiceLogo","sourcePath":"components/data/ServiceLogo.jsx"},{"name":"useTheme","sourcePath":"components/core/ThemeToggle.jsx"}]} */

(() => {

const __ds_ns = (window.SubscriptionManagerDesignSystem_6a702b = window.SubscriptionManagerDesignSystem_6a702b || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/core/Card.jsx
try { (() => {
function Card({
  title,
  subtitle,
  action,
  padding = 16,
  children,
  interactive,
  style
}) {
  const [hover, setHover] = React.useState(false);
  return /*#__PURE__*/React.createElement("section", {
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "flex",
      flexDirection: "column",
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-lg)",
      boxShadow: interactive && hover ? "var(--shadow-raised)" : "var(--shadow-card)",
      transition: "box-shadow var(--dur-base) var(--ease-standard)",
      overflow: "hidden",
      ...style
    }
  }, title || action ? /*#__PURE__*/React.createElement("header", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      padding: "14px 16px",
      borderBottom: "1px solid var(--border-subtle)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, /*#__PURE__*/React.createElement("h3", {
    style: {
      fontSize: "var(--text-h3)",
      letterSpacing: "var(--text-h3-ls)"
    }
  }, title), subtitle ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, subtitle) : null), action) : null, /*#__PURE__*/React.createElement("div", {
    style: {
      padding,
      flex: 1,
      minHeight: 0
    }
  }, children));
}
Object.assign(__ds_scope, { Card });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Card.jsx", error: String((e && e.message) || e) }); }

// components/core/Icon.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const BASE = "https://unpkg.com/lucide-static@0.454.0/icons/";
function Icon({
  name,
  size = 16,
  color = "currentColor",
  strokeWidth,
  style,
  ...rest
}) {
  const url = `url("${BASE}${name}.svg")`;
  return /*#__PURE__*/React.createElement("span", _extends({
    "aria-hidden": "true"
  }, rest, {
    style: {
      display: "inline-block",
      flex: "0 0 auto",
      width: size,
      height: size,
      background: color,
      WebkitMaskImage: url,
      maskImage: url,
      WebkitMaskRepeat: "no-repeat",
      maskRepeat: "no-repeat",
      WebkitMaskPosition: "center",
      maskPosition: "center",
      WebkitMaskSize: "contain",
      maskSize: "contain",
      ...style
    }
  }));
}
Object.assign(__ds_scope, { Icon });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Icon.jsx", error: String((e && e.message) || e) }); }

// components/core/Button.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const SIZES = {
  sm: {
    h: "var(--control-h-sm)",
    px: 10,
    fs: "var(--text-sm)",
    gap: 6
  },
  md: {
    h: "var(--control-h-md)",
    px: 14,
    fs: "var(--text-sm)",
    gap: 7
  },
  lg: {
    h: "var(--control-h-lg)",
    px: 18,
    fs: "var(--text-body-size)",
    gap: 8
  }
};
const VARIANTS = {
  primary: {
    background: "var(--accent)",
    color: "var(--on-accent)",
    border: "1px solid var(--accent)",
    boxShadow: "var(--shadow-sm), inset 0 1px 0 var(--inset-highlight)"
  },
  secondary: {
    background: "var(--surface-card)",
    color: "var(--text-strong)",
    border: "1px solid var(--border-default)",
    boxShadow: "var(--shadow-sm)"
  },
  ghost: {
    background: "transparent",
    color: "var(--text-body)",
    border: "1px solid transparent"
  },
  danger: {
    background: "var(--red-600)",
    color: "var(--on-danger)",
    border: "1px solid var(--red-600)",
    boxShadow: "var(--shadow-sm), inset 0 1px 0 var(--inset-highlight)"
  },
  quiet: {
    background: "var(--surface-sunken)",
    color: "var(--text-strong)",
    border: "1px solid transparent"
  }
};
const HOVER = {
  primary: "var(--accent-hover)",
  secondary: "var(--surface-hover)",
  ghost: "var(--surface-hover)",
  danger: "var(--red-700)",
  quiet: "var(--surface-active)"
};
function Button({
  variant = "secondary",
  size = "md",
  icon,
  iconEnd,
  fullWidth,
  disabled,
  children,
  style,
  onMouseEnter,
  onMouseLeave,
  ...rest
}) {
  const [hover, setHover] = React.useState(false);
  const s = SIZES[size] || SIZES.md,
    v = VARIANTS[variant] || VARIANTS.secondary;
  return /*#__PURE__*/React.createElement("button", _extends({
    type: "button",
    disabled: disabled,
    onMouseEnter: e => {
      setHover(true);
      onMouseEnter && onMouseEnter(e);
    },
    onMouseLeave: e => {
      setHover(false);
      onMouseLeave && onMouseLeave(e);
    }
  }, rest, {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      gap: s.gap,
      height: s.h,
      padding: `0 ${s.px}px`,
      width: fullWidth ? "100%" : undefined,
      borderRadius: "var(--radius-sm)",
      fontFamily: "var(--font-sans)",
      fontSize: s.fs,
      fontWeight: "var(--weight-medium)",
      lineHeight: 1,
      whiteSpace: "nowrap",
      cursor: disabled ? "not-allowed" : "pointer",
      opacity: disabled ? 0.45 : 1,
      transition: "var(--transition-control), transform var(--dur-instant) var(--ease-standard)",
      ...v,
      ...(hover && !disabled ? {
        background: HOVER[variant]
      } : null),
      ...style
    }
  }), icon ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: size === "lg" ? 17 : 15
  }) : null, children, iconEnd ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: iconEnd,
    size: size === "lg" ? 17 : 15
  }) : null);
}
Object.assign(__ds_scope, { Button });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Button.jsx", error: String((e && e.message) || e) }); }

// components/core/Checkbox.jsx
try { (() => {
function Checkbox({
  label,
  description,
  checked,
  onChange,
  disabled,
  style
}) {
  return /*#__PURE__*/React.createElement("label", {
    style: {
      display: "inline-flex",
      alignItems: description ? "flex-start" : "center",
      gap: 9,
      cursor: disabled ? "not-allowed" : "pointer",
      opacity: disabled ? 0.5 : 1,
      ...style
    }
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: !!checked,
    disabled: disabled,
    onChange: e => onChange && onChange(e.target.checked, e),
    style: {
      position: "absolute",
      opacity: 0,
      width: 0,
      height: 0
    }
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: 17,
      height: 17,
      marginTop: description ? 2 : 0,
      flex: "0 0 auto",
      borderRadius: "var(--radius-xs)",
      background: checked ? "var(--accent)" : "var(--surface-card)",
      border: "1px solid " + (checked ? "var(--accent)" : "var(--border-default)"),
      boxShadow: "var(--shadow-sm)",
      transition: "var(--transition-control)"
    }
  }, checked ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "check",
    size: 12,
    color: "var(--on-accent)"
  }) : null), label ? /*#__PURE__*/React.createElement("span", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  }, label), description ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, description) : null) : null);
}
Object.assign(__ds_scope, { Checkbox });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Checkbox.jsx", error: String((e && e.message) || e) }); }

// components/core/IconButton.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const SZ = {
  sm: 28,
  md: 36,
  lg: 44
};
function IconButton({
  icon,
  size = "md",
  variant = "ghost",
  label,
  disabled,
  style,
  ...rest
}) {
  const [hover, setHover] = React.useState(false);
  const d = SZ[size] || SZ.md;
  const base = variant === "secondary" ? {
    background: "var(--surface-card)",
    border: "1px solid var(--border-default)",
    boxShadow: "var(--shadow-sm)"
  } : {
    background: "transparent",
    border: "1px solid transparent"
  };
  return /*#__PURE__*/React.createElement("button", _extends({
    type: "button",
    "aria-label": label,
    title: label,
    disabled: disabled,
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false)
  }, rest, {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: d,
      height: d,
      borderRadius: "var(--radius-sm)",
      color: "var(--text-muted)",
      cursor: disabled ? "not-allowed" : "pointer",
      opacity: disabled ? 0.45 : 1,
      transition: "var(--transition-control)",
      ...base,
      ...(hover && !disabled ? {
        background: "var(--surface-hover)",
        color: "var(--text-strong)"
      } : null),
      ...style
    }
  }), /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: size === "sm" ? 15 : 17
  }));
}
Object.assign(__ds_scope, { IconButton });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/IconButton.jsx", error: String((e && e.message) || e) }); }

// components/core/Input.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function Input({
  label,
  hint,
  error,
  icon,
  suffix,
  size = "md",
  id,
  style,
  ...rest
}) {
  const [focus, setFocus] = React.useState(false);
  const uid = id || React.useMemo(() => "in-" + Math.random().toString(36).slice(2, 7), []);
  const h = size === "lg" ? "var(--control-h-lg)" : size === "sm" ? "var(--control-h-sm)" : "var(--control-h-md)";
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 6,
      ...style
    }
  }, label ? /*#__PURE__*/React.createElement("label", {
    htmlFor: uid,
    style: {
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-strong)"
    }
  }, label) : null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      height: h,
      padding: "0 10px",
      background: "var(--surface-card)",
      border: "1px solid " + (error ? "var(--red-600)" : focus ? "var(--border-focus)" : "var(--border-default)"),
      borderRadius: "var(--radius-sm)",
      boxShadow: focus ? "var(--ring)" : "var(--shadow-sm)",
      transition: "var(--transition-control)"
    }
  }, icon ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: 15,
    color: "var(--text-faint)"
  }) : null, /*#__PURE__*/React.createElement("input", _extends({
    id: uid,
    onFocus: () => setFocus(true),
    onBlur: () => setFocus(false)
  }, rest, {
    style: {
      flex: 1,
      minWidth: 0,
      border: "none",
      outline: "none",
      background: "transparent",
      font: "inherit",
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  })), suffix ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)",
      whiteSpace: "nowrap"
    }
  }, suffix) : null), error || hint ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: error ? "var(--red-600)" : "var(--text-muted)"
    }
  }, error || hint) : null);
}
Object.assign(__ds_scope, { Input });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Input.jsx", error: String((e && e.message) || e) }); }

// components/core/LogoSlot.jsx
try { (() => {
function LogoSlot({
  src,
  size = 64,
  label = "Adicionar logo personalizado",
  hint = "PNG, JPG, SVG ou WebP",
  onClick,
  style
}) {
  const [hover, setHover] = React.useState(false);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      ...style
    }
  }, /*#__PURE__*/React.createElement("button", {
    type: "button",
    onClick: onClick,
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: size,
      height: size,
      flex: "0 0 auto",
      padding: src ? Math.round(size * 0.1) : 0,
      background: hover ? "var(--surface-hover)" : "var(--surface-card)",
      border: src ? "1px solid var(--border-subtle)" : "1px dashed " + (hover ? "var(--border-accent)" : "var(--border-default)"),
      borderRadius: "var(--radius-md)",
      cursor: "pointer",
      transition: "var(--transition-control)"
    }
  }, src ? /*#__PURE__*/React.createElement("img", {
    src: src,
    alt: "",
    style: {
      width: "100%",
      height: "100%",
      objectFit: "contain"
    }
  }) : /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "image-plus",
    size: Math.round(size * 0.32),
    color: hover ? "var(--accent)" : "var(--text-faint)"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-strong)"
    }
  }, label), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, hint)));
}
Object.assign(__ds_scope, { LogoSlot });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/LogoSlot.jsx", error: String((e && e.message) || e) }); }

// components/core/SearchInput.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function SearchInput({
  placeholder = "Pesquisar cliente, serviço, domínio…",
  shortcut = "/",
  width = 380,
  style,
  ...rest
}) {
  const [focus, setFocus] = React.useState(false);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      width,
      height: "var(--control-h-md)",
      padding: "0 10px",
      background: focus ? "var(--surface-card)" : "var(--surface-sunken)",
      border: "1px solid " + (focus ? "var(--border-focus)" : "transparent"),
      borderRadius: "var(--radius-sm)",
      boxShadow: focus ? "var(--ring)" : "none",
      transition: "var(--transition-control)",
      ...style
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "search",
    size: 15,
    color: "var(--text-faint)"
  }), /*#__PURE__*/React.createElement("input", _extends({
    placeholder: placeholder,
    onFocus: () => setFocus(true),
    onBlur: () => setFocus(false)
  }, rest, {
    style: {
      flex: 1,
      minWidth: 0,
      border: "none",
      outline: "none",
      background: "transparent",
      font: "inherit",
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  })), shortcut ? /*#__PURE__*/React.createElement("kbd", {
    style: {
      fontFamily: "var(--font-mono)",
      fontSize: "var(--text-xs)",
      color: "var(--text-faint)",
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-xs)",
      padding: "1px 5px"
    }
  }, shortcut) : null);
}
Object.assign(__ds_scope, { SearchInput });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/SearchInput.jsx", error: String((e && e.message) || e) }); }

// components/core/Select.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function Select({
  label,
  hint,
  options = [],
  size = "md",
  id,
  style,
  ...rest
}) {
  const uid = id || React.useMemo(() => "se-" + Math.random().toString(36).slice(2, 7), []);
  const h = size === "lg" ? "var(--control-h-lg)" : size === "sm" ? "var(--control-h-sm)" : "var(--control-h-md)";
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 6,
      ...style
    }
  }, label ? /*#__PURE__*/React.createElement("label", {
    htmlFor: uid,
    style: {
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-strong)"
    }
  }, label) : null, /*#__PURE__*/React.createElement("div", {
    style: {
      position: "relative",
      display: "flex",
      alignItems: "center"
    }
  }, /*#__PURE__*/React.createElement("select", _extends({
    id: uid
  }, rest, {
    style: {
      appearance: "none",
      width: "100%",
      height: h,
      padding: "0 30px 0 10px",
      background: "var(--surface-card)",
      border: "1px solid var(--border-default)",
      borderRadius: "var(--radius-sm)",
      boxShadow: "var(--shadow-sm)",
      font: "inherit",
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)",
      cursor: "pointer"
    }
  }), options.map(o => {
    const v = typeof o === "string" ? o : o.value,
      l = typeof o === "string" ? o : o.label;
    return /*#__PURE__*/React.createElement("option", {
      key: v,
      value: v
    }, l);
  })), /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "chevron-down",
    size: 15,
    color: "var(--text-faint)",
    style: {
      position: "absolute",
      right: 9,
      pointerEvents: "none"
    }
  })), hint ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, hint) : null);
}
Object.assign(__ds_scope, { Select });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Select.jsx", error: String((e && e.message) || e) }); }

// components/core/Switch.jsx
try { (() => {
function Switch({
  checked,
  onChange,
  label,
  disabled,
  style
}) {
  return /*#__PURE__*/React.createElement("label", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 10,
      cursor: disabled ? "not-allowed" : "pointer",
      opacity: disabled ? 0.5 : 1,
      ...style
    }
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: !!checked,
    disabled: disabled,
    onChange: e => onChange && onChange(e.target.checked, e),
    style: {
      position: "absolute",
      opacity: 0,
      width: 0,
      height: 0
    }
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      position: "relative",
      width: 36,
      height: 21,
      flex: "0 0 auto",
      borderRadius: "var(--radius-pill)",
      background: checked ? "var(--accent)" : "var(--border-default)",
      transition: "background-color var(--dur-fast) var(--ease-standard)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      position: "absolute",
      top: 2,
      left: checked ? 17 : 2,
      width: 17,
      height: 17,
      borderRadius: "50%",
      background: "var(--on-accent)",
      boxShadow: "var(--shadow-sm)",
      transition: "left var(--dur-fast) var(--ease-standard)"
    }
  })), label ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  }, label) : null);
}
Object.assign(__ds_scope, { Switch });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Switch.jsx", error: String((e && e.message) || e) }); }

// components/core/Textarea.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function Textarea({
  label,
  hint,
  error,
  rows = 3,
  id,
  style,
  ...rest
}) {
  const [focus, setFocus] = React.useState(false);
  const uid = id || React.useMemo(() => "ta-" + Math.random().toString(36).slice(2, 7), []);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 6,
      ...style
    }
  }, label ? /*#__PURE__*/React.createElement("label", {
    htmlFor: uid,
    style: {
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-strong)"
    }
  }, label) : null, /*#__PURE__*/React.createElement("textarea", _extends({
    id: uid,
    rows: rows,
    onFocus: () => setFocus(true),
    onBlur: () => setFocus(false)
  }, rest, {
    style: {
      resize: "vertical",
      padding: "8px 10px",
      background: "var(--surface-card)",
      border: "1px solid " + (error ? "var(--red-600)" : focus ? "var(--border-focus)" : "var(--border-default)"),
      borderRadius: "var(--radius-sm)",
      boxShadow: focus ? "var(--ring)" : "var(--shadow-sm)",
      outline: "none",
      font: "inherit",
      fontSize: "var(--text-sm)",
      lineHeight: 1.5,
      color: "var(--text-strong)",
      transition: "var(--transition-control)"
    }
  })), error || hint ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: error ? "var(--red-600)" : "var(--text-muted)"
    }
  }, error || hint) : null);
}
Object.assign(__ds_scope, { Textarea });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Textarea.jsx", error: String((e && e.message) || e) }); }

// components/core/ThemeToggle.jsx
try { (() => {
const KEY = "sm-theme";
function useTheme() {
  const get = () => typeof document === "undefined" ? "light" : document.documentElement.dataset.theme || "light";
  const [theme, setThemeState] = React.useState(get);
  React.useEffect(() => {
    let saved = null;
    try {
      saved = window.localStorage.getItem(KEY);
    } catch (e) {}
    const initial = saved || (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
    document.documentElement.dataset.theme = initial;
    setThemeState(initial);
  }, []);
  const setTheme = next => {
    document.documentElement.dataset.theme = next;
    try {
      window.localStorage.setItem(KEY, next);
    } catch (e) {}
    setThemeState(next);
  };
  return [theme, setTheme, () => setTheme(get() === "dark" ? "light" : "dark")];
}
function ThemeToggle({
  size = 36,
  style
}) {
  const [theme,, toggle] = useTheme();
  const [hover, setHover] = React.useState(false);
  const dark = theme === "dark";
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    onClick: toggle,
    "aria-label": dark ? "Modo claro" : "Modo escuro",
    title: dark ? "Modo claro" : "Modo escuro",
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: size,
      height: size,
      border: "1px solid transparent",
      borderRadius: "var(--radius-sm)",
      background: hover ? "var(--surface-hover)" : "transparent",
      color: hover ? "var(--text-strong)" : "var(--text-muted)",
      cursor: "pointer",
      transition: "var(--transition-control)",
      ...style
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: dark ? "sun" : "moon",
    size: 17
  }));
}
Object.assign(__ds_scope, { useTheme, ThemeToggle });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/ThemeToggle.jsx", error: String((e && e.message) || e) }); }

// components/data/DataTable.jsx
try { (() => {
function DataTable({
  columns = [],
  rows = [],
  onRowClick,
  dense,
  emptyState,
  style
}) {
  const [hover, setHover] = React.useState(-1);
  const pad = dense ? "8px 14px" : "11px 14px";
  return /*#__PURE__*/React.createElement("div", {
    style: {
      width: "100%",
      overflowX: "auto",
      ...style
    }
  }, /*#__PURE__*/React.createElement("table", {
    style: {
      width: "100%",
      borderCollapse: "collapse",
      fontSize: "var(--text-sm)"
    }
  }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, columns.map(c => /*#__PURE__*/React.createElement("th", {
    key: c.key,
    style: {
      padding: pad,
      textAlign: c.align || "left",
      fontSize: "var(--text-label)",
      letterSpacing: "var(--text-label-ls)",
      textTransform: "uppercase",
      fontWeight: "var(--weight-semibold)",
      color: "var(--text-muted)",
      borderBottom: "1px solid var(--border-subtle)",
      whiteSpace: "nowrap",
      background: "var(--table-head-bg)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 4
    }
  }, c.header, c.sorted ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: c.sorted === "desc" ? "arrow-down" : "arrow-up",
    size: 11
  }) : null))))), /*#__PURE__*/React.createElement("tbody", null, rows.length === 0 ? /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: columns.length,
    style: {
      padding: 0
    }
  }, emptyState)) : rows.map((r, i) => /*#__PURE__*/React.createElement("tr", {
    key: r.id ?? i,
    onClick: onRowClick ? () => onRowClick(r, i) : undefined,
    onMouseEnter: () => setHover(i),
    onMouseLeave: () => setHover(-1),
    style: {
      background: hover === i && onRowClick ? "var(--surface-hover)" : "transparent",
      cursor: onRowClick ? "pointer" : "default",
      transition: "background-color var(--dur-instant) var(--ease-standard)"
    }
  }, columns.map(c => /*#__PURE__*/React.createElement("td", {
    key: c.key,
    style: {
      padding: pad,
      textAlign: c.align || "left",
      color: "var(--text-body)",
      borderBottom: i === rows.length - 1 ? "none" : "1px solid var(--border-subtle)",
      whiteSpace: c.wrap ? "normal" : "nowrap"
    }
  }, c.render ? c.render(r, i) : r[c.key])))))));
}
Object.assign(__ds_scope, { DataTable });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/DataTable.jsx", error: String((e && e.message) || e) }); }

// components/data/MoneyValue.jsx
try { (() => {
function MoneyValue({
  amount = 0,
  currency = "MZN",
  period,
  size = "md",
  tone = "neutral",
  style
}) {
  const sizes = {
    sm: "var(--text-sm)",
    md: "var(--text-body-size)",
    lg: "var(--text-h2)",
    metric: "var(--text-metric)"
  };
  const tones = {
    neutral: "var(--money-neutral)",
    in: "var(--money-in)",
    out: "var(--money-out)",
    muted: "var(--text-muted)"
  };
  const neg = amount < 0,
    abs = Math.abs(Number(amount) || 0);
  const int = Math.trunc(abs),
    frac = Math.round((abs - int) * 100);
  const grouped = String(int).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  const n = (neg ? "-" : "") + grouped + (frac ? "," + String(frac).padStart(2, "0") : "");
  return /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "baseline",
      gap: 4,
      fontFamily: "var(--font-mono)",
      fontVariantNumeric: "tabular-nums",
      fontSize: sizes[size] || sizes.md,
      letterSpacing: size === "metric" ? "var(--text-metric-ls)" : "-0.01em",
      fontWeight: size === "metric" || size === "lg" ? "var(--weight-semibold)" : "var(--weight-medium)",
      color: tones[tone] || tones.neutral,
      ...style
    }
  }, n, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "0.72em",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-muted)"
    }
  }, currency, period ? " / " + period : ""));
}
Object.assign(__ds_scope, { MoneyValue });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/MoneyValue.jsx", error: String((e && e.message) || e) }); }

// components/data/PaymentMethod.jsx
try { (() => {
const PAYMENT_METHODS = {
  mpesa: {
    label: "M-Pesa",
    logo: "mpesa.png",
    bg: "#e30613"
  },
  emola: {
    label: "e-Mola",
    logo: "emola.png",
    bg: "#ee7623"
  },
  transferencia: {
    label: "Transferência",
    icon: "landmark",
    bg: "var(--surface-sunken)",
    fg: "var(--text-body)"
  },
  dinheiro: {
    label: "Dinheiro",
    icon: "banknote",
    bg: "var(--status-active-bg)",
    fg: "var(--status-active-fg)"
  },
  outro: {
    label: "Outro",
    icon: "credit-card",
    bg: "var(--surface-sunken)",
    fg: "var(--text-muted)"
  }
};
function PaymentMethod({
  method = "outro",
  size = 22,
  showLabel = true,
  assetsBase = "/assets/logos/",
  style
}) {
  const m = PAYMENT_METHODS[method] || PAYMENT_METHODS.outro;
  return /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 8,
      whiteSpace: "nowrap",
      ...style
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: size,
      height: size,
      flex: "0 0 auto",
      borderRadius: "var(--radius-xs)",
      overflow: "hidden",
      background: m.bg,
      boxShadow: "0 0 0 1px rgba(20,23,29,.07)"
    }
  }, m.logo ? /*#__PURE__*/React.createElement("img", {
    src: assetsBase + m.logo,
    alt: m.label,
    style: {
      width: "100%",
      height: "100%",
      objectFit: "cover"
    }
  }) : /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: m.icon,
    size: Math.round(size * 0.62),
    color: m.fg
  })), showLabel ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-body)"
    }
  }, m.label) : null);
}
Object.assign(__ds_scope, { PAYMENT_METHODS, PaymentMethod });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/PaymentMethod.jsx", error: String((e && e.message) || e) }); }

// components/data/ServiceLogo.jsx
try { (() => {
const CATEGORY = {
  dominio: {
    icon: "globe",
    bg: "var(--cat-dominio-bg)",
    fg: "var(--cat-dominio-fg)"
  },
  hospedagem: {
    icon: "server",
    bg: "var(--cat-hospedagem-bg)",
    fg: "var(--cat-hospedagem-fg)"
  },
  email: {
    icon: "mail",
    bg: "var(--cat-email-bg)",
    fg: "var(--cat-email-fg)"
  },
  ia: {
    icon: "sparkles",
    bg: "var(--cat-ia-bg)",
    fg: "var(--cat-ia-fg)"
  },
  software: {
    icon: "app-window",
    bg: "var(--cat-software-bg)",
    fg: "var(--cat-software-fg)"
  },
  manutencao: {
    icon: "wrench",
    bg: "var(--cat-manutencao-bg)",
    fg: "var(--cat-manutencao-fg)"
  },
  desenvolvimento: {
    icon: "code",
    bg: "var(--cat-desenvolvimento-bg)",
    fg: "var(--cat-desenvolvimento-fg)"
  },
  outro: {
    icon: "package",
    bg: "var(--cat-outro-bg)",
    fg: "var(--cat-outro-fg)"
  }
};
/** Known services → file name (served from assets/logos/). Add a local assets/logos/<file>
 *  and its entry here to switch a service over from the monogram fallback to a real logo. */
const LOGO_LIBRARY = {};
function resolveServiceLogo(name = "", assetsBase = "/assets/logos/") {
  const key = String(name).trim().toLowerCase();
  const file = LOGO_LIBRARY[key] || Object.entries(LOGO_LIBRARY).find(([k]) => key.startsWith(k))?.[1];
  if (!file) return null;
  return /^https?:\/\//.test(file) ? file : assetsBase + file;
}
function ServiceLogo({
  name = "",
  category = "outro",
  src,
  size = 40,
  assetsBase = "/assets/logos/",
  style
}) {
  const c = CATEGORY[category] || CATEGORY.outro;
  const radius = size >= 40 ? "var(--radius-md)" : "var(--radius-sm)";
  const [broken, setBroken] = React.useState(false);
  const url = src || resolveServiceLogo(name, assetsBase);
  if (url && !broken) return /*#__PURE__*/React.createElement("img", {
    src: url,
    "data-logo": true,
    alt: name,
    title: name,
    onError: () => setBroken(true),
    style: {
      width: size,
      height: size,
      flex: "0 0 auto",
      objectFit: "contain",
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: radius,
      padding: Math.round(size * 0.12),
      ...style
    }
  });
  const initials = name.replace(/[^\p{L}\p{N} ]/gu, "").split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0]).join("").toUpperCase();
  return /*#__PURE__*/React.createElement("span", {
    title: name,
    style: {
      position: "relative",
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: size,
      height: size,
      flex: "0 0 auto",
      borderRadius: radius,
      background: c.bg,
      color: c.fg,
      fontFamily: "var(--font-sans)",
      fontSize: Math.round(size * 0.36),
      fontWeight: "var(--weight-semibold)",
      letterSpacing: "-0.02em",
      ...style
    }
  }, initials || /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: c.icon,
    size: Math.round(size * 0.5)
  }), initials ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: c.icon,
    size: Math.max(9, Math.round(size * 0.26)),
    style: {
      position: "absolute",
      right: -2,
      bottom: -2,
      background: "var(--surface-card)",
      borderRadius: "var(--radius-pill)",
      padding: 2,
      boxShadow: "0 0 0 1px var(--border-subtle)",
      color: c.fg
    }
  }) : null);
}
const SERVICE_CATEGORIES = CATEGORY;
Object.assign(__ds_scope, { LOGO_LIBRARY, resolveServiceLogo, ServiceLogo, SERVICE_CATEGORIES });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/ServiceLogo.jsx", error: String((e && e.message) || e) }); }

// components/data/StatCard.jsx
try { (() => {
function StatCard({
  label,
  value,
  currency,
  period,
  delta,
  deltaTone = "neutral",
  icon,
  tone = "neutral",
  footnote,
  onClick,
  style
}) {
  const [hover, setHover] = React.useState(false);
  const accents = {
    neutral: "var(--text-strong)",
    in: "var(--money-in)",
    out: "var(--money-out)",
    due: "var(--status-due-fg)"
  };
  return /*#__PURE__*/React.createElement("div", {
    onClick: onClick,
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 8,
      padding: 16,
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-lg)",
      boxShadow: onClick && hover ? "var(--shadow-raised)" : "var(--shadow-card)",
      cursor: onClick ? "pointer" : "default",
      transition: "box-shadow var(--dur-base) var(--ease-standard)",
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1,
      fontSize: "var(--text-label)",
      letterSpacing: "var(--text-label-ls)",
      textTransform: "uppercase",
      fontWeight: "var(--weight-semibold)",
      color: "var(--text-muted)"
    }
  }, label), icon ? /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: 15,
    color: "var(--text-faint)"
  }) : null), currency ? /*#__PURE__*/React.createElement(__ds_scope.MoneyValue, {
    amount: value,
    currency: currency,
    period: period,
    size: "metric",
    tone: tone === "neutral" ? "neutral" : tone === "out" ? "out" : tone === "in" ? "in" : "neutral"
  }) : /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      fontVariantNumeric: "tabular-nums",
      fontSize: "var(--text-metric)",
      lineHeight: "var(--text-metric-lh)",
      letterSpacing: "var(--text-metric-ls)",
      fontWeight: "var(--weight-semibold)",
      color: accents[tone] || accents.neutral
    }
  }, value), delta || footnote ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 6,
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, delta ? /*#__PURE__*/React.createElement("span", {
    style: {
      color: deltaTone === "in" ? "var(--money-in)" : deltaTone === "out" ? "var(--money-out)" : "var(--text-muted)",
      fontWeight: "var(--weight-medium)"
    }
  }, delta) : null, footnote) : null);
}
Object.assign(__ds_scope, { StatCard });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/StatCard.jsx", error: String((e && e.message) || e) }); }

// components/data/Timeline.jsx
try { (() => {
const KIND = {
  criado: ["plus", "var(--text-muted)"],
  pagamento: ["banknote", "var(--green-600)"],
  activado: ["circle-check", "var(--green-600)"],
  lembrete: ["mail", "var(--accent)"],
  vencido: ["circle-alert", "var(--red-600)"],
  suspenso: ["pause", "var(--ink-800)"],
  alterado: ["pencil", "var(--text-muted)"],
  cancelado: ["circle-slash", "var(--text-faint)"]
};
function Timeline({
  items = [],
  style
}) {
  return /*#__PURE__*/React.createElement("ol", {
    style: {
      listStyle: "none",
      margin: 0,
      padding: 0,
      display: "flex",
      flexDirection: "column",
      ...style
    }
  }, items.map((it, i) => {
    const [icon, color] = KIND[it.kind] || KIND.alterado;
    const last = i === items.length - 1;
    return /*#__PURE__*/React.createElement("li", {
      key: i,
      style: {
        display: "grid",
        gridTemplateColumns: "26px 1fr",
        gap: 10
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: "flex",
        flexDirection: "column",
        alignItems: "center"
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        width: 26,
        height: 26,
        borderRadius: "50%",
        background: "var(--surface-card)",
        border: "1px solid var(--border-subtle)"
      }
    }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
      name: icon,
      size: 13,
      color: color
    })), !last ? /*#__PURE__*/React.createElement("span", {
      style: {
        flex: 1,
        width: 1,
        background: "var(--border-subtle)",
        minHeight: 14
      }
    }) : null), /*#__PURE__*/React.createElement("div", {
      style: {
        paddingBottom: last ? 0 : 16,
        display: "flex",
        flexDirection: "column",
        gap: 2
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        fontFamily: "var(--font-mono)",
        fontSize: "var(--text-xs)",
        color: "var(--text-faint)"
      }
    }, it.date), /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: "var(--text-sm)",
        color: "var(--text-strong)",
        fontWeight: "var(--weight-medium)"
      }
    }, it.title), it.description ? /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: "var(--text-sm)",
        color: "var(--text-muted)"
      }
    }, it.description) : null));
  }));
}
Object.assign(__ds_scope, { Timeline });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/Timeline.jsx", error: String((e && e.message) || e) }); }

// components/feedback/AlertBanner.jsx
try { (() => {
const TONES = {
  info: {
    bg: "var(--accent-soft)",
    bd: "var(--accent-soft-border)",
    fg: "var(--text-accent)",
    icon: "info"
  },
  warning: {
    bg: "var(--status-due-bg)",
    bd: "var(--amber-100)",
    fg: "var(--status-due-fg)",
    icon: "triangle-alert"
  },
  danger: {
    bg: "var(--status-overdue-bg)",
    bd: "var(--red-100)",
    fg: "var(--status-overdue-fg)",
    icon: "circle-alert"
  },
  success: {
    bg: "var(--status-active-bg)",
    bd: "var(--green-100)",
    fg: "var(--status-active-fg)",
    icon: "circle-check"
  }
};
function AlertBanner({
  tone = "info",
  title,
  children,
  action,
  style
}) {
  const t = TONES[tone] || TONES.info;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "flex-start",
      gap: 10,
      padding: "12px 14px",
      background: t.bg,
      border: "1px solid " + t.bd,
      borderRadius: "var(--radius-md)",
      ...style
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: t.icon,
    size: 17,
    color: t.fg,
    style: {
      marginTop: 1
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, title ? /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-sm)",
      color: t.fg,
      fontWeight: "var(--weight-semibold)"
    }
  }, title) : null, children ? /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-body)"
    }
  }, children) : null), action);
}
Object.assign(__ds_scope, { AlertBanner });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/AlertBanner.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Dialog.jsx
try { (() => {
function Dialog({
  open = true,
  title,
  description,
  children,
  footer,
  width = 460,
  onClose,
  style
}) {
  if (!open) return null;
  return /*#__PURE__*/React.createElement("div", {
    onClick: onClose,
    style: {
      position: "fixed",
      inset: 0,
      zIndex: 60,
      display: "flex",
      alignItems: "flex-start",
      justifyContent: "center",
      padding: "8vh 16px",
      background: "var(--scrim)",
      backdropFilter: "blur(2px)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    role: "dialog",
    "aria-modal": "true",
    style: {
      width,
      maxWidth: "100%",
      maxHeight: "84vh",
      display: "flex",
      flexDirection: "column",
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-lg)",
      boxShadow: "var(--shadow-overlay)",
      animation: "none",
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "flex-start",
      gap: 12,
      padding: "16px 16px 0",
      flex: "0 0 auto"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: "flex",
      flexDirection: "column",
      gap: 3
    }
  }, /*#__PURE__*/React.createElement("h3", {
    style: {
      fontSize: "var(--text-h3)",
      letterSpacing: "var(--text-h3-ls)"
    }
  }, title), description ? /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, description) : null), onClose ? /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    icon: "x",
    label: "Fechar",
    size: "sm",
    onClick: onClose
  }) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 16,
      overflowY: "auto",
      flex: "1 1 auto",
      minHeight: 0
    }
  }, children), footer ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "flex-end",
      gap: 8,
      padding: "12px 16px",
      borderTop: "1px solid var(--border-subtle)",
      background: "var(--table-head-bg)",
      borderRadius: "0 0 var(--radius-lg) var(--radius-lg)",
      flex: "0 0 auto"
    }
  }, footer) : null));
}
Object.assign(__ds_scope, { Dialog });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Dialog.jsx", error: String((e && e.message) || e) }); }

// components/feedback/EmptyState.jsx
try { (() => {
function EmptyState({
  icon = "inbox",
  title,
  description,
  action,
  style
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      gap: 8,
      padding: "40px 24px",
      textAlign: "center",
      ...style
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: 40,
      height: 40,
      borderRadius: "var(--radius-md)",
      background: "var(--surface-sunken)"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: 20,
    color: "var(--text-faint)"
  })), /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-h3)",
      color: "var(--text-strong)"
    }
  }, title), description ? /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      maxWidth: 320,
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, description) : null, action ? /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6
    }
  }, action) : null);
}
Object.assign(__ds_scope, { EmptyState });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/EmptyState.jsx", error: String((e && e.message) || e) }); }

// components/feedback/StatusBadge.jsx
try { (() => {
const STATUS = {
  activo: {
    label: "Activo",
    fg: "var(--status-active-fg)",
    bg: "var(--status-active-bg)",
    dot: "var(--status-active-dot)"
  },
  a_vencer: {
    label: "A vencer",
    fg: "var(--status-due-fg)",
    bg: "var(--status-due-bg)",
    dot: "var(--status-due-dot)"
  },
  vencido: {
    label: "Vencido",
    fg: "var(--status-overdue-fg)",
    bg: "var(--status-overdue-bg)",
    dot: "var(--status-overdue-dot)"
  },
  suspenso: {
    label: "Suspenso",
    fg: "var(--status-suspended-fg)",
    bg: "var(--status-suspended-bg)",
    dot: "var(--status-suspended-dot)"
  },
  cancelado: {
    label: "Cancelado",
    fg: "var(--status-cancelled-fg)",
    bg: "var(--status-cancelled-bg)",
    dot: "var(--status-cancelled-dot)"
  }
};
function StatusBadge({
  status = "activo",
  label,
  size = "md",
  style
}) {
  const s = STATUS[status] || STATUS.activo;
  const sm = size === "sm";
  return /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 6,
      height: sm ? 20 : 24,
      padding: sm ? "0 7px" : "0 9px",
      borderRadius: "var(--radius-pill)",
      background: s.bg,
      color: s.fg,
      fontSize: sm ? "var(--text-xs)" : "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      whiteSpace: "nowrap",
      ...style
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 6,
      height: 6,
      borderRadius: "50%",
      background: s.dot,
      flex: "0 0 auto"
    }
  }), label || s.label);
}
Object.assign(__ds_scope, { STATUS, StatusBadge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/StatusBadge.jsx", error: String((e && e.message) || e) }); }

// components/data/ServiceCard.jsx
try { (() => {
function ServiceCard({
  name,
  client,
  category = "outro",
  logoSrc,
  assetsBase,
  description,
  amount,
  currency = "MZN",
  period,
  dueLabel,
  status = "activo",
  statusLabel,
  action = "Ver serviço",
  onAction,
  style
}) {
  const [hover, setHover] = React.useState(false);
  return /*#__PURE__*/React.createElement("div", {
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12,
      padding: 16,
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-lg)",
      boxShadow: hover ? "var(--shadow-raised)" : "var(--shadow-card)",
      transition: "box-shadow var(--dur-base) var(--ease-standard)",
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 11
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.ServiceLogo, {
    name: name,
    category: category,
    src: logoSrc,
    size: 40,
    assetsBase: assetsBase
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      minWidth: 0,
      display: "flex",
      flexDirection: "column",
      gap: 1
    }
  }, /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-h3)",
      color: "var(--text-strong)",
      letterSpacing: "var(--text-h3-ls)",
      overflow: "hidden",
      textOverflow: "ellipsis",
      whiteSpace: "nowrap"
    }
  }, name), client ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, client) : null)), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 6
    }
  }, description ? /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)",
      display: "-webkit-box",
      WebkitLineClamp: 2,
      WebkitBoxOrient: "vertical",
      overflow: "hidden"
    }
  }, description) : null, /*#__PURE__*/React.createElement(__ds_scope.MoneyValue, {
    amount: amount,
    currency: currency,
    period: period,
    size: "lg"
  }), dueLabel ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, dueLabel) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      paddingTop: 12,
      borderTop: "1px solid var(--border-subtle)"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.StatusBadge, {
    status: status,
    label: statusLabel,
    size: "sm"
  }), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "sm",
    variant: "ghost",
    iconEnd: "arrow-right",
    onClick: onAction
  }, action)));
}
Object.assign(__ds_scope, { ServiceCard });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/ServiceCard.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Tag.jsx
try { (() => {
function Tag({
  children,
  tone = "neutral",
  icon,
  style
}) {
  const tones = {
    neutral: ["var(--surface-sunken)", "var(--text-body)"],
    accent: ["var(--accent-soft)", "var(--text-accent)"],
    outline: ["transparent", "var(--text-muted)"]
  };
  const [bg, fg] = tones[tone] || tones.neutral;
  return /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 5,
      height: 22,
      padding: "0 8px",
      borderRadius: "var(--radius-xs)",
      background: bg,
      color: fg,
      border: tone === "outline" ? "1px solid var(--border-subtle)" : "1px solid transparent",
      fontSize: "var(--text-xs)",
      fontWeight: "var(--weight-medium)",
      whiteSpace: "nowrap",
      ...style
    }
  }, icon, children);
}
Object.assign(__ds_scope, { Tag });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Tag.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Toast.jsx
try { (() => {
function Toast({
  tone = "success",
  title,
  children,
  onClose,
  style
}) {
  const map = {
    success: ["circle-check", "var(--green-600)"],
    danger: ["circle-alert", "var(--red-600)"],
    info: ["info", "var(--accent)"]
  };
  const [icon, color] = map[tone] || map.info;
  return /*#__PURE__*/React.createElement("div", {
    role: "status",
    style: {
      display: "flex",
      alignItems: "flex-start",
      gap: 10,
      width: 320,
      padding: "12px 13px",
      background: "var(--surface-card)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-md)",
      boxShadow: "var(--shadow-overlay)",
      ...style
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: icon,
    size: 17,
    color: color,
    style: {
      marginTop: 1
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  }, title), children ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, children) : null), onClose ? /*#__PURE__*/React.createElement("span", {
    onClick: onClose,
    style: {
      cursor: "pointer",
      display: "flex"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "x",
    size: 14,
    color: "var(--text-faint)"
  })) : null);
}
Object.assign(__ds_scope, { Toast });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Toast.jsx", error: String((e && e.message) || e) }); }

// components/navigation/PageHeader.jsx
try { (() => {
function PageHeader({
  eyebrow,
  title,
  meta,
  actions,
  back,
  onBack,
  style
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "flex-end",
      gap: 16,
      flexWrap: "wrap",
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 240,
      display: "flex",
      flexDirection: "column",
      gap: 4
    }
  }, back ? /*#__PURE__*/React.createElement("button", {
    type: "button",
    onClick: onBack,
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 5,
      alignSelf: "flex-start",
      border: "none",
      background: "none",
      padding: 0,
      color: "var(--text-muted)",
      font: "inherit",
      fontSize: "var(--text-sm)",
      cursor: "pointer"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "arrow-left",
    size: 14
  }), back) : eyebrow ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-label)",
      letterSpacing: "var(--text-label-ls)",
      textTransform: "uppercase",
      fontWeight: "var(--weight-semibold)",
      color: "var(--text-muted)"
    }
  }, eyebrow) : null, /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: "var(--text-h1)",
      lineHeight: "var(--text-h1-lh)",
      letterSpacing: "var(--text-h1-ls)"
    }
  }, title), meta ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      flexWrap: "wrap",
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, meta) : null), actions ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      flexWrap: "wrap"
    }
  }, actions) : null);
}
Object.assign(__ds_scope, { PageHeader });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/PageHeader.jsx", error: String((e && e.message) || e) }); }

// components/navigation/SidebarNav.jsx
try { (() => {
const NAV_ITEMS = [{
  id: "dashboard",
  label: "Dashboard",
  icon: "layout-dashboard"
}, {
  id: "clientes",
  label: "Clientes",
  icon: "users"
}, {
  id: "servicos",
  label: "Serviços",
  icon: "package"
}, {
  id: "vencimentos",
  label: "Vencimentos",
  icon: "calendar-clock"
}, {
  id: "pagamentos",
  label: "Pagamentos",
  icon: "banknote"
}, {
  id: "financas",
  label: "Finanças",
  icon: "chart-line"
}, {
  id: "historico",
  label: "Histórico",
  icon: "history"
}, {
  id: "configuracoes",
  label: "Configurações",
  icon: "settings"
}];
function Item({
  item,
  active,
  onSelect,
  collapsed
}) {
  const [hover, setHover] = React.useState(false);
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    title: collapsed ? item.label : undefined,
    onClick: () => onSelect && onSelect(item.id),
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      width: "100%",
      height: 34,
      padding: collapsed ? 0 : "0 10px",
      justifyContent: collapsed ? "center" : "flex-start",
      border: "none",
      borderRadius: "var(--radius-sm)",
      background: active ? "var(--surface-card)" : hover ? "var(--nav-hover)" : "transparent",
      boxShadow: active ? "var(--shadow-sm)" : "none",
      color: active ? "var(--text-strong)" : "var(--text-body)",
      font: "inherit",
      fontSize: "var(--text-sm)",
      fontWeight: active ? "var(--weight-medium)" : "var(--weight-regular)",
      whiteSpace: "nowrap",
      cursor: "pointer",
      textAlign: "left",
      transition: "var(--transition-control)"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: item.icon,
    size: 16,
    color: active ? "var(--accent)" : "var(--text-muted)",
    style: {
      flex: "0 0 auto"
    }
  }), collapsed ? null : /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1
    }
  }, item.label), !collapsed && item.badge ? /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-mono)",
      fontSize: "var(--text-xs)",
      color: "var(--status-due-fg)",
      background: "var(--status-due-bg)",
      borderRadius: "var(--radius-pill)",
      padding: "1px 6px"
    }
  }, item.badge) : null);
}
function SidebarNav({
  items = NAV_ITEMS,
  active = "dashboard",
  onSelect,
  brand = "Vencia",
  logoSrc = "/assets/logo-mark.png",
  footer,
  collapsed = false,
  onToggleCollapse,
  style
}) {
  return /*#__PURE__*/React.createElement("nav", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
      width: collapsed ? "var(--sidebar-w-collapsed, 60px)" : "var(--sidebar-w)",
      flex: "0 0 auto",
      height: "100%",
      padding: collapsed ? "14px 8px" : "14px 12px",
      background: "var(--surface-app)",
      borderRight: "1px solid var(--border-subtle)",
      transition: "width var(--transition-control, 0.15s ease)",
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 9,
      padding: "4px 6px",
      justifyContent: collapsed ? "center" : "flex-start"
    }
  }, /*#__PURE__*/React.createElement("img", {
    src: logoSrc,
    alt: "",
    style: {
      width: 26,
      height: 26,
      borderRadius: "var(--radius-sm)",
      objectFit: "contain",
      flex: "0 0 auto"
    }
  }), collapsed ? null : /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1,
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-semibold)",
      color: "var(--text-strong)",
      letterSpacing: "-0.015em",
      whiteSpace: "nowrap",
      overflow: "hidden"
    }
  }, brand)), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, items.map(it => /*#__PURE__*/React.createElement(Item, {
    key: it.id,
    item: it,
    active: it.id === active,
    onSelect: onSelect,
    collapsed: collapsed
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: "auto",
      display: "flex",
      flexDirection: "column",
      gap: 8
    }
  }, collapsed ? null : footer, onToggleCollapse ? /*#__PURE__*/React.createElement("button", {
    type: "button",
    title: collapsed ? "Expandir menu" : "Ocultar menu",
    onClick: onToggleCollapse,
    style: {
      display: "flex",
      alignItems: "center",
      justifyContent: collapsed ? "center" : "flex-start",
      gap: 8,
      height: 30,
      padding: collapsed ? 0 : "0 10px",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-sm)",
      background: "var(--surface-card)",
      color: "var(--text-muted)",
      font: "inherit",
      fontSize: "var(--text-xs)",
      cursor: "pointer"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: collapsed ? "panel-left-open" : "panel-left-close",
    size: 15
  }), collapsed ? null : /*#__PURE__*/React.createElement("span", null, "Ocultar menu")) : null));
}
Object.assign(__ds_scope, { NAV_ITEMS, SidebarNav });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/SidebarNav.jsx", error: String((e && e.message) || e) }); }

// components/navigation/Tabs.jsx
try { (() => {
function Tabs({
  tabs = [],
  active,
  onSelect,
  style
}) {
  return /*#__PURE__*/React.createElement("div", {
    role: "tablist",
    style: {
      display: "flex",
      alignItems: "center",
      gap: 2,
      borderBottom: "1px solid var(--border-subtle)",
      ...style
    }
  }, tabs.map(t => {
    const id = typeof t === "string" ? t : t.id;
    const label = typeof t === "string" ? t : t.label;
    const count = typeof t === "string" ? null : t.count;
    const on = id === active;
    return /*#__PURE__*/React.createElement("button", {
      key: id,
      role: "tab",
      "aria-selected": on,
      onClick: () => onSelect && onSelect(id),
      style: {
        display: "inline-flex",
        alignItems: "center",
        gap: 6,
        height: 36,
        padding: "0 12px",
        border: "none",
        background: "none",
        borderBottom: "2px solid " + (on ? "var(--accent)" : "transparent"),
        marginBottom: -1,
        color: on ? "var(--text-strong)" : "var(--text-muted)",
        font: "inherit",
        fontSize: "var(--text-sm)",
        fontWeight: on ? "var(--weight-medium)" : "var(--weight-regular)",
        whiteSpace: "nowrap",
        cursor: "pointer",
        transition: "var(--transition-control)"
      }
    }, label, count != null ? /*#__PURE__*/React.createElement("span", {
      style: {
        fontFamily: "var(--font-mono)",
        fontSize: "var(--text-xs)",
        color: "var(--text-faint)"
      }
    }, count) : null);
  }));
}
Object.assign(__ds_scope, { Tabs });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/Tabs.jsx", error: String((e && e.message) || e) }); }

// components/navigation/TopBar.jsx
try { (() => {
function TopBar({
  title,
  search = true,
  onSearch,
  actions,
  alertCount,
  themeToggle = true,
  avatarSrc,
  style
}) {
  return /*#__PURE__*/React.createElement("header", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      height: "var(--topbar-h)",
      flex: "0 0 auto",
      padding: "0 var(--gutter)",
      background: "var(--topbar-bg)",
      backdropFilter: "blur(8px)",
      borderBottom: "1px solid var(--border-subtle)",
      ...style
    }
  }, title ? /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-h3)",
      color: "var(--text-strong)",
      letterSpacing: "var(--text-h3-ls)"
    }
  }, title) : null, search ? /*#__PURE__*/React.createElement(__ds_scope.SearchInput, {
    width: 400,
    onChange: onSearch ? e => onSearch(e.target.value) : undefined,
    style: {
      marginLeft: title ? 8 : 0
    }
  }) : null, /*#__PURE__*/React.createElement("div", {
    style: {
      marginLeft: "auto",
      display: "flex",
      alignItems: "center",
      gap: 6
    }
  }, actions, themeToggle ? /*#__PURE__*/React.createElement(__ds_scope.ThemeToggle, null) : null, /*#__PURE__*/React.createElement("div", {
    style: {
      position: "relative",
      display: "flex"
    }
  }, /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    icon: "bell",
    label: "Notifica\xE7\xF5es"
  }), alertCount ? /*#__PURE__*/React.createElement("span", {
    style: {
      position: "absolute",
      top: 4,
      right: 4,
      minWidth: 15,
      height: 15,
      padding: "0 3px",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      borderRadius: "var(--radius-pill)",
      background: "var(--red-600)",
      color: "var(--on-danger)",
      fontFamily: "var(--font-mono)",
      fontSize: 9,
      fontWeight: "var(--weight-semibold)"
    }
  }, alertCount) : null), /*#__PURE__*/React.createElement("span", {
    style: {
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      width: 28,
      height: 28,
      borderRadius: "50%",
      background: "var(--surface-sunken)",
      color: "var(--text-muted)",
      overflow: "hidden",
      flex: "0 0 auto"
    }
  }, avatarSrc ? /*#__PURE__*/React.createElement("img", {
    src: avatarSrc,
    alt: "",
    style: {
      width: "100%",
      height: "100%",
      objectFit: "cover"
    }
  }) : /*#__PURE__*/React.createElement(__ds_scope.Icon, {
    name: "circle-user-round",
    size: 20
  }))));
}
Object.assign(__ds_scope, { TopBar });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/TopBar.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/App.jsx
try { (() => {
const {
  SidebarNav,
  TopBar,
  Toast,
  Button,
  NAV_ITEMS
} = window.SubscriptionManagerDesignSystem_6a702b;
function App() {
  const [route, setRoute] = React.useState({
    view: "dashboard"
  });
  const [toast, setToast] = React.useState(null);
  const [newOpen, setNewOpen] = React.useState(false);
  const [collapsed, setCollapsed] = React.useState(false);
  const showToast = t => {
    setToast(t);
    setTimeout(() => setToast(null), 3200);
  };
  const nav = view => setRoute({
    view
  });
  const openService = id => setRoute({
    view: "servico",
    id,
    from: route.view === "servico" ? "vencimentos" : route.view,
    clienteId: route.clienteId
  });
  const openClient = id => setRoute({
    view: "cliente",
    clienteId: id
  });
  const navActive = route.view === "cliente" ? "clientes" : route.view === "servico" ? "servicos" : route.view;
  const t = window.SM_DATA.totais;
  const navItems = NAV_ITEMS.map(it => it.id === "vencimentos" ? {
    ...it,
    badge: t.aVencer
  } : it.id === "pagamentos" ? {
    ...it,
    badge: t.nAReceber
  } : it);
  let body;
  if (route.view === "dashboard") body = /*#__PURE__*/React.createElement(DashboardScreen, {
    onOpenService: openService,
    onNew: () => setNewOpen(true)
  });else if (route.view === "clientes") body = /*#__PURE__*/React.createElement(ClientsScreen, {
    onOpenClient: openClient,
    onNew: () => setNewOpen(true)
  });else if (route.view === "cliente") body = /*#__PURE__*/React.createElement(ClientDetailScreen, {
    clienteId: route.clienteId,
    onBack: () => nav("clientes"),
    onOpenService: openService
  });else if (route.view === "servico") body = /*#__PURE__*/React.createElement(ServiceDetailScreen, {
    servicoId: route.id,
    onBack: () => route.clienteId ? openClient(route.clienteId) : nav(route.from || "vencimentos"),
    onToast: showToast
  });else if (route.view === "servicos") body = /*#__PURE__*/React.createElement(ServicesScreen, {
    onOpenService: openService,
    onNew: () => setNewOpen(true)
  });else if (route.view === "vencimentos") body = /*#__PURE__*/React.createElement(DueDatesScreen, {
    onOpenService: openService
  });else if (route.view === "pagamentos") body = /*#__PURE__*/React.createElement(PaymentsScreen, null);else if (route.view === "financas") body = /*#__PURE__*/React.createElement(FinanceScreen, null);else if (route.view === "configuracoes") body = /*#__PURE__*/React.createElement(SettingsScreen, null);else body = /*#__PURE__*/React.createElement(HistoryScreen, null);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      height: "100vh",
      overflow: "hidden",
      background: "var(--surface-app)"
    }
  }, /*#__PURE__*/React.createElement(SidebarNav, {
    items: navItems,
    active: navActive,
    onSelect: nav,
    logoSrc: "../../assets/logo-mark.png",
    collapsed: collapsed,
    onToggleCollapse: () => setCollapsed(c => !c),
    footer: /*#__PURE__*/React.createElement("div", {
      style: {
        display: "flex",
        alignItems: "center",
        gap: 8,
        padding: "10px 8px",
        borderTop: "1px solid var(--border-subtle)",
        fontSize: "var(--text-xs)",
        color: "var(--text-muted)"
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        width: 6,
        height: 6,
        borderRadius: "50%",
        background: "var(--green-600)"
      }
    }), "Verifica\xE7\xE3o di\xE1ria \xE0s 07:00")
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      minWidth: 0,
      display: "flex",
      flexDirection: "column"
    }
  }, /*#__PURE__*/React.createElement(TopBar, {
    alertCount: t.aVencer + t.nEmAtraso,
    actions: /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      size: "sm",
      icon: "plus",
      onClick: () => setNewOpen(true)
    }, "Nova assinatura")
  }), /*#__PURE__*/React.createElement("main", {
    style: {
      flex: 1,
      overflowY: "auto",
      padding: "22px var(--gutter) 40px"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: "var(--page-max)",
      margin: "0 auto"
    }
  }, body))), newOpen ? /*#__PURE__*/React.createElement(NewSubscriptionDialog, {
    onClose: () => setNewOpen(false),
    onDone: () => {
      setNewOpen(false);
      showToast({
        title: "Assinatura criada",
        body: "Lembretes automáticos activados."
      });
    }
  }) : null, toast ? /*#__PURE__*/React.createElement("div", {
    style: {
      position: "fixed",
      right: 20,
      bottom: 20,
      zIndex: 80
    }
  }, /*#__PURE__*/React.createElement(Toast, {
    title: toast.title,
    tone: toast.tone,
    onClose: () => setToast(null)
  }, toast.body)) : null);
}
ReactDOM.createRoot(document.getElementById("root")).render(/*#__PURE__*/React.createElement(App, null));
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/App.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/ClientDetailScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  ServiceCard,
  MoneyValue,
  Timeline,
  Icon,
  Tag
} = window.SubscriptionManagerDesignSystem_6a702b;
function SummaryRow({
  label,
  children
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "baseline",
      justifyContent: "space-between",
      gap: 12,
      padding: "8px 0",
      borderBottom: "1px solid var(--border-subtle)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, label), children);
}
function ClientDetailScreen({
  clienteId,
  onBack,
  onOpenService
}) {
  const {
    clientesById,
    servicos,
    historico
  } = window.SM_DATA;
  const c = clientesById[clienteId];
  const list = servicos.filter(s => s.clienteId === clienteId);
  const mrr = Math.round(list.reduce((t, s) => t + (s.periodo === "mês" ? s.valor : s.valor / 12), 0));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 18
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    back: "Clientes",
    onBack: onBack,
    title: c.nome,
    meta: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("span", {
      style: {
        display: "inline-flex",
        alignItems: "center",
        gap: 5
      }
    }, /*#__PURE__*/React.createElement(Icon, {
      name: "mail",
      size: 14,
      assetsBase: window.LOGO_BASE
    }), /*#__PURE__*/React.createElement("a", {
      href: "mailto:" + c.email
    }, c.email)), /*#__PURE__*/React.createElement("span", {
      style: {
        display: "inline-flex",
        alignItems: "center",
        gap: 5
      }
    }, /*#__PURE__*/React.createElement(Icon, {
      name: "phone",
      size: 14,
      assetsBase: window.LOGO_BASE
    }), c.tel), c.empresa ? /*#__PURE__*/React.createElement(Tag, null, c.empresa) : null, /*#__PURE__*/React.createElement("span", null, "Cliente desde ", c.desde)),
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      icon: "pencil"
    }, "Editar"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "plus"
    }, "Novo servi\xE7o"))
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "baseline",
      justifyContent: "space-between"
    }
  }, /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: "var(--text-h2)",
      letterSpacing: "var(--text-h2-ls)"
    }
  }, "Servi\xE7os"), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, list.length, " activos ou pendentes")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill,minmax(232px,1fr))",
      gap: 12
    }
  }, list.map(s => /*#__PURE__*/React.createElement(ServiceCard, {
    key: s.id,
    name: s.nome,
    client: s.plano,
    category: s.categoria,
    assetsBase: window.LOGO_BASE,
    description: s.descricao,
    amount: s.valor,
    period: s.periodo,
    dueLabel: s.dias < 0 ? "Venceu em " + s.vencimento : s.dias <= 10 ? "Vence em " + s.dias + " dias" : "Vence: " + s.vencimento,
    status: s.status,
    onAction: () => onOpenService(s.id)
  })))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Resumo financeiro"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column"
    }
  }, /*#__PURE__*/React.createElement(SummaryRow, {
    label: "Total pago"
  }, /*#__PURE__*/React.createElement(MoneyValue, {
    amount: 9700,
    tone: "in"
  })), /*#__PURE__*/React.createElement(SummaryRow, {
    label: "Total pendente"
  }, /*#__PURE__*/React.createElement(MoneyValue, {
    amount: 1500,
    tone: "out"
  })), /*#__PURE__*/React.createElement(SummaryRow, {
    label: "Receita mensal"
  }, /*#__PURE__*/React.createElement(MoneyValue, {
    amount: mrr
  })), /*#__PURE__*/React.createElement(SummaryRow, {
    label: "Receita anual"
  }, /*#__PURE__*/React.createElement(MoneyValue, {
    amount: mrr * 12
  })), /*#__PURE__*/React.createElement(SummaryRow, {
    label: "\xDAltimo pagamento"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num",
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-strong)"
    }
  }, "15/08/2026")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      paddingTop: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, "Pr\xF3ximo vencimento"), /*#__PURE__*/React.createElement("span", {
    className: "num",
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--status-due-fg)",
      fontWeight: 500
    }
  }, "02/09/2026")))), /*#__PURE__*/React.createElement(Card, {
    title: "Hist\xF3rico",
    subtitle: "Nada \xE9 apagado"
  }, /*#__PURE__*/React.createElement(Timeline, {
    items: historico.slice(0, 5)
  })))));
}
Object.assign(window, {
  ClientDetailScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/ClientDetailScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/ClientsScreen.jsx
try { (() => {
const {
  Card,
  DataTable,
  PageHeader,
  Button,
  Tabs,
  StatusBadge,
  MoneyValue,
  SearchInput,
  Tag,
  EmptyState
} = window.SubscriptionManagerDesignSystem_6a702b;
function ClientsScreen({
  onOpenClient,
  onNew
}) {
  const {
    clientes,
    servicos
  } = window.SM_DATA;
  const [tab, setTab] = React.useState("todos");
  const [q, setQ] = React.useState("");
  const rows = clientes.filter(c => tab === "todos" ? true : c.status === tab).filter(c => (c.nome + c.email + (c.empresa || "")).toLowerCase().includes(q.toLowerCase()));
  const count = id => servicos.filter(s => s.clienteId === id).length;
  const mrr = id => servicos.filter(s => s.clienteId === id).reduce((t, s) => t + (s.periodo === "mês" ? s.valor : s.valor / 12), 0);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: clientes.length + " clientes",
    title: "Clientes",
    actions: /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "plus",
      onClick: onNew
    }, "Nova assinatura")
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 14,
      flexWrap: "wrap"
    }
  }, /*#__PURE__*/React.createElement(Tabs, {
    active: tab,
    onSelect: setTab,
    style: {
      flex: 1,
      minWidth: 260
    },
    tabs: [{
      id: "todos",
      label: "Todos",
      count: clientes.length
    }, {
      id: "activo",
      label: "Activos",
      count: clientes.filter(c => c.status === "activo").length
    }, {
      id: "inactivo",
      label: "Inactivos",
      count: clientes.filter(c => c.status === "inactivo").length
    }]
  }), /*#__PURE__*/React.createElement(SearchInput, {
    width: 260,
    placeholder: "Nome, email ou empresa",
    shortcut: null,
    value: q,
    onChange: e => setQ(e.target.value)
  })), /*#__PURE__*/React.createElement(Card, {
    padding: 0
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: rows,
    onRowClick: r => onOpenClient(r.id),
    emptyState: /*#__PURE__*/React.createElement(EmptyState, {
      icon: "search-x",
      title: "Sem resultados",
      description: "Nenhum cliente corresponde a “" + q + "”."
    }),
    columns: [{
      key: "nome",
      header: "Cliente",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          alignItems: "center",
          gap: 10
        }
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          width: 30,
          height: 30,
          borderRadius: "50%",
          background: "var(--surface-sunken)",
          color: "var(--text-body)",
          fontSize: "var(--text-xs)",
          fontWeight: "var(--weight-semibold)"
        }
      }, r.nome.split(" ").map(w => w[0]).slice(0, 2).join("")), /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          flexDirection: "column"
        }
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, r.nome), /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "var(--text-xs)",
          color: "var(--text-muted)"
        }
      }, r.email)))
    }, {
      key: "empresa",
      header: "Empresa",
      render: r => r.empresa ? /*#__PURE__*/React.createElement(Tag, null, r.empresa) : /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-faint)"
        }
      }, "\u2014")
    }, {
      key: "servicos",
      header: "Serviços",
      align: "right",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num"
      }, count(r.id))
    }, {
      key: "mrr",
      header: "Receita / mês",
      align: "right",
      render: r => /*#__PURE__*/React.createElement(MoneyValue, {
        amount: Math.round(mrr(r.id)),
        size: "sm",
        tone: "muted"
      })
    }, {
      key: "desde",
      header: "Cliente desde",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num",
        style: {
          fontSize: "var(--text-xs)"
        }
      }, r.desde)
    }, {
      key: "status",
      header: "Estado",
      render: r => /*#__PURE__*/React.createElement(StatusBadge, {
        size: "sm",
        status: r.status === "activo" ? "activo" : "cancelado",
        label: r.status === "activo" ? "Activo" : "Inactivo"
      })
    }]
  })));
}
Object.assign(window, {
  ClientsScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/ClientsScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/DashboardScreen.jsx
try { (() => {
const {
  StatCard,
  Card,
  DataTable,
  MoneyValue,
  StatusBadge,
  ServiceLogo,
  AlertBanner,
  Button,
  PageHeader,
  Icon
} = window.SubscriptionManagerDesignSystem_6a702b;
function AttentionList({
  onOpen
}) {
  const t = window.SM_DATA.totais;
  const items = [{
    icon: "calendar-clock",
    text: t.aVencer + " serviços vencem em breve",
    tone: "var(--status-due-fg)"
  }, {
    icon: "circle-alert",
    text: t.nEmAtraso + (t.nEmAtraso === 1 ? " serviço está vencido" : " serviços estão vencidos"),
    tone: "var(--status-overdue-fg)"
  }, {
    icon: "banknote",
    text: t.nAReceber + " pagamentos estão pendentes",
    tone: "var(--status-due-fg)"
  }, {
    icon: "pause",
    text: "1 acesso aguarda suspensão",
    tone: "var(--text-body)"
  }];
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column"
    }
  }, items.map((it, i) => /*#__PURE__*/React.createElement("button", {
    key: i,
    onClick: onOpen,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 9,
      padding: "10px 16px",
      border: "none",
      borderTop: i ? "1px solid var(--border-subtle)" : "none",
      background: "none",
      font: "inherit",
      fontSize: "var(--text-sm)",
      color: "var(--text-body)",
      cursor: "pointer",
      textAlign: "left"
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    name: it.icon,
    size: 15,
    color: it.tone
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1
    }
  }, it.text), /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-right",
    size: 14,
    color: "var(--text-faint)"
  }))));
}
function DashboardScreen({
  onOpenService,
  onNew
}) {
  const {
    servicos,
    clientesById,
    totais
  } = window.SM_DATA;
  const proximos = servicos.filter(s => s.dias >= -30 && s.status !== "suspenso" && s.status !== "cancelado").sort((a, b) => a.dias - b.dias).slice(0, 6);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 18
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: totais.mes,
    title: "Dashboard",
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      icon: "download"
    }, "Exportar"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "plus",
      onClick: onNew
    }, "Nova assinatura"))
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(178px,1fr))",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(StatCard, {
    label: "Receita deste m\xEAs",
    value: totais.entradas,
    currency: "MZN",
    icon: "wallet",
    tone: "in",
    footnote: totais.nPagamentos + " pagamentos"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "Receita esperada",
    value: totais.esperada,
    currency: "MZN",
    icon: "target",
    footnote: "faltam " + String(totais.aReceber).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " MZN"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "Em atraso",
    value: totais.emAtraso,
    currency: "MZN",
    tone: "out",
    icon: "circle-alert",
    footnote: totais.nEmAtraso + (totais.nEmAtraso === 1 ? " serviço" : " serviços")
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "Clientes activos",
    value: totais.clientesActivos,
    icon: "users"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "Servi\xE7os activos",
    value: totais.servicosActivos,
    icon: "package"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "Vencendo em breve",
    value: totais.aVencer,
    tone: "due",
    icon: "calendar-clock",
    footnote: "pr\xF3ximos 10 dias"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "minmax(0,1.85fr) minmax(280px,1fr)",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Pr\xF3ximos vencimentos",
    subtitle: "Ordenado pela data mais pr\xF3xima",
    padding: 0,
    action: /*#__PURE__*/React.createElement(Button, {
      size: "sm",
      iconEnd: "arrow-right"
    }, "Ver todos")
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: proximos,
    onRowClick: r => onOpenService(r.id),
    columns: [{
      key: "nome",
      header: "Serviço",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          alignItems: "center",
          gap: 9
        }
      }, /*#__PURE__*/React.createElement(ServiceLogo, {
        name: r.nome,
        category: r.categoria,
        size: 26,
        assetsBase: window.LOGO_BASE
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, r.nome))
    }, {
      key: "cliente",
      header: "Cliente",
      render: r => clientesById[r.clienteId].nome
    }, {
      key: "vencimento",
      header: "Vencimento",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num",
        style: {
          fontSize: "var(--text-xs)"
        }
      }, r.vencimento)
    }, {
      key: "valor",
      header: "Valor",
      align: "right",
      render: r => /*#__PURE__*/React.createElement(MoneyValue, {
        amount: r.valor,
        size: "sm"
      })
    }, {
      key: "status",
      header: "Estado",
      render: r => /*#__PURE__*/React.createElement(StatusBadge, {
        status: r.status,
        size: "sm",
        label: r.dias < 0 ? "Vencido" : r.dias <= 7 ? `Vence em ${r.dias} dias` : undefined
      })
    }]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(AlertBanner, {
    tone: "warning",
    title: "Requer aten\xE7\xE3o"
  }, "Manuten\xE7\xE3o do site \u2014 Carlos Manuel est\xE1 vencida h\xE1 1 dia."), /*#__PURE__*/React.createElement(Card, {
    title: "Alertas",
    padding: 0
  }, /*#__PURE__*/React.createElement(AttentionList, {
    onOpen: () => onOpenService("s7")
  })), /*#__PURE__*/React.createElement(Card, {
    title: "Receita recorrente"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 2
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "MRR"), /*#__PURE__*/React.createElement(MoneyValue, {
    amount: totais.mrr,
    size: "lg"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 2,
      paddingTop: 12,
      borderTop: "1px solid var(--border-subtle)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "ARR"), /*#__PURE__*/React.createElement(MoneyValue, {
    amount: totais.arr,
    size: "lg"
  })))))));
}
Object.assign(window, {
  DashboardScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/DashboardScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/DueDatesScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  Tabs,
  DataTable,
  MoneyValue,
  StatusBadge,
  ServiceLogo
} = window.SubscriptionManagerDesignSystem_6a702b;
function CalendarMonth({
  onOpen
}) {
  const {
    servicos,
    clientesById
  } = window.SM_DATA;
  const events = {};
  servicos.forEach(s => {
    const [d, m] = s.vencimento.split("/");
    if (m === "09") (events[+d] = events[+d] || []).push(s);
  });
  const first = 2; // 01/09/2026 is a Tuesday
  const cells = [];
  for (let i = 0; i < first; i++) cells.push(null);
  for (let d = 1; d <= 30; d++) cells.push(d);
  const dows = ["Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"];
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(7,1fr)",
      gap: 1,
      background: "var(--border-subtle)",
      border: "1px solid var(--border-subtle)",
      borderRadius: "var(--radius-md)",
      overflow: "hidden"
    }
  }, dows.map(d => /*#__PURE__*/React.createElement("div", {
    key: d,
    style: {
      background: "var(--ink-25)",
      padding: "7px 9px",
      fontSize: "var(--text-label)",
      letterSpacing: "var(--text-label-ls)",
      textTransform: "uppercase",
      fontWeight: "var(--weight-semibold)",
      color: "var(--text-muted)"
    }
  }, d)), cells.map((d, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      minHeight: 78,
      background: d ? "var(--surface-card)" : "var(--ink-25)",
      padding: "6px 7px",
      display: "flex",
      flexDirection: "column",
      gap: 4
    }
  }, d ? /*#__PURE__*/React.createElement("span", {
    className: "num",
    style: {
      fontSize: "var(--text-xs)",
      color: d === 30 ? "var(--text-strong)" : "var(--text-faint)",
      fontWeight: d === 30 ? 600 : 400
    }
  }, String(d).padStart(2, "0")) : null, (events[d] || []).map(s => /*#__PURE__*/React.createElement("button", {
    key: s.id,
    onClick: () => onOpen(s.id),
    style: {
      display: "flex",
      alignItems: "center",
      gap: 5,
      border: "none",
      textAlign: "left",
      background: s.status === "vencido" ? "var(--status-overdue-bg)" : s.status === "a_vencer" ? "var(--status-due-bg)" : "var(--status-active-bg)",
      color: s.status === "vencido" ? "var(--status-overdue-fg)" : s.status === "a_vencer" ? "var(--status-due-fg)" : "var(--status-active-fg)",
      borderRadius: "var(--radius-xs)",
      padding: "3px 5px",
      font: "inherit",
      fontSize: 11,
      cursor: "pointer",
      overflow: "hidden"
    }
  }, /*#__PURE__*/React.createElement(ServiceLogo, {
    name: s.nome,
    category: s.categoria,
    size: 14,
    assetsBase: window.LOGO_BASE
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      overflow: "hidden",
      textOverflow: "ellipsis",
      whiteSpace: "nowrap"
    }
  }, clientesById[s.clienteId].nome.split(" ")[0], " \u2014 ", s.nome))))));
}
function DueDatesScreen({
  onOpenService
}) {
  const {
    servicos,
    clientesById
  } = window.SM_DATA;
  const [view, setView] = React.useState("lista");
  const [tab, setTab] = React.useState("todos");
  const rows = servicos.filter(s => tab === "todos" || s.status === tab).sort((a, b) => a.dias - b.dias);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: "Setembro 2026",
    title: "Vencimentos",
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      variant: view === "lista" ? "primary" : "secondary",
      icon: "list",
      onClick: () => setView("lista")
    }, "Lista"), /*#__PURE__*/React.createElement(Button, {
      variant: view === "calendario" ? "primary" : "secondary",
      icon: "calendar",
      onClick: () => setView("calendario")
    }, "Calend\xE1rio"))
  }), view === "lista" ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Tabs, {
    active: tab,
    onSelect: setTab,
    tabs: [{
      id: "todos",
      label: "Todos",
      count: servicos.length
    }, {
      id: "activo",
      label: "Activos",
      count: servicos.filter(s => s.status === "activo").length
    }, {
      id: "a_vencer",
      label: "A vencer",
      count: servicos.filter(s => s.status === "a_vencer").length
    }, {
      id: "vencido",
      label: "Vencidos",
      count: servicos.filter(s => s.status === "vencido").length
    }, {
      id: "suspenso",
      label: "Suspensos",
      count: servicos.filter(s => s.status === "suspenso").length
    }]
  }), /*#__PURE__*/React.createElement(Card, {
    padding: 0
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: rows,
    onRowClick: r => onOpenService(r.id),
    columns: [{
      key: "nome",
      header: "Serviço",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          alignItems: "center",
          gap: 9
        }
      }, /*#__PURE__*/React.createElement(ServiceLogo, {
        name: r.nome,
        category: r.categoria,
        size: 26,
        assetsBase: window.LOGO_BASE
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          flexDirection: "column"
        }
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, r.nome), /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "var(--text-xs)",
          color: "var(--text-muted)"
        }
      }, r.plano)))
    }, {
      key: "cliente",
      header: "Cliente",
      render: r => clientesById[r.clienteId].nome
    }, {
      key: "periodicidade",
      header: "Período"
    }, {
      key: "vencimento",
      header: "Vencimento",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num",
        style: {
          fontSize: "var(--text-xs)"
        }
      }, r.vencimento)
    }, {
      key: "valor",
      header: "Valor",
      align: "right",
      render: r => /*#__PURE__*/React.createElement(MoneyValue, {
        amount: r.valor,
        size: "sm"
      })
    }, {
      key: "status",
      header: "Estado",
      render: r => /*#__PURE__*/React.createElement(StatusBadge, {
        status: r.status,
        size: "sm",
        label: r.status === "a_vencer" ? "Vence em " + r.dias + " dias" : r.status === "vencido" ? "Vencido há " + Math.abs(r.dias) + " dia" : undefined
      })
    }]
  }))) : /*#__PURE__*/React.createElement(Card, {
    title: "Setembro 2026",
    subtitle: "Clique num evento para abrir o servi\xE7o"
  }, /*#__PURE__*/React.createElement(CalendarMonth, {
    onOpen: onOpenService
  })));
}
Object.assign(window, {
  DueDatesScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/DueDatesScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/FinanceScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  StatCard,
  MoneyValue,
  PaymentMethod,
  Select,
  DataTable,
  Tag
} = window.SubscriptionManagerDesignSystem_6a702b;
function Bar({
  label,
  value,
  max,
  tone = "var(--accent)"
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 10
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 62,
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, label), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1,
      height: 8,
      background: "var(--surface-sunken)",
      borderRadius: "var(--radius-pill)",
      overflow: "hidden"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "block",
      width: Math.round(value / max * 100) + "%",
      height: "100%",
      background: tone,
      borderRadius: "var(--radius-pill)"
    }
  })), /*#__PURE__*/React.createElement(MoneyValue, {
    amount: value,
    size: "sm",
    tone: "muted",
    style: {
      width: 108,
      justifyContent: "flex-end"
    }
  }));
}
function FinanceScreen() {
  const {
    clientesById,
    pagamentosDoMes,
    aReceber,
    totais
  } = window.SM_DATA;
  const porMetodo = pagamentosDoMes.reduce((acc, p) => {
    acc[p.metodo] = (acc[p.metodo] || 0) + p.valor;
    return acc;
  }, {});
  const maxMetodo = Math.max(...Object.values(porMetodo));
  const meses = [["Abril", 6900], ["Maio", 7400], ["Junho", 8100], ["Julho", 9600], ["Agosto", totais.entradas]];
  const maxMes = Math.max(...meses.map(m => m[1]));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: totais.mes,
    title: "Finan\xE7as",
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Select, {
      options: ["Agosto 2026", "Julho 2026", "Últimos 6 meses"],
      style: {
        width: 170
      }
    }), /*#__PURE__*/React.createElement(Button, {
      icon: "download"
    }, "Exportar"))
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(190px,1fr))",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(StatCard, {
    label: "Entradas",
    value: totais.entradas,
    currency: "MZN",
    tone: "in",
    icon: "arrow-down-to-line",
    footnote: totais.nPagamentos + " pagamentos em " + totais.mes
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "A receber",
    value: totais.aReceber,
    currency: "MZN",
    tone: "out",
    icon: "hourglass",
    footnote: totais.nAReceber + " serviços"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "MRR",
    value: totais.mrr,
    currency: "MZN",
    icon: "repeat",
    footnote: "receita mensal recorrente"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "ARR",
    value: totais.arr,
    currency: "MZN",
    icon: "chart-line",
    footnote: "receita anual recorrente"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(320px,1fr))",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Como o dinheiro entrou",
    subtitle: totais.mes + ", por método de pagamento"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, Object.entries(porMetodo).sort((a, b) => b[1] - a[1]).map(([m, v]) => /*#__PURE__*/React.createElement("div", {
    key: m,
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 152
    }
  }, /*#__PURE__*/React.createElement(PaymentMethod, {
    method: m,
    size: 24,
    assetsBase: window.LOGO_BASE
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      flex: 1,
      height: 8,
      background: "var(--surface-sunken)",
      borderRadius: "var(--radius-pill)",
      overflow: "hidden"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: "block",
      width: Math.round(v / maxMetodo * 100) + "%",
      height: "100%",
      background: "var(--green-600)",
      borderRadius: "var(--radius-pill)"
    }
  })), /*#__PURE__*/React.createElement(MoneyValue, {
    amount: v,
    size: "sm",
    tone: "in",
    style: {
      width: 104,
      justifyContent: "flex-end"
    }
  }))))), /*#__PURE__*/React.createElement(Card, {
    title: "Entradas por m\xEAs",
    subtitle: "\xDAltimos 5 meses"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 10
    }
  }, meses.map(([m, v]) => /*#__PURE__*/React.createElement(Bar, {
    key: m,
    label: m,
    value: v,
    max: maxMes
  }))))), /*#__PURE__*/React.createElement(Card, {
    title: "A receber",
    subtitle: "Servi\xE7os vencidos ou a vencer, ainda n\xE3o pagos",
    padding: 0,
    action: /*#__PURE__*/React.createElement(Tag, {
      tone: "accent"
    }, totais.nAReceber, " servi\xE7os")
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: aReceber,
    columns: [{
      key: "nome",
      header: "Serviço",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, r.nome)
    }, {
      key: "cliente",
      header: "Cliente",
      render: r => clientesById[r.clienteId].nome
    }, {
      key: "vencimento",
      header: "Vencimento",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num",
        style: {
          fontSize: "var(--text-xs)"
        }
      }, r.vencimento)
    }, {
      key: "periodicidade",
      header: "Período"
    }, {
      key: "valor",
      header: "Valor",
      align: "right",
      render: r => /*#__PURE__*/React.createElement(MoneyValue, {
        amount: r.valor,
        size: "sm",
        tone: "out"
      })
    }]
  })));
}
Object.assign(window, {
  FinanceScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/FinanceScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/HistoryScreen.jsx
try { (() => {
const {
  PageHeader,
  Card,
  Timeline,
  Tabs,
  Select,
  SearchInput,
  ServiceLogo,
  MoneyValue,
  PaymentMethod
} = window.SubscriptionManagerDesignSystem_6a702b;
const EVENTS = [{
  date: "30/08/2026",
  title: "Pagamento recebido — Hospedagem, Maria Costa",
  description: "900 MZN · M-Pesa · 1 ano",
  kind: "pagamento",
  tipo: "pagamentos"
}, {
  date: "30/08/2026",
  title: "Assinatura renovada — Hospedagem, Maria Costa",
  description: "Novo vencimento: 28/08/2027",
  kind: "activado",
  tipo: "alteracoes"
}, {
  date: "29/08/2026",
  title: "Lembrete enviado — Hospedagem, João Silva",
  description: "3 dias antes · joao@email.com",
  kind: "lembrete",
  tipo: "emails"
}, {
  date: "28/08/2026",
  title: "Pagamento recebido — ChatGPT, Maria Costa",
  description: "500 MZN · Transferência · 1 mês",
  kind: "pagamento",
  tipo: "pagamentos"
}, {
  date: "26/08/2026",
  title: "Lembrete enviado — Email corporativo, Ana Mucavele",
  description: "15 dias antes · ana@mucavele.co.mz",
  kind: "lembrete",
  tipo: "emails"
}, {
  date: "05/08/2026",
  title: "Serviço suspenso — ChatGPT, Hélder Tembe",
  description: "Motivo: pagamento não renovado",
  kind: "suspenso",
  tipo: "alteracoes"
}, {
  date: "02/08/2026",
  title: "Serviço vencido — Manutenção do site, Carlos Manuel",
  description: "Aguarda renovação ou suspensão",
  kind: "vencido",
  tipo: "alteracoes"
}, {
  date: "01/08/2026",
  title: "Email de vencimento enviado — Manutenção do site",
  description: "carlos@cmdigital.co.mz",
  kind: "lembrete",
  tipo: "emails"
}, {
  date: "20/07/2026",
  title: "Serviço criado — Claude, Ana Mucavele",
  description: "700 MZN / mês · acesso individual",
  kind: "criado",
  tipo: "alteracoes"
}];
function HistoryScreen() {
  const [tab, setTab] = React.useState("todos");
  const [q, setQ] = React.useState("");
  const items = EVENTS.filter(e => tab === "todos" || e.tipo === tab).filter(e => (e.title + e.description).toLowerCase().includes(q.toLowerCase()));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: "Registo imut\xE1vel",
    title: "Hist\xF3rico",
    actions: /*#__PURE__*/React.createElement(Select, {
      options: ["Últimos 60 dias", "Este ano", "Tudo"],
      style: {
        width: 170
      }
    })
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      flexWrap: "wrap"
    }
  }, /*#__PURE__*/React.createElement(Tabs, {
    active: tab,
    onSelect: setTab,
    style: {
      flex: 1,
      minWidth: 280
    },
    tabs: [{
      id: "todos",
      label: "Todos",
      count: EVENTS.length
    }, {
      id: "pagamentos",
      label: "Pagamentos",
      count: EVENTS.filter(e => e.tipo === "pagamentos").length
    }, {
      id: "emails",
      label: "Emails",
      count: EVENTS.filter(e => e.tipo === "emails").length
    }, {
      id: "alteracoes",
      label: "Alterações",
      count: EVENTS.filter(e => e.tipo === "alteracoes").length
    }]
  }), /*#__PURE__*/React.createElement(SearchInput, {
    width: 250,
    placeholder: "Cliente, servi\xE7o ou evento",
    shortcut: null,
    value: q,
    onChange: e => setQ(e.target.value)
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Eventos",
    subtitle: "Nada \xE9 apagado \u2014 apenas acrescentado"
  }, /*#__PURE__*/React.createElement(Timeline, {
    items: items
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "\xDAltimos pagamentos"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, window.SM_DATA.pagamentos.slice(0, 4).map(p => {
    const s = window.SM_DATA.servicos.find(x => x.id === p.servicoId) || {};
    return /*#__PURE__*/React.createElement("div", {
      key: p.id,
      style: {
        display: "flex",
        alignItems: "center",
        gap: 10
      }
    }, /*#__PURE__*/React.createElement(ServiceLogo, {
      name: s.nome,
      category: s.categoria,
      size: 28,
      assetsBase: window.LOGO_BASE
    }), /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        minWidth: 0,
        display: "flex",
        flexDirection: "column"
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: "var(--text-sm)",
        color: "var(--text-strong)",
        fontWeight: "var(--weight-medium)",
        overflow: "hidden",
        textOverflow: "ellipsis",
        whiteSpace: "nowrap"
      }
    }, s.nome), /*#__PURE__*/React.createElement(PaymentMethod, {
      method: p.metodo,
      size: 16,
      assetsBase: window.LOGO_BASE
    })), /*#__PURE__*/React.createElement(MoneyValue, {
      amount: p.valor,
      size: "sm",
      tone: "in"
    }));
  }))))));
}
Object.assign(window, {
  HistoryScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/HistoryScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/NewSubscriptionDialog.jsx
try { (() => {
const {
  Dialog,
  Button,
  Input,
  Select,
  Textarea,
  LogoSlot,
  ServiceLogo,
  Tag,
  Icon,
  PaymentMethod
} = window.SubscriptionManagerDesignSystem_6a702b;
const LOGO_BASE = "../../assets/logos/";
const LIBRARY = [{
  nome: "ChatGPT",
  categoria: "ia"
}, {
  nome: "Claude",
  categoria: "ia"
}, {
  nome: "Canva",
  categoria: "software"
}, {
  nome: "Google Workspace",
  categoria: "email"
}, {
  nome: "Microsoft 365",
  categoria: "software"
}, {
  nome: "GitHub",
  categoria: "software"
}, {
  nome: "Adobe",
  categoria: "software"
}, {
  nome: "Dropbox",
  categoria: "software"
}, {
  nome: "Zoom",
  categoria: "software"
}, {
  nome: "Domínio .co.mz",
  categoria: "dominio"
}, {
  nome: "Hospedagem Business",
  categoria: "hospedagem"
}, {
  nome: "Email corporativo",
  categoria: "email"
}, {
  nome: "Manutenção de site",
  categoria: "manutencao"
}];
function NewSubscriptionDialog({
  onClose,
  onDone
}) {
  const {
    clientes
  } = window.SM_DATA;
  const [pick, setPick] = React.useState(LIBRARY[0].nome);
  const svc = LIBRARY.find(l => l.nome === pick) || LIBRARY[0];
  const [metodo, setMetodo] = React.useState("mpesa");
  const [cliente, setCliente] = React.useState(clientes[0]?.id ?? "");
  const novoCliente = cliente === "__novo";
  return /*#__PURE__*/React.createElement(Dialog, {
    width: 720,
    title: "Nova assinatura",
    description: "Escolha um servi\xE7o da biblioteca \u2014 nome, logo e categoria s\xE3o preenchidos automaticamente.",
    onClose: onClose,
    footer: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      onClick: onClose
    }, "Cancelar"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "check",
      onClick: onDone
    }, "Criar assinatura"))
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Biblioteca de servi\xE7os"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 8,
      flexWrap: "wrap"
    }
  }, LIBRARY.map(l => {
    const on = l.nome === pick;
    return /*#__PURE__*/React.createElement("button", {
      key: l.nome,
      onClick: () => setPick(l.nome),
      style: {
        display: "inline-flex",
        alignItems: "center",
        gap: 7,
        padding: "5px 10px 5px 6px",
        background: on ? "var(--accent-soft)" : "var(--surface-card)",
        border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"),
        borderRadius: "var(--radius-pill)",
        color: on ? "var(--text-accent)" : "var(--text-body)",
        font: "inherit",
        fontSize: "var(--text-sm)",
        fontWeight: on ? "var(--weight-medium)" : "var(--weight-regular)",
        cursor: "pointer",
        whiteSpace: "nowrap",
        transition: "var(--transition-control)"
      }
    }, /*#__PURE__*/React.createElement(ServiceLogo, {
      name: l.nome,
      category: l.categoria,
      size: 20,
      assetsBase: LOGO_BASE
    }), l.nome);
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 14,
      padding: 12,
      background: "var(--surface-sunken)",
      borderRadius: "var(--radius-md)"
    }
  }, /*#__PURE__*/React.createElement(ServiceLogo, {
    name: svc.nome,
    category: svc.categoria,
    size: 44,
    assetsBase: LOGO_BASE
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      display: "flex",
      flexDirection: "column",
      gap: 3
    }
  }, /*#__PURE__*/React.createElement("strong", {
    style: {
      fontSize: "var(--text-h3)",
      color: "var(--text-strong)"
    }
  }, svc.nome), /*#__PURE__*/React.createElement("span", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 6
    }
  }, /*#__PURE__*/React.createElement(Tag, null, svc.categoria), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-xs)",
      color: "var(--text-muted)"
    }
  }, "preenchido pela biblioteca"))), /*#__PURE__*/React.createElement(LogoSlot, {
    size: 44,
    label: "Logo personalizado",
    hint: "PNG, JPG, SVG ou WebP"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: novoCliente ? "1fr 1fr 1fr" : "1fr",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Select, {
    label: "Cliente",
    value: cliente,
    onChange: e => setCliente(e.target.value),
    options: [...clientes.map(c => ({
      value: c.id,
      label: c.nome
    })), {
      value: "__novo",
      label: "+ Novo cliente"
    }]
  }), novoCliente ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Input, {
    label: "Nome do novo cliente",
    placeholder: "Nome completo"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Email",
    placeholder: "cliente@email.com"
  })) : null), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr 1fr",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Valor",
    defaultValue: "500",
    suffix: "MZN"
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Periodicidade",
    options: ["Mensal", "Trimestral", "Semestral", "Anual", "Única", "Personalizada"]
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Data de in\xEDcio",
    defaultValue: "30/08/2026",
    icon: "calendar"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Data de vencimento",
    defaultValue: "30/09/2026",
    icon: "calendar-clock"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Dura\xE7\xE3o",
    defaultValue: "30",
    suffix: "dias"
  })), /*#__PURE__*/React.createElement(Textarea, {
    label: "Breve descri\xE7\xE3o do servi\xE7o para este cliente",
    rows: 2,
    defaultValue: "Acesso partilhado à conta de equipe — 2 utilizadores.",
    hint: "Aparece no cart\xE3o do servi\xE7o, na p\xE1gina do cliente e nos emails de aviso."
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Como o cliente paga"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 8,
      flexWrap: "wrap"
    }
  }, ["mpesa", "emola", "transferencia", "dinheiro", "outro"].map(m => {
    const on = m === metodo;
    return /*#__PURE__*/React.createElement("button", {
      key: m,
      onClick: () => setMetodo(m),
      style: {
        display: "inline-flex",
        alignItems: "center",
        padding: "5px 11px 5px 7px",
        background: on ? "var(--accent-soft)" : "var(--surface-card)",
        border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"),
        borderRadius: "var(--radius-pill)",
        cursor: "pointer",
        font: "inherit",
        transition: "var(--transition-control)"
      }
    }, /*#__PURE__*/React.createElement(PaymentMethod, {
      method: m,
      size: 22,
      assetsBase: LOGO_BASE
    }));
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      fontSize: "var(--text-sm)",
      color: "var(--text-muted)"
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "bell",
    size: 15
  }), "Lembretes autom\xE1ticos: 30, 15, 7, 3 e 1 dia antes, no dia e ap\xF3s o vencimento.")));
}
Object.assign(window, {
  NewSubscriptionDialog,
  SERVICE_LIBRARY: LIBRARY,
  LOGO_BASE
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/NewSubscriptionDialog.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/PaymentsScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  DataTable,
  MoneyValue,
  Select,
  SearchInput,
  StatCard,
  ServiceLogo,
  Tag,
  PaymentMethod
} = window.SubscriptionManagerDesignSystem_6a702b;
const fmtMZN = n => String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
function PaymentsScreen() {
  const {
    pagamentos,
    clientesById,
    servicos,
    totais
  } = window.SM_DATA;
  const [metodo, setMetodo] = React.useState("todos");
  const METODOS = [{
    value: "todos",
    label: "Todos os métodos"
  }, {
    value: "mpesa",
    label: "M-Pesa"
  }, {
    value: "emola",
    label: "e-Mola"
  }, {
    value: "transferencia",
    label: "Transferência"
  }, {
    value: "dinheiro",
    label: "Dinheiro"
  }];
  const rows = pagamentos.filter(p => metodo === "todos" || p.metodo === metodo);
  const total = rows.reduce((t, p) => t + p.valor, 0);
  const svc = id => servicos.find(s => s.id === id) || {};
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: totais.mes,
    title: "Pagamentos",
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      icon: "download"
    }, "Exportar CSV"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "plus"
    }, "Registrar pagamento"))
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(190px,1fr))",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(StatCard, {
    label: "Entradas do per\xEDodo",
    value: total,
    currency: "MZN",
    tone: "in",
    icon: "arrow-down-to-line",
    footnote: rows.length + " pagamentos"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "A receber",
    value: totais.aReceber,
    currency: "MZN",
    tone: "out",
    icon: "hourglass",
    footnote: totais.nAReceber + " serviços"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "MRR",
    value: totais.mrr,
    currency: "MZN",
    icon: "repeat"
  }), /*#__PURE__*/React.createElement(StatCard, {
    label: "ARR",
    value: totais.arr,
    currency: "MZN",
    icon: "chart-line"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 8,
      flexWrap: "wrap"
    }
  }, METODOS.slice(1).map(m => {
    const on = m.value === metodo;
    return /*#__PURE__*/React.createElement("button", {
      key: m.value,
      onClick: () => setMetodo(on ? "todos" : m.value),
      style: {
        display: "inline-flex",
        alignItems: "center",
        padding: "5px 11px 5px 7px",
        background: on ? "var(--accent-soft)" : "var(--surface-card)",
        border: "1px solid " + (on ? "var(--accent-soft-border)" : "var(--border-subtle)"),
        borderRadius: "var(--radius-pill)",
        cursor: "pointer",
        font: "inherit",
        transition: "var(--transition-control)"
      }
    }, /*#__PURE__*/React.createElement(PaymentMethod, {
      method: m.value,
      size: 22,
      assetsBase: window.LOGO_BASE
    }));
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "flex-end",
      gap: 10,
      flexWrap: "wrap"
    }
  }, /*#__PURE__*/React.createElement(Select, {
    options: [totais.mes, "Julho 2026", "Junho 2026"],
    style: {
      width: 160
    }
  }), /*#__PURE__*/React.createElement(Select, {
    options: METODOS,
    value: metodo,
    onChange: e => setMetodo(e.target.value),
    style: {
      width: 180
    }
  }), /*#__PURE__*/React.createElement(Select, {
    options: ["Todos os clientes", "João da Silva", "Maria Costa", "Carlos Manuel"],
    style: {
      width: 190
    }
  }), /*#__PURE__*/React.createElement(SearchInput, {
    width: 220,
    placeholder: "Pesquisar pagamento",
    shortcut: null,
    style: {
      marginLeft: "auto"
    }
  })), /*#__PURE__*/React.createElement(Card, {
    padding: 0,
    title: "Total recebido no período",
    subtitle: fmtMZN(total) + " MZN",
    action: /*#__PURE__*/React.createElement(Tag, {
      tone: "accent"
    }, rows.length, " registos")
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: rows,
    columns: [{
      key: "data",
      header: "Data",
      render: r => /*#__PURE__*/React.createElement("span", {
        className: "num",
        style: {
          fontSize: "var(--text-xs)"
        }
      }, r.data)
    }, {
      key: "cliente",
      header: "Cliente",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, clientesById[r.clienteId].nome)
    }, {
      key: "servico",
      header: "Serviço",
      render: r => {
        const s = svc(r.servicoId);
        return /*#__PURE__*/React.createElement("span", {
          style: {
            display: "flex",
            alignItems: "center",
            gap: 8
          }
        }, /*#__PURE__*/React.createElement(ServiceLogo, {
          name: s.nome,
          category: s.categoria,
          size: 24,
          assetsBase: window.LOGO_BASE
        }), s.nome);
      }
    }, {
      key: "periodo",
      header: "Período",
      render: r => /*#__PURE__*/React.createElement(Tag, {
        tone: "outline"
      }, r.periodo)
    }, {
      key: "metodo",
      header: "Método",
      render: r => /*#__PURE__*/React.createElement(PaymentMethod, {
        method: r.metodo,
        size: 22,
        assetsBase: window.LOGO_BASE
      })
    }, {
      key: "valor",
      header: "Valor",
      align: "right",
      render: r => /*#__PURE__*/React.createElement(MoneyValue, {
        amount: r.valor,
        tone: "in",
        size: "sm"
      })
    }]
  })));
}
Object.assign(window, {
  PaymentsScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/PaymentsScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/ServiceDetailScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  ServiceLogo,
  MoneyValue,
  StatusBadge,
  Timeline,
  AlertBanner,
  Dialog,
  Input,
  Select,
  Icon,
  Tag,
  Checkbox,
  PaymentMethod,
  ServiceCard
} = window.SubscriptionManagerDesignSystem_6a702b;
function Field({
  label,
  children
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 3
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, label), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-body-size)",
      color: "var(--text-strong)",
      fontWeight: "var(--weight-medium)"
    }
  }, children));
}
function RenewDialog({
  servico,
  onClose,
  onDone
}) {
  const [metodo, setMetodo] = React.useState("mpesa");
  return /*#__PURE__*/React.createElement(Dialog, {
    title: "Renovar servi\xE7o",
    description: servico.nome + " — período " + servico.periodicidade.toLowerCase(),
    onClose: onClose,
    width: 440,
    footer: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      onClick: onClose
    }, "Cancelar"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "check",
      onClick: onDone
    }, "Confirmar renova\xE7\xE3o"))
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Valor pago",
    defaultValue: String(servico.valor),
    suffix: "MZN"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Data do pagamento",
    defaultValue: "30/08/2026",
    icon: "calendar"
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Per\xEDodo",
    options: ["1 mês", "3 meses", "6 meses", "1 ano"],
    defaultValue: servico.periodo === "mês" ? "1 mês" : "1 ano"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 6
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: "var(--text-sm)",
      fontWeight: "var(--weight-medium)",
      color: "var(--text-strong)"
    }
  }, "M\xE9todo"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 6,
      flexWrap: "wrap"
    }
  }, ["mpesa", "emola", "transferencia", "dinheiro"].map(m => /*#__PURE__*/React.createElement("button", {
    key: m,
    onClick: () => setMetodo(m),
    title: m,
    style: {
      display: "inline-flex",
      alignItems: "center",
      padding: 4,
      background: metodo === m ? "var(--accent-soft)" : "var(--surface-card)",
      border: "1px solid " + (metodo === m ? "var(--accent-soft-border)" : "var(--border-subtle)"),
      borderRadius: "var(--radius-sm)",
      cursor: "pointer"
    }
  }, /*#__PURE__*/React.createElement(PaymentMethod, {
    method: m,
    size: 22,
    showLabel: false,
    assetsBase: window.LOGO_BASE
  }))))), /*#__PURE__*/React.createElement(Input, {
    label: "Observa\xE7\xF5es",
    placeholder: "Opcional",
    style: {
      gridColumn: "1 / -1"
    }
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 12,
      padding: "10px 12px",
      background: "var(--surface-sunken)",
      borderRadius: "var(--radius-sm)",
      display: "flex",
      alignItems: "center",
      gap: 8,
      fontSize: "var(--text-sm)",
      color: "var(--text-body)"
    }
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "calendar-check",
    size: 15,
    color: "var(--text-muted)"
  }), "Novo vencimento calculado: ", /*#__PURE__*/React.createElement("strong", {
    className: "num",
    style: {
      color: "var(--text-strong)"
    }
  }, servico.periodo === "mês" ? "01/10/2026" : "02/09/2027")));
}
function SuspendDialog({
  onClose,
  onDone
}) {
  return /*#__PURE__*/React.createElement(Dialog, {
    title: "Suspender servi\xE7o",
    description: "O hist\xF3rico \xE9 mantido; os lembretes param.",
    onClose: onClose,
    width: 420,
    footer: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      onClick: onClose
    }, "Cancelar"), /*#__PURE__*/React.createElement(Button, {
      variant: "danger",
      icon: "pause",
      onClick: onDone
    }, "Suspender acesso"))
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Select, {
    label: "Motivo",
    options: ["Pagamento não renovado", "Pedido do cliente", "Serviço substituído", "Outro"]
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Data da suspens\xE3o",
    defaultValue: "30/08/2026",
    icon: "calendar"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Observa\xE7\xE3o",
    placeholder: "Registro interno"
  }), /*#__PURE__*/React.createElement(Checkbox, {
    label: "Enviar email de aviso ao cliente",
    checked: true,
    description: "Utiliza o template \u201CServi\xE7o terminado\u201D."
  })));
}
function ServiceDetailScreen({
  servicoId,
  onBack,
  onToast
}) {
  const {
    servicos,
    clientesById,
    historico
  } = window.SM_DATA;
  const s = servicos.find(x => x.id === servicoId) || servicos[0];
  const c = clientesById[s.clienteId];
  const [dialog, setDialog] = React.useState(null);
  const [status, setStatus] = React.useState(s.status);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 18
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    back: c.nome,
    onBack: onBack,
    title: /*#__PURE__*/React.createElement("span", {
      style: {
        display: "inline-flex",
        alignItems: "center",
        gap: 12
      }
    }, /*#__PURE__*/React.createElement(ServiceLogo, {
      name: s.nome,
      category: s.categoria,
      size: 38,
      assetsBase: window.LOGO_BASE
    }), s.nome),
    meta: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Tag, null, s.plano), /*#__PURE__*/React.createElement(Tag, {
      tone: "outline"
    }, s.periodicidade), /*#__PURE__*/React.createElement("span", null, c.email)),
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "refresh-cw",
      onClick: () => setDialog("renew")
    }, "Renovar"), /*#__PURE__*/React.createElement(Button, {
      icon: "banknote"
    }, "Registrar pagamento"), /*#__PURE__*/React.createElement(Button, {
      icon: "pencil"
    }, "Editar"), /*#__PURE__*/React.createElement(Button, {
      variant: "danger",
      icon: "pause",
      onClick: () => setDialog("suspend")
    }, "Suspender"))
  }), status === "vencido" ? /*#__PURE__*/React.createElement(AlertBanner, {
    tone: "danger",
    title: "Este acesso terminou"
  }, "Terminou em ", s.vencimento, ". \xC9 necess\xE1rio renovar ou suspender.") : null, status === "suspenso" ? /*#__PURE__*/React.createElement(AlertBanner, {
    tone: "warning",
    title: "Servi\xE7o suspenso em 30/08/2026"
  }, "Motivo: pagamento n\xE3o renovado. Os lembretes est\xE3o parados.") : null, status === "a_vencer" ? /*#__PURE__*/React.createElement(AlertBanner, {
    tone: "warning",
    title: "Vence em " + s.dias + " dias"
  }, "Pr\xF3ximo aviso autom\xE1tico: 3 dias antes do vencimento.") : null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "minmax(0,1.9fr) minmax(280px,1fr)",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Assinatura",
    subtitle: s.descricao
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(140px,1fr))",
      gap: 18
    }
  }, /*#__PURE__*/React.createElement(Field, {
    label: "Cliente"
  }, c.nome), /*#__PURE__*/React.createElement(Field, {
    label: "Categoria"
  }, s.categoria), /*#__PURE__*/React.createElement(Field, {
    label: "M\xE9todo habitual"
  }, /*#__PURE__*/React.createElement(PaymentMethod, {
    method: "mpesa",
    size: 20,
    assetsBase: window.LOGO_BASE
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Valor"
  }, /*#__PURE__*/React.createElement(MoneyValue, {
    amount: s.valor,
    period: s.periodo
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Per\xEDodo"
  }, s.periodicidade), /*#__PURE__*/React.createElement(Field, {
    label: "In\xEDcio"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, s.inicio)), /*#__PURE__*/React.createElement(Field, {
    label: "Vencimento"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, s.vencimento)), /*#__PURE__*/React.createElement(Field, {
    label: "Pr\xF3ximo aviso"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, "25/08/2027")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 5
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Estado"), /*#__PURE__*/React.createElement(StatusBadge, {
    status: status
  })))), /*#__PURE__*/React.createElement(Card, {
    title: "Lembretes",
    subtitle: "Intervalos activos para este servi\xE7o"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 16,
      flexWrap: "wrap"
    }
  }, ["30 dias antes", "15 dias antes", "7 dias antes", "3 dias antes", "1 dia antes", "No dia", "Após vencimento"].map((l, i) => /*#__PURE__*/React.createElement(Checkbox, {
    key: l,
    label: l,
    checked: i !== 4
  }))))), /*#__PURE__*/React.createElement(Card, {
    title: "Hist\xF3rico do servi\xE7o",
    subtitle: "Registro imut\xE1vel"
  }, /*#__PURE__*/React.createElement(Timeline, {
    items: historico
  }))), dialog === "renew" ? /*#__PURE__*/React.createElement(RenewDialog, {
    servico: s,
    onClose: () => setDialog(null),
    onDone: () => {
      setStatus("activo");
      setDialog(null);
      onToast({
        title: "Renovação registrada",
        body: String(s.valor).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " MZN — " + s.nome
      });
    }
  }) : null, dialog === "suspend" ? /*#__PURE__*/React.createElement(SuspendDialog, {
    onClose: () => setDialog(null),
    onDone: () => {
      setStatus("suspenso");
      setDialog(null);
      onToast({
        title: "Serviço suspenso",
        body: s.nome + " — " + c.nome,
        tone: "danger"
      });
    }
  }) : null);
}
Object.assign(window, {
  ServiceDetailScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/ServiceDetailScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/ServicesScreen.jsx
try { (() => {
const {
  PageHeader,
  Button,
  Card,
  ServiceCard,
  Tabs,
  SearchInput,
  Select,
  EmptyState,
  Tag,
  ServiceLogo
} = window.SubscriptionManagerDesignSystem_6a702b;
function ServicesScreen({
  onOpenService,
  onNew
}) {
  const {
    servicos,
    clientesById
  } = window.SM_DATA;
  const [tab, setTab] = React.useState("todos");
  const [cat, setCat] = React.useState("Todas as categorias");
  const [q, setQ] = React.useState("");
  const rows = servicos.filter(s => tab === "todos" || s.status === tab).filter(s => cat.startsWith("Todas") || s.categoria === cat.toLowerCase()).filter(s => (s.nome + s.plano + clientesById[s.clienteId].nome).toLowerCase().includes(q.toLowerCase()));
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: servicos.length + " serviços",
    title: "Servi\xE7os",
    actions: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Button, {
      icon: "library-big"
    }, "Biblioteca"), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      icon: "plus",
      onClick: onNew
    }, "Nova assinatura"))
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      flexWrap: "wrap"
    }
  }, /*#__PURE__*/React.createElement(Tabs, {
    active: tab,
    onSelect: setTab,
    style: {
      flex: 1,
      minWidth: 300
    },
    tabs: [{
      id: "todos",
      label: "Todos",
      count: servicos.length
    }, {
      id: "activo",
      label: "Activos",
      count: servicos.filter(s => s.status === "activo").length
    }, {
      id: "a_vencer",
      label: "A vencer",
      count: servicos.filter(s => s.status === "a_vencer").length
    }, {
      id: "vencido",
      label: "Vencidos",
      count: servicos.filter(s => s.status === "vencido").length
    }, {
      id: "suspenso",
      label: "Suspensos",
      count: servicos.filter(s => s.status === "suspenso").length
    }]
  }), /*#__PURE__*/React.createElement(Select, {
    options: ["Todas as categorias", "Dominio", "Hospedagem", "Email", "IA", "Software", "Manutencao"],
    value: cat,
    onChange: e => setCat(e.target.value),
    style: {
      width: 190
    }
  }), /*#__PURE__*/React.createElement(SearchInput, {
    width: 230,
    placeholder: "Servi\xE7o ou cliente",
    shortcut: null,
    value: q,
    onChange: e => setQ(e.target.value)
  })), rows.length === 0 ? /*#__PURE__*/React.createElement(Card, null, /*#__PURE__*/React.createElement(EmptyState, {
    icon: "search-x",
    title: "Sem servi\xE7os",
    description: "Nenhum servi\xE7o corresponde aos filtros aplicados."
  })) : /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fill,minmax(268px,1fr))",
      gap: 12
    }
  }, rows.map(s => /*#__PURE__*/React.createElement("div", {
    key: s.id,
    style: {
      display: "flex",
      flexDirection: "column"
    }
  }, /*#__PURE__*/React.createElement(ServiceCard, {
    name: s.nome,
    client: clientesById[s.clienteId].nome,
    category: s.categoria,
    assetsBase: window.LOGO_BASE,
    description: s.descricao,
    amount: s.valor,
    period: s.periodo,
    dueLabel: s.dias < 0 ? "Venceu em " + s.vencimento : s.dias <= 10 ? "Vence em " + s.dias + " dias" : "Vence: " + s.vencimento,
    status: s.status,
    onAction: () => onOpenService(s.id),
    style: {
      flex: 1
    }
  })))), /*#__PURE__*/React.createElement(Card, {
    title: "Biblioteca de servi\xE7os",
    subtitle: "Servi\xE7os conhecidos com logo pronto \u2014 selecione um ao criar a assinatura",
    action: /*#__PURE__*/React.createElement(Button, {
      size: "sm",
      icon: "plus"
    }, "Adicionar servi\xE7o")
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 10,
      flexWrap: "wrap"
    }
  }, window.SERVICE_LIBRARY.map(l => /*#__PURE__*/React.createElement("span", {
    key: l.nome,
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: 8,
      padding: "6px 12px 6px 8px",
      background: "var(--surface-sunken)",
      borderRadius: "var(--radius-pill)",
      fontSize: "var(--text-sm)",
      color: "var(--text-body)",
      whiteSpace: "nowrap"
    }
  }, /*#__PURE__*/React.createElement(ServiceLogo, {
    name: l.nome,
    category: l.categoria,
    size: 22,
    assetsBase: window.LOGO_BASE
  }), l.nome)))));
}
Object.assign(window, {
  ServicesScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/ServicesScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/SettingsScreen.jsx
try { (() => {
const {
  PageHeader,
  Card,
  Button,
  Input,
  Select,
  Switch,
  Checkbox,
  Tabs,
  ServiceLogo,
  Tag,
  IconButton,
  DataTable,
  LogoSlot,
  Textarea,
  PaymentMethod
} = window.SubscriptionManagerDesignSystem_6a702b;
const LIBRARY = window.SERVICE_LIBRARY;
const DESCRICOES = {
  "ChatGPT": "Acesso a conta de equipe ou individual, cobrado mensalmente.",
  "Claude": "Acesso individual, cobrado mensalmente.",
  "Canva": "Licença Pro para materiais gráficos.",
  "Google Workspace": "Email profissional e Drive por utilizador.",
  "Microsoft 365": "Office e email corporativo por utilizador.",
  "GitHub": "Repositórios privados e CI.",
  "Adobe": "Creative Cloud, licença única ou equipe.",
  "Dropbox": "Armazenamento partilhado com o cliente.",
  "Zoom": "Reuniões sem limite de tempo.",
  "Domínio .co.mz": "Registo e renovação anual, DNS gerido.",
  "Hospedagem Business": "Alojamento SSD com backups semanais.",
  "Email corporativo": "Contas de email no domínio do cliente.",
  "Manutenção de site": "Actualizações, backups e horas de alteração por mês."
};
function SettingsScreen() {
  const [tab, setTab] = React.useState("biblioteca");
  const [auto, setAuto] = React.useState(true);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(PageHeader, {
    eyebrow: "Administra\xE7\xE3o",
    title: "Configura\xE7\xF5es"
  }), /*#__PURE__*/React.createElement(Tabs, {
    active: tab,
    onSelect: setTab,
    tabs: [{
      id: "biblioteca",
      label: "Biblioteca de Serviços",
      count: LIBRARY.length
    }, {
      id: "lembretes",
      label: "Lembretes"
    }, {
      id: "empresa",
      label: "Empresa"
    }]
  }), tab === "biblioteca" ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Adicionar servi\xE7o \xE0 biblioteca",
    subtitle: "Logo, categoria e descri\xE7\xE3o ficam guardados para reutilizar em qualquer cliente"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 14,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Nome do servi\xE7o",
    placeholder: "Ex.: Notion"
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Categoria",
    options: ["IA / Software", "Domínio", "Hospedagem", "Email", "Manutenção", "Desenvolvimento", "Outro"]
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(LogoSlot, {
    size: 64,
    label: "Adicionar logo personalizado",
    hint: "PNG, JPG, SVG ou WebP \u2014 redimensionado automaticamente"
  }), /*#__PURE__*/React.createElement(Textarea, {
    label: "Breve descri\xE7\xE3o",
    rows: 2,
    placeholder: "Uma linha que explica o que o cliente recebe."
  })))), /*#__PURE__*/React.createElement(Card, {
    title: "Biblioteca de Servi\xE7os",
    subtitle: "Preenche automaticamente nome, logo e categoria numa nova assinatura",
    action: /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      size: "sm",
      icon: "plus"
    }, "Adicionar servi\xE7o"),
    padding: 0
  }, /*#__PURE__*/React.createElement(DataTable, {
    rows: LIBRARY.map((l, i) => ({
      id: i,
      ...l
    })),
    dense: true,
    columns: [{
      key: "nome",
      header: "Serviço",
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          display: "flex",
          alignItems: "center",
          gap: 9
        }
      }, /*#__PURE__*/React.createElement(ServiceLogo, {
        name: r.nome,
        category: r.categoria,
        size: 26,
        assetsBase: window.LOGO_BASE
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "var(--text-strong)",
          fontWeight: "var(--weight-medium)"
        }
      }, r.nome))
    }, {
      key: "categoria",
      header: "Categoria",
      render: r => /*#__PURE__*/React.createElement(Tag, null, r.categoria)
    }, {
      key: "descricao",
      header: "Descrição padrão",
      wrap: true,
      render: r => /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "var(--text-xs)",
          color: "var(--text-muted)"
        }
      }, DESCRICOES[r.nome] || "—")
    }, {
      key: "acoes",
      header: "",
      align: "right",
      render: () => /*#__PURE__*/React.createElement("span", {
        style: {
          display: "inline-flex",
          gap: 2
        }
      }, /*#__PURE__*/React.createElement(IconButton, {
        icon: "upload",
        label: "Alterar logo",
        size: "sm"
      }), /*#__PURE__*/React.createElement(IconButton, {
        icon: "pencil",
        label: "Editar",
        size: "sm"
      }), /*#__PURE__*/React.createElement(IconButton, {
        icon: "archive",
        label: "Arquivar",
        size: "sm"
      }))
    }]
  })), /*#__PURE__*/React.createElement(Card, {
    title: "M\xE9todos de pagamento aceites",
    subtitle: "Aparecem na renova\xE7\xE3o e no registo de pagamento"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      gap: 16,
      flexWrap: "wrap",
      alignItems: "center"
    }
  }, ["mpesa", "emola", "transferencia", "dinheiro", "outro"].map(m => /*#__PURE__*/React.createElement(PaymentMethod, {
    key: m,
    method: m,
    size: 28,
    assetsBase: window.LOGO_BASE
  })), /*#__PURE__*/React.createElement(Button, {
    size: "sm",
    variant: "ghost",
    icon: "plus"
  }, "Adicionar m\xE9todo")))) : tab === "lembretes" ? /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(300px,1fr))",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Intervalos padr\xE3o"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 10
    }
  }, /*#__PURE__*/React.createElement(Switch, {
    checked: auto,
    onChange: setAuto,
    label: "Verifica\xE7\xE3o di\xE1ria autom\xE1tica"
  }), ["30 dias antes", "15 dias antes", "7 dias antes", "3 dias antes", "1 dia antes", "No dia do vencimento", "Depois do vencimento"].map((l, i) => /*#__PURE__*/React.createElement(Checkbox, {
    key: l,
    label: l,
    checked: i !== 1
  })))), /*#__PURE__*/React.createElement(Card, {
    title: "Template \u2014 Aviso de renova\xE7\xE3o",
    action: /*#__PURE__*/React.createElement(Button, {
      size: "sm",
      icon: "pencil"
    }, "Editar")
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "var(--text-sm)",
      color: "var(--text-body)",
      lineHeight: 1.65,
      background: "var(--surface-sunken)",
      padding: 12,
      borderRadius: "var(--radius-sm)"
    }
  }, "Ol\xE1, ", /*#__PURE__*/React.createElement("mark", {
    style: {
      background: "var(--accent-soft)",
      color: "var(--text-accent)",
      padding: "0 3px",
      borderRadius: 3
    }
  }, "[NOME]"), ".", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), "O seu servi\xE7o ", /*#__PURE__*/React.createElement("mark", {
    style: {
      background: "var(--accent-soft)",
      color: "var(--text-accent)",
      padding: "0 3px",
      borderRadius: 3
    }
  }, "[SERVI\xC7O]"), " ser\xE1 renovado em ", /*#__PURE__*/React.createElement("mark", {
    style: {
      background: "var(--accent-soft)",
      color: "var(--text-accent)",
      padding: "0 3px",
      borderRadius: 3
    }
  }, "[DATA]"), ".", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), "Valor da renova\xE7\xE3o: ", /*#__PURE__*/React.createElement("mark", {
    style: {
      background: "var(--accent-soft)",
      color: "var(--text-accent)",
      padding: "0 3px",
      borderRadius: 3
    }
  }, "[VALOR]"), ".", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), "Atenciosamente,", /*#__PURE__*/React.createElement("br", null), "[NOME DA EMPRESA]"))) : /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit,minmax(300px,1fr))",
      gap: 12,
      alignItems: "start"
    }
  }, /*#__PURE__*/React.createElement(Card, {
    title: "Empresa"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Nome da empresa",
    defaultValue: "Comusanas"
  }), /*#__PURE__*/React.createElement(Input, {
    label: "Email remetente",
    icon: "mail",
    defaultValue: "cobrancas@comusanas.org.mz"
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Moeda padr\xE3o",
    options: ["MZN — Metical", "USD — Dólar", "ZAR — Rand"]
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Fuso hor\xE1rio",
    options: ["Africa/Maputo (CAT)", "UTC"]
  }), /*#__PURE__*/React.createElement(LogoSlot, {
    size: 56,
    label: "Logo da empresa",
    hint: "Usado nos emails enviados ao cliente"
  }))), /*#__PURE__*/React.createElement(Card, {
    title: "Envio de email (SMTP)"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      flexDirection: "column",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Servidor",
    defaultValue: "smtp.comusanas.org.mz"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 12
    }
  }, /*#__PURE__*/React.createElement(Input, {
    label: "Porta",
    defaultValue: "587"
  }), /*#__PURE__*/React.createElement(Select, {
    label: "Seguran\xE7a",
    options: ["STARTTLS", "SSL", "Nenhuma"]
  })), /*#__PURE__*/React.createElement(Input, {
    label: "Utilizador",
    defaultValue: "cobrancas@comusanas.org.mz"
  }), /*#__PURE__*/React.createElement(Button, {
    variant: "secondary",
    icon: "send"
  }, "Enviar email de teste")))));
}
Object.assign(window, {
  SettingsScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/SettingsScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/subscription-manager/data.js
try { (() => {
window.SM_DATA = (() => {
  const clientes = [{
    id: "c1",
    nome: "João da Silva",
    email: "joao@email.com",
    tel: "+258 84 512 0034",
    empresa: "Silva Consultoria",
    status: "activo",
    desde: "12/03/2025"
  }, {
    id: "c2",
    nome: "Maria Costa",
    email: "maria.costa@email.com",
    tel: "+258 82 771 9920",
    empresa: "",
    status: "activo",
    desde: "04/06/2025"
  }, {
    id: "c3",
    nome: "Carlos Manuel",
    email: "carlos@cmdigital.co.mz",
    tel: "+258 87 330 1188",
    empresa: "CM Digital",
    status: "activo",
    desde: "22/01/2026"
  }, {
    id: "c4",
    nome: "Ana Mucavele",
    email: "ana@mucavele.co.mz",
    tel: "+258 84 220 7745",
    empresa: "Mucavele & Filhos",
    status: "activo",
    desde: "09/09/2025"
  }, {
    id: "c5",
    nome: "Hélder Tembe",
    email: "helder.tembe@email.com",
    tel: "+258 86 909 4412",
    empresa: "",
    status: "inactivo",
    desde: "17/11/2025"
  }];
  const servicos = [{
    id: "s1",
    clienteId: "c1",
    descricao: "Alojamento do site institucional, 10 GB SSD, backups semanais.",
    nome: "Hospedagem",
    plano: "Plano Business",
    categoria: "hospedagem",
    valor: 1500,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "02/09/2025",
    vencimento: "02/09/2026",
    status: "a_vencer",
    dias: 3
  }, {
    id: "s2",
    clienteId: "c1",
    descricao: "Registo e renovação do domínio principal, DNS gerido por mim.",
    nome: "exemplo.co.mz",
    plano: "Domínio .co.mz",
    categoria: "dominio",
    valor: 800,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "02/09/2025",
    vencimento: "02/09/2027",
    status: "activo",
    dias: 368
  }, {
    id: "s3",
    clienteId: "c1",
    descricao: "Acesso partilhado à conta de equipe — 2 utilizadores.",
    nome: "ChatGPT",
    plano: "Acesso Equipe",
    categoria: "ia",
    valor: 500,
    periodo: "mês",
    periodicidade: "Mensal",
    inicio: "01/09/2026",
    vencimento: "01/10/2026",
    status: "activo",
    dias: 32
  }, {
    id: "s4",
    clienteId: "c2",
    descricao: "Acesso individual para redacção de conteúdos.",
    nome: "ChatGPT",
    plano: "Acesso Equipe",
    categoria: "ia",
    valor: 500,
    periodo: "mês",
    periodicidade: "Mensal",
    inicio: "05/08/2026",
    vencimento: "05/09/2026",
    status: "a_vencer",
    dias: 6
  }, {
    id: "s5",
    clienteId: "c2",
    descricao: "Licença Pro para materiais de marketing da loja.",
    nome: "Canva",
    plano: "Pro",
    categoria: "software",
    valor: 1000,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "20/10/2025",
    vencimento: "20/10/2026",
    status: "activo",
    dias: 51
  }, {
    id: "s6",
    clienteId: "c3",
    descricao: "Domínio da agência, renovação anual automática.",
    nome: "Domínio cmdigital.co.mz",
    plano: "Domínio .co.mz",
    categoria: "dominio",
    valor: 800,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "08/09/2024",
    vencimento: "08/09/2026",
    status: "a_vencer",
    dias: 9
  }, {
    id: "s7",
    clienteId: "c3",
    descricao: "Manutenção mensal do site: actualizações, backups e 4h de alterações.",
    nome: "Manutenção do site",
    plano: "4h / mês",
    categoria: "manutencao",
    valor: 2500,
    periodo: "mês",
    periodicidade: "Mensal",
    inicio: "01/08/2026",
    vencimento: "01/09/2026",
    status: "vencido",
    dias: -1
  }, {
    id: "s8",
    clienteId: "c4",
    descricao: "Acesso individual usado para análise de documentos.",
    nome: "Claude",
    plano: "Acesso individual",
    categoria: "ia",
    valor: 700,
    periodo: "mês",
    periodicidade: "Mensal",
    inicio: "15/08/2026",
    vencimento: "15/09/2026",
    status: "activo",
    dias: 16
  }, {
    id: "s9",
    clienteId: "c4",
    descricao: "5 contas de email corporativo com webmail e antispam.",
    nome: "Email corporativo",
    plano: "5 contas",
    categoria: "email",
    valor: 1200,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "09/09/2025",
    vencimento: "09/09/2026",
    status: "a_vencer",
    dias: 10
  }, {
    id: "s10",
    clienteId: "c5",
    descricao: "Acesso suspenso por falta de pagamento; conta ainda não removida.",
    nome: "ChatGPT",
    plano: "Acesso Equipe",
    categoria: "ia",
    valor: 500,
    periodo: "mês",
    periodicidade: "Mensal",
    inicio: "05/07/2026",
    vencimento: "05/08/2026",
    status: "suspenso",
    dias: -25
  }, {
    id: "s11",
    clienteId: "c2",
    descricao: "Alojamento partilhado do site da loja online, 5 GB SSD.",
    nome: "Hospedagem",
    plano: "Plano Start",
    categoria: "hospedagem",
    valor: 900,
    periodo: "ano",
    periodicidade: "Anual",
    inicio: "28/08/2026",
    vencimento: "28/08/2027",
    status: "activo",
    dias: 363
  }];
  const pagamentos = [{
    id: "p1",
    data: "30/08/2026",
    clienteId: "c2",
    servicoId: "s11",
    valor: 900,
    metodo: "mpesa",
    periodo: "1 ano"
  }, {
    id: "p2",
    data: "28/08/2026",
    clienteId: "c2",
    servicoId: "s4",
    valor: 500,
    metodo: "transferencia",
    periodo: "1 mês"
  }, {
    id: "p3",
    data: "20/08/2026",
    clienteId: "c4",
    servicoId: "s8",
    valor: 700,
    metodo: "emola",
    periodo: "1 mês"
  }, {
    id: "p4",
    data: "15/08/2026",
    clienteId: "c1",
    servicoId: "s3",
    valor: 500,
    metodo: "mpesa",
    periodo: "1 mês"
  }, {
    id: "p5",
    data: "12/08/2026",
    clienteId: "c3",
    servicoId: "s7",
    valor: 2500,
    metodo: "dinheiro",
    periodo: "1 mês"
  }, {
    id: "p6",
    data: "04/08/2026",
    clienteId: "c4",
    servicoId: "s9",
    valor: 1200,
    metodo: "transferencia",
    periodo: "1 ano"
  }, {
    id: "p7",
    data: "18/08/2026",
    clienteId: "c1",
    servicoId: "s1",
    valor: 1500,
    metodo: "mpesa",
    periodo: "1 ano"
  }, {
    id: "p8",
    data: "14/08/2026",
    clienteId: "c2",
    servicoId: "s5",
    valor: 1000,
    metodo: "emola",
    periodo: "1 ano"
  }, {
    id: "p9",
    data: "11/08/2026",
    clienteId: "c3",
    servicoId: "s6",
    valor: 800,
    metodo: "transferencia",
    periodo: "1 ano"
  }, {
    id: "p10",
    data: "08/08/2026",
    clienteId: "c4",
    servicoId: "s8",
    valor: 700,
    metodo: "mpesa",
    periodo: "1 mês"
  }, {
    id: "p11",
    data: "02/08/2026",
    clienteId: "c1",
    servicoId: "s2",
    valor: 800,
    metodo: "mpesa",
    periodo: "1 ano"
  }];
  const historico = [{
    date: "01/09/2025",
    title: "Serviço criado",
    description: "Hospedagem — Plano Business",
    kind: "criado"
  }, {
    date: "02/09/2025",
    title: "Pagamento recebido",
    description: "1.500 MZN — M-Pesa · 1 ano",
    kind: "pagamento"
  }, {
    date: "02/09/2025",
    title: "Serviço activado",
    kind: "activado"
  }, {
    date: "03/08/2026",
    title: "Lembrete enviado",
    description: "30 dias antes — joao@email.com",
    kind: "lembrete"
  }, {
    date: "18/08/2026",
    title: "Lembrete enviado",
    description: "15 dias antes — joao@email.com",
    kind: "lembrete"
  }, {
    date: "26/08/2026",
    title: "Lembrete enviado",
    description: "7 dias antes — joao@email.com",
    kind: "lembrete"
  }];
  const byId = arr => Object.fromEntries(arr.map(x => [x.id, x]));
  /* Single source of truth for every headline figure — no screen hard-codes money. */
  const MES = "08/2026";
  const doMes = pagamentos.filter(p => p.data.slice(3) === MES);
  const aReceber = servicos.filter(s => s.status === "vencido" || s.status === "a_vencer");
  const mensal = s => s.periodo === "mês" ? s.valor : s.periodo === "trimestre" ? s.valor / 3 : s.valor / 12;
  const activos = servicos.filter(s => s.status !== "cancelado" && s.status !== "suspenso");
  const totais = {
    mes: "Agosto 2026",
    entradas: doMes.reduce((t, p) => t + p.valor, 0),
    nPagamentos: doMes.length,
    aReceber: aReceber.reduce((t, s) => t + s.valor, 0),
    nAReceber: aReceber.length,
    emAtraso: servicos.filter(s => s.status === "vencido").reduce((t, s) => t + s.valor, 0),
    nEmAtraso: servicos.filter(s => s.status === "vencido").length,
    aVencer: servicos.filter(s => s.status === "a_vencer").length,
    clientesActivos: clientes.filter(c => c.status === "activo").length,
    servicosActivos: activos.length,
    mrr: Math.round(activos.reduce((t, s) => t + mensal(s), 0))
  };
  totais.esperada = totais.entradas + totais.aReceber;
  totais.arr = totais.mrr * 12;
  return {
    clientes,
    servicos,
    pagamentos,
    historico,
    aReceber,
    pagamentosDoMes: doMes,
    totais,
    clientesById: byId(clientes)
  };
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/subscription-manager/data.js", error: String((e && e.message) || e) }); }

__ds_ns.Button = __ds_scope.Button;

__ds_ns.Card = __ds_scope.Card;

__ds_ns.Checkbox = __ds_scope.Checkbox;

__ds_ns.Icon = __ds_scope.Icon;

__ds_ns.IconButton = __ds_scope.IconButton;

__ds_ns.Input = __ds_scope.Input;

__ds_ns.LogoSlot = __ds_scope.LogoSlot;

__ds_ns.SearchInput = __ds_scope.SearchInput;

__ds_ns.Select = __ds_scope.Select;

__ds_ns.Switch = __ds_scope.Switch;

__ds_ns.Textarea = __ds_scope.Textarea;

__ds_ns.ThemeToggle = __ds_scope.ThemeToggle;

__ds_ns.DataTable = __ds_scope.DataTable;

__ds_ns.MoneyValue = __ds_scope.MoneyValue;

__ds_ns.PAYMENT_METHODS = __ds_scope.PAYMENT_METHODS;

__ds_ns.PaymentMethod = __ds_scope.PaymentMethod;

__ds_ns.ServiceCard = __ds_scope.ServiceCard;

__ds_ns.LOGO_LIBRARY = __ds_scope.LOGO_LIBRARY;

__ds_ns.ServiceLogo = __ds_scope.ServiceLogo;

__ds_ns.SERVICE_CATEGORIES = __ds_scope.SERVICE_CATEGORIES;

__ds_ns.StatCard = __ds_scope.StatCard;

__ds_ns.Timeline = __ds_scope.Timeline;

__ds_ns.AlertBanner = __ds_scope.AlertBanner;

__ds_ns.Dialog = __ds_scope.Dialog;

__ds_ns.EmptyState = __ds_scope.EmptyState;

__ds_ns.STATUS = __ds_scope.STATUS;

__ds_ns.StatusBadge = __ds_scope.StatusBadge;

__ds_ns.Tag = __ds_scope.Tag;

__ds_ns.Toast = __ds_scope.Toast;

__ds_ns.PageHeader = __ds_scope.PageHeader;

__ds_ns.NAV_ITEMS = __ds_scope.NAV_ITEMS;

__ds_ns.SidebarNav = __ds_scope.SidebarNav;

__ds_ns.Tabs = __ds_scope.Tabs;

__ds_ns.TopBar = __ds_scope.TopBar;

})();
