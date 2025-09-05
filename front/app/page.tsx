"use client"

import { useMemo, useState } from "react"
import { MobileDashboard } from "@/components/mobile-dashboard"
import { MobileShop } from "@/components/mobile-shop"
import { MobileBasket } from "@/components/mobile-basket"

export default function Home() {
  const [currentPage, setCurrentPage] = useState<"dashboard" | "shop" | "basket">("dashboard")
  const [cart, setCart] = useState<Record<number, { product: any; quantity: number }>>({})

  const parseMoney = (value?: string): number => {
    if (!value) return 0
    const match = value.replace(/[^0-9.\-]/g, "")
    const num = parseFloat(match)
    return Number.isFinite(num) ? num : 0
  }
  const formatMoney = (num: number): string => `£${num.toFixed(2)}`

  const increment = (product: any) => {
    setCart((prev) => {
      const current = prev[product.id]?.quantity ?? 0
      return { ...prev, [product.id]: { product, quantity: current + 1 } }
    })
  }

  const decrement = (product: any) => {
    setCart((prev) => {
      const current = prev[product.id]?.quantity ?? 0
      const nextQty = Math.max(0, current - 1)
      const next = { ...prev }
      if (nextQty === 0) {
        delete next[product.id]
        return { ...next }
      }
      next[product.id] = { product, quantity: nextQty }
      return next
    })
  }

  const clearCart = () => {
    setCart({})
  }

  const totals = useMemo(() => {
    const entries = Object.values(cart)
    const units = entries.reduce((sum, item) => sum + item.quantity, 0)
    const skus = entries.length
    const subtotal = entries.reduce((sum, item) => sum + parseMoney(item.product.price) * item.quantity, 0)
    const totalDiscount = entries.reduce((sum, item) => sum + parseMoney(item.product.discount) * item.quantity, 0)
    const total = Math.max(0, subtotal - totalDiscount)
    return { units, skus, subtotal, totalDiscount, total }
  }, [cart])

  if (currentPage === "shop") {
    return (
      <MobileShop
        onNavigate={setCurrentPage}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        formatMoney={formatMoney}
      />
    )
  }
  if (currentPage === "basket") {
    return (
      <MobileBasket
        onNavigate={setCurrentPage}
        cart={cart}
        increment={increment}
        decrement={decrement}
        totals={totals}
        formatMoney={formatMoney}
        clearCart={clearCart}
      />
    )
  }

  return <MobileDashboard onNavigate={setCurrentPage} />
}
