"use client"

import { useSettings } from "./settings-provider"

export function Banner() {
  const { settings } = useSettings()

  if (!settings?.banner) {
    return null
  }

  return (
    <div className="w-full mb-4">
      <img
        src={settings.banner}
        alt="Banner"
        className="w-full h-auto rounded-lg object-cover"
        style={{ maxHeight: '200px' }}
      />
    </div>
  )
}
