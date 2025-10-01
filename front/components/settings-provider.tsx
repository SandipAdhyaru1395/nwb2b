"use client"

import React, { createContext, useContext, useEffect, useMemo, useState } from "react"
import api from "@/lib/axios"

type Settings = {
  company_title?: string | null
  company_logo_url?: string | null
  currency?: string | null
  currency_symbol?: string | null
  banner?: string | null
  maintenance_mode?: boolean | null
}

type SettingsContextValue = {
  settings: Settings | null
  loading: boolean
  error: string | null
  refresh: () => Promise<void>
  serverMaintenance: boolean
}

const SettingsContext = createContext<SettingsContextValue | undefined>(undefined)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<Settings | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)
  const [serverMaintenance, setServerMaintenance] = useState<boolean>(false)

  const fetchSettings = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.get('/settings')
      const s = res?.data?.settings
      const normalized: Settings = {
        company_title: s?.company_title ?? null,
        company_logo_url: s?.company_logo_url ?? null,
        currency: s?.currency ?? null,
        currency_symbol: s?.currency_symbol ?? null,
        banner: s?.banner ?? null,
        maintenance_mode: typeof s?.maintenance_mode === 'boolean' ? s.maintenance_mode : null,
      }
      setSettings(normalized)
      setServerMaintenance(false)
      try { sessionStorage.setItem('settings_cache', JSON.stringify(normalized)) } catch {}
    } catch (e: any) {
      const status = e?.response?.status
      if (status === 503) {
        setServerMaintenance(true)
      }
      setError(e?.message || 'Failed to load settings')
      setSettings(null)
      try { sessionStorage.removeItem('settings_cache') } catch {}
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    // Serve cached settings if available; otherwise fetch fresh data
    try {
      const raw = sessionStorage.getItem('settings_cache')
      if (raw) {
        const cachedSettings = JSON.parse(raw)
        // Ensure banner field exists in cached settings
        if (cachedSettings && !cachedSettings.hasOwnProperty('banner')) {
          cachedSettings.banner = null
          // Update the cache with the new field
          try { sessionStorage.setItem('settings_cache', JSON.stringify(cachedSettings)) } catch {}
        }
        setSettings(cachedSettings)
        setLoading(false)
        return
      }
    } catch {}
    
    // No cached data available, fetch fresh data
    fetchSettings()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const value = useMemo<SettingsContextValue>(() => ({
    settings,
    loading,
    error,
    refresh: fetchSettings,
    serverMaintenance,
  }), [settings, loading, error, serverMaintenance])

  return <SettingsContext.Provider value={value}>{children}</SettingsContext.Provider>
}

export function useSettings() {
  const ctx = useContext(SettingsContext)
  if (!ctx) throw new Error('useSettings must be used within SettingsProvider')
  return ctx
}


