"use client"

import React, { createContext, useContext, useEffect, useState } from "react"
import api from "@/lib/axios"

type Customer = {
  id: number
  name?: string | null
  email?: string | null
  wallet_balance: number
}

type CustomerContextValue = {
  customer: Customer | null
  loading: boolean
  error: string | null
  refresh: () => Promise<void>
}

const CustomerContext = createContext<CustomerContextValue | undefined>(undefined)

export function CustomerProvider({ children }: { children: React.ReactNode }) {
  const [customer, setCustomer] = useState<Customer | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)

  const fetchCustomer = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.get("/customer")
      const c = res?.data?.customer
      if (c && typeof c.wallet_balance !== "undefined") {
        setCustomer({
          id: Number(c.id),
          name: c.name ?? null,
          email: c.email ?? null,
          wallet_balance: Number(c.wallet_balance) || 0,
        })
      } else {
        setCustomer(null)
      }
    } catch (e: any) {
      setError(e?.message || "Failed to load customer")
      setCustomer(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchCustomer()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const value: CustomerContextValue = {
    customer,
    loading,
    error,
    refresh: fetchCustomer,
  }

  return <CustomerContext.Provider value={value}>{children}</CustomerContext.Provider>
}

export function useCustomer() {
  const ctx = useContext(CustomerContext)
  if (!ctx) throw new Error("useCustomer must be used within CustomerProvider")
  return ctx
}


