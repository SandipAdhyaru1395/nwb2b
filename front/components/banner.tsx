"use client";

import { useSettings } from "./settings-provider";

export function Banner() {
  const { settings } = useSettings();

  if (!settings?.banner) {
    return null;
  }

  return (
    <div className="w-full">
      <img src={settings.banner} alt="Banner" className="w-full h-auto object-cover" style={{ maxHeight: "266.66px" }} />
    </div>
  );
}
