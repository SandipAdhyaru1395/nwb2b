"use client"

import { Card } from "@/components/ui/card"
import { useCurrency } from "@/components/currency-provider"
import { Minus, Plus, Home, ShoppingBag, User, Wallet } from "lucide-react"
import { useEffect, useState } from "react"
import api from "@/lib/axios"
import { useCustomer } from "@/components/customer-provider"
import { Banner } from "@/components/banner"

interface ProductItem {
  id: number
  name: string
  image: string
  price: string
  discount?: string
}

interface MobileWalletProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void
  cart: Record<number, { product: ProductItem; quantity: number }>
  increment: (product: ProductItem) => void
  decrement: (product: ProductItem) => void
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number }
  clearCart: () => void
}

export function MobileWallet({ onNavigate }: MobileWalletProps) {
  const { symbol } = useCurrency()
  const { customer } = useCustomer()
  const wallet = Number(customer?.wallet_balance || 0)
  return (
    <div className="w-full max-w-[1000px] mx-auto bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="bg-white p-4 flex items-center gap-3 border-b">
        <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
          <Wallet className="w-5 h-5 text-white" />
        </div>
        <h1 className="text-lg font-semibold text-gray-900">Wallet</h1>
      </div>

      {/* Banner */}
      <Banner />

      {/* Wallet Content */}
      <div className="p-4 space-y-6">
        {/* Wallet Balance */}
        
        <div>
          <h2 className="text-base font-medium text-gray-900 mb-3">Your wallet balance</h2>
          <Card className="p-4 border border-gray-200">
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                <span className="text-white text-xs font-bold">{symbol}</span>
              </div>
              <span className="text-xl font-semibold text-gray-900">{symbol}{wallet.toFixed(2)}</span>
              <span className="text-green-600 font-medium">Credit</span>
            </div>
          </Card>
        </div>

        {/* FAQ Sections */}
        <div className="space-y-4">
          <div>
            <h3 className="text-sm font-semibold text-gray-900 mb-2">What is the wallet?</h3>
            <p className="text-sm text-gray-600 leading-relaxed">
              The wallet contains credit you acquired from your previous purchases on this platform.
            </p>
          </div>

          <div>
            <h3 className="text-sm font-semibold text-gray-900 mb-2">How much credit do I get?</h3>
            <p className="text-sm text-gray-600 leading-relaxed">
              Every product has a wallet indicator which states how much credit you will be awarded for every unit of
              that product purchased.
            </p>
          </div>

          <div>
            <h3 className="text-sm font-semibold text-gray-900 mb-2">How do I use my credit?</h3>
            <p className="text-sm text-gray-600 leading-relaxed">
              Your wallet credit will be automatically applied on your next purchase as a discount from the order total.
            </p>
          </div>

          <div>
            <h3 className="text-sm font-semibold text-gray-900 mb-2">Do I get credit if I don't use the platform?</h3>
            <p className="text-sm text-gray-600 leading-relaxed">
              No. Credit is only added to your wallet when you purchase through this platform.
            </p>
          </div>
        </div>
      </div>

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
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center py-2 text-green-600 hover:text-green-600 hover:cursor-pointer">
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
