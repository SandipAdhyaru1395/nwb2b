"use client"

import { createContext, useContext, useEffect, useMemo, useState } from "react"
import api from "@/lib/axios"

type CurrencyContextValue = {
  symbol: string
  code?: string
  format: (n: number) => string
}

const CurrencyContext = createContext<CurrencyContextValue | undefined>(undefined)

export function CurrencyProvider({ children }: { children: React.ReactNode }) {
  const [symbol, setSymbol] = useState<string>('')
  const [code, setCode] = useState<string | undefined>('')

  useEffect(() => {
    const run = async () => {
      try {
        const res = await api.get('/settings')
        const s = res?.data?.settings?.currency_symbol
        const c = res?.data?.settings?.currency
        if (typeof s === 'string' && s.length > 0) setSymbol(s)
        if (typeof c === 'string' && c.length > 0) setCode(c)
      } catch (_) {
        // ignore
      }
    }
    run()
  }, [])

  const value = useMemo<CurrencyContextValue>(() => ({
    symbol,
    code,
    format: (n: number) => `${symbol}${n.toFixed(2)}`
  }), [symbol, code])

  return (
    <CurrencyContext.Provider value={value}>{children}</CurrencyContext.Provider>
  )
}

export function useCurrency() {
  const ctx = useContext(CurrencyContext)
  if (!ctx) throw new Error('useCurrency must be used within CurrencyProvider')
  return ctx
}


