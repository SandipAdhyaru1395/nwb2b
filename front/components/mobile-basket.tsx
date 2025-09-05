"use client"

import api from "@/lib/axios"
import { useState } from "react"
import { Minus, Plus, Home, QrCode, ShoppingBag, User, Wallet } from "lucide-react"
import { useToast } from "@/hooks/use-toast"

interface ProductItem {
  id: number
  name: string
  image: string
  price: string
  discount?: string
}

interface MobileBasketProps {
  onNavigate: (page: "dashboard" | "shop" | "basket") => void
  cart: Record<number, { product: ProductItem; quantity: number }>
  increment: (product: ProductItem) => void
  decrement: (product: ProductItem) => void
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number }
  formatMoney: (n: number) => string
  clearCart: () => void
}

export function MobileBasket({ onNavigate, cart, increment, decrement, totals, formatMoney, clearCart }: MobileBasketProps) {
  const [isCheckingOut, setIsCheckingOut] = useState(false)
  const { toast } = useToast()

  const handleCheckout = async () => {
    setIsCheckingOut(true)
    try {
      const items = Object.values(cart).map(({ product, quantity }) => ({
        product_id: product.id,
        quantity: quantity
      }))

      const { data: result } = await api.post('/checkout', {
        items,
        total: totals.total,
        units: totals.units,
        skus: totals.skus
      })
      
      if (result.success) {
        toast({
          title: "Order Placed Successfully! 🎉",
          description: `Order Number: ${result.order_number}`,
          variant: "default",
        })
        // Clear the cart after successful checkout
        clearCart()
        onNavigate('dashboard')
      } else {
        toast({
          title: "Checkout Failed",
          description: result.message,
          variant: "destructive",
        })
      }
    } catch (error) {
      toast({
        title: "Checkout Failed",
        description: "Please try again later.",
        variant: "destructive",
      })
      console.error('Checkout error:', error)
    } finally {
      setIsCheckingOut(false)
    }
  }
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col w-[820px] mx-auto">
      <div className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <div className="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
          <ShoppingBag className="w-5 h-5 text-white" />
        </div>
        <h1 className="text-lg font-semibold">Basket</h1>
      </div>

      <div className="flex-1 divide-y bg-white">
        {Object.values(cart).map(({ product, quantity }) => (
          <div key={product.id} className="px-4 py-3 flex items-center gap-3">
            <div className="w-12 h-12 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
              <img src={product.image || "/placeholder.svg"} alt={product.name} className="w-full h-full object-contain" />
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-sm text-gray-800 truncate">{product.name}</div>
              <div className="text-xs text-gray-500">{product.price} {product.discount && <span className="text-green-600 ml-2">{product.discount} off</span>}</div>
            </div>
            <div className="flex items-center gap-2">
              <button onClick={() => decrement(product)} className="w-7 h-7 rounded-full bg-black text-white flex items-center justify-center">
                <Minus className="w-4 h-4" />
              </button>
              <span className="w-8 text-center font-medium">{quantity}</span>
              <button onClick={() => increment(product)} className="w-7 h-7 rounded-full bg-black text-white flex items-center justify-center">
                <Plus className="w-4 h-4" />
              </button>
            </div>
          </div>
        ))}
        {Object.keys(cart).length === 0 && (
          <div className="px-4 py-6 text-center text-sm text-gray-500">Your basket is empty</div>
        )}
      </div>

      {totals.units > 0 && (
        <div className="bg-white border-t px-4 py-3 space-y-1">
          <div className="flex items-center justify-between text-sm">
            <span className="text-gray-700">{totals.units} Units | {totals.skus} SKUs</span>
            <div className="flex items-center gap-2">
              <span className="font-semibold">{formatMoney(totals.total)}</span>
              {totals.totalDiscount > 0 && (
                <span className="text-green-600 text-xs">{formatMoney(totals.totalDiscount)} off</span>
              )}
            </div>
          </div>
          <button 
            onClick={handleCheckout}
            disabled={isCheckingOut}
            className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-2 rounded-md font-medium"
          >
            {isCheckingOut ? 'Processing...' : 'Checkout'}
          </button>
          <div className="text-[11px] text-center text-gray-500">Includes FREE delivery</div>
        </div>
      )}

      <div className="bg-white border-t px-4 py-2">
        <div className="flex justify-around">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-1 py-2">
            <Home className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center gap-1 py-2">
            <ShoppingBag className="w-6 h-6 text-gray-800" />
            <span className="text-xs text-gray-800 font-medium">Shop</span>
          </button>
          <button className="flex flex-col items-center gap-1 py-2">
            <QrCode className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Scan</span>
          </button>
          <button className="flex flex-col items-center gap-1 py-2">
            <Wallet className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Wallet</span>
          </button>
          <button className="flex flex-col items-center gap-1 py-2">
            <User className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Account</span>
          </button>
        </div>
      </div>
    </div>
  )
}


