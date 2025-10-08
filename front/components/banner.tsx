"use client";

import { useSettings } from "./settings-provider";

export function Banner() {
  const { settings } = useSettings();

  if (!settings?.banner) {
    return null;
  }

  return (
    <div className="w-full">
      <img src={settings.banner} alt="Banner" className="w-full h-auto rounded-lg object-cover" style={{ maxHeight: "270px" }} />
    </div>
  );
}
