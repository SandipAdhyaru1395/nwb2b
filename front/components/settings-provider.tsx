"use client"

import React, { createContext, useContext, useEffect, useMemo, useState } from "react"
import api from "@/lib/axios"

type Settings = {
  company_title?: string | null
  company_logo_url?: string | null
  currency?: string | null
  currency_symbol?: string | null
}

type SettingsContextValue = {
  settings: Settings | null
  loading: boolean
  error: string | null
  refresh: () => Promise<void>
}

const SettingsContext = createContext<SettingsContextValue | undefined>(undefined)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<Settings | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)

  const fetchSettings = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.get('/settings')
      const s = res?.data?.settings
      setSettings({
        company_title: s?.company_title ?? null,
        company_logo_url: s?.company_logo_url ?? null,
        currency: s?.currency ?? null,
        currency_symbol: s?.currency_symbol ?? null,
      })
    } catch (e: any) {
      setError(e?.message || 'Failed to load settings')
      setSettings(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchSettings()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const value = useMemo<SettingsContextValue>(() => ({
    settings,
    loading,
    error,
    refresh: fetchSettings,
  }), [settings, loading, error])

  return <SettingsContext.Provider value={value}>{children}</SettingsContext.Provider>
}

export function useSettings() {
  const ctx = useContext(SettingsContext)
  if (!ctx) throw new Error('useSettings must be used within SettingsProvider')
  return ctx
}


