import React from "react";
const BASE = "https://unpkg.com/lucide-static@0.454.0/icons/";
export function Icon({ name, size = 16, color = "currentColor", strokeWidth, style, ...rest }) {
  const url = `url("${BASE}${name}.svg")`;
  return (
    <span aria-hidden="true" {...rest} style={{ display: "inline-block", flex: "0 0 auto", width: size, height: size, background: color, WebkitMaskImage: url, maskImage: url, WebkitMaskRepeat: "no-repeat", maskRepeat: "no-repeat", WebkitMaskPosition: "center", maskPosition: "center", WebkitMaskSize: "contain", maskSize: "contain", ...style }} />
  );
}
