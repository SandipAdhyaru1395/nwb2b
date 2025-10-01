"use client"

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package } from "lucide-react"
import { Card } from "@/components/ui/card"
import api from "@/lib/axios"
import { useEffect, useState } from "react"
import { Banner } from "@/components/banner"

interface MobileOrderDetailsProps {
  orderNumber: string
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders" | "order-details") => void
  onBack: () => void
  onReorder: (items: Array<{ product: any; quantity: number }>) => void
}

type Address = { line1?: string | null; line2?: string | null; city?: string | null; state?: string | null; zip?: string | null; country?: string | null }

type OrderDetails = {
  order_number: string
  ordered_at: string
  payment_status: string
  fulfillment_status: string
  units: number
  skus: number
  subtotal: number
  vat_amount: number
  delivery: string
  wallet_discount: number
  total_paid: number
  currency_symbol: string
  billing_address: Address
  shipping_address: Address
  items: Array<{ product_id: number; product_name?: string | null; product_image?: string | null; quantity: number; unit_price: number; wallet_credit_earned: number; total_price: number }>
}

export function MobileOrderDetails({ orderNumber, onNavigate, onBack, onReorder }: MobileOrderDetailsProps) {
  const [order, setOrder] = useState<OrderDetails | null>(null)
  const [loading, setLoading] = useState<boolean>(true)
  const [reordering, setReordering] = useState<boolean>(false)

  useEffect(() => {
    const fetchDetails = async () => {
      setLoading(true)
      try {
        const res = await api.get(`/orders/${orderNumber}`)
        if (res?.data?.success && res.data.order) {
          setOrder(res.data.order)
        } else {
          setOrder(null)
        }
      } catch {
        setOrder(null)
      } finally {
        setLoading(false)
      }
    }
    fetchDetails()
  }, [orderNumber])

  return (
    <div className="min-h-screen bg-gray-50 w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <header className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <Package className="w-4 h-4 text-green-600" />
          </div>
          <span className="text-sm text-gray-600">Orders / Details - {orderNumber}</span>
        </div>
      </header>

  {/* Banner */}
  <Banner />

      <main className="pb-24">
        {loading ? (
          <div className="p-4 space-y-3">
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
          </div>
        ) : !order ? (
          <div className="p-4 text-center text-gray-500">Order not found</div>
        ) : (
          <>
            {/* Order Summary (exact style with three sections and right-aligned values) */}
            <div className="mx-4 mt-4">
              <h3 className="font-semibold text-gray-900 mb-3">Order Summary</h3>
              <div className="bg-white border rounded-lg">
                {/* Order details row */}
                <div className="p-4">
                  <div className="flex items-start justify-between">
                    <span className="font-medium">Order details</span>
                    <div className="text-sm w-40">
                      <div className="flex justify-between"><span className="text-gray-600">Units</span><span className="text-gray-900">{order.units}</span></div>
                      <div className="flex justify-between"><span className="text-gray-600">SKUs</span><span className="text-gray-900">{order.skus}</span></div>
                    </div>
                  </div>
                </div>
                {/* Delivery row */}
                <div className="border-t p-4">
                  <div className="flex items-start justify-between">
                    <span className="font-medium">Delivery</span>
                    <div className="text-right text-sm text-gray-700 max-w-[60%]">
                      <div className="">Next Working Day Delivery</div>
                      <div>{order.shipping_address.line1}</div>
                      {order.shipping_address.line2 && <div>{order.shipping_address.line2}</div>}
                      <div>{order.shipping_address.city}</div>
                      <div>{order.shipping_address.state}</div>
                      <div>{order.shipping_address.zip}</div>
                      <div>{order.shipping_address.country}</div>
                    </div>
                  </div>
                </div>
                {/* Summary row */}
                <div className="border-t p-4">
                  <div className="flex items-start justify-between">
                    <span className="font-medium">Summary</span>
                    <div className="text-sm w-44">
                      <div className="flex justify-between"><span className="text-gray-600">Subtotal</span><span className="text-gray-900">{order.currency_symbol}{order.subtotal.toFixed(2)}</span></div>
                      <div className="flex justify-between"><span className="text-gray-600">Wallet Discount</span><span className="text-gray-900">{order.currency_symbol}{order.wallet_discount.toFixed(2)}</span></div>
                      <div className="flex justify-between"><span className="text-gray-600">Delivery</span><span className="text-gray-900">{order.delivery}</span></div>
                      <div className="flex justify-between"><span className="text-gray-600">VAT (20%)</span><span className="text-gray-900">{order.currency_symbol}{order.vat_amount.toFixed(2)}</span></div>
                      <div className="flex justify-between font-semibold"><span className="text-gray-600">Payment Total</span><span className="text-gray-900">{order.currency_symbol}{((order as any).payment_amount ?? Math.max(0, (order.total_paid ?? 0) - ((order as any).wallet_credit_used ?? 0))).toFixed(2)}</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Reorder Items CTA */}
            <div className="mx-4 mt-4">
              <button
                disabled={reordering}
                onClick={async () => {
                  if (!orderNumber) return
                  try {
                    setReordering(true)
                    const res = await api.post(`/orders/${orderNumber}/reorder`)
                    const items = Array.isArray(res?.data?.items) ? res.data.items : []
                    const mapped = items.map((it: any) => ({ product: it.product, quantity: it.quantity }))
                    onReorder(mapped)
                  } catch (e) {
                    // noop; optionally show a toast in future
                  } finally {
                    setReordering(false)
                  }
                }}
                className="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-md"
              >
                {reordering ? 'Reordering…' : 'Reorder Items'}
              </button>
            </div>

            {/* Addresses removed per request */}

            {/* Order Lines */}
            <div className="mx-4 mt-10">
              <h3 className="font-semibold text-gray-900 mb-3">Order Lines</h3>
              {order.items.map((it, idx) => (
                <div key={it.product_id + "-" + idx} className="py-4 border-b last:border-b-0">
                  <div className="grid grid-cols-[1fr_64px_80px] items-center gap-4">
                    {/* Product info */}
                    <div className="flex items-center gap-3 min-w-0">
                      {it.product_image ? (
                        <img src={it.product_image} alt="" className="w-10 h-10 rounded-full object-cover" />
                      ) : (
                        <div className="w-10 h-10 rounded-full bg-gray-100" />
                      )}
                      <div className="truncate">
                        <div className="font-medium text-gray-900 truncate">{it.product_name || `Product #${it.product_id}`}</div>
                      </div>
                    </div>
                    {/* Quantity */}
                    <div className="text-gray-800 text-sm text-center">{it.quantity}</div>
                    {/* Line total */}
                    <div className="text-right font-semibold text-gray-900">{order.currency_symbol}{it.total_price.toFixed(2)}</div>
                  </div>
                </div>
              ))}
            </div>
          </>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t">
        <div className="grid grid-cols-4 py-2">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Home className="w-5 h-5" />
            <span className="text-xs mt-1">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <ShoppingBag className="w-5 h-5" />
            <span className="text-xs mt-1">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Wallet className="w-5 h-5" />
            <span className="text-xs mt-1">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <User className="w-5 h-5" />
            <span className="text-xs mt-1">Account</span>
          </button>
        </div>
      </nav>
    </div>
  )
}


