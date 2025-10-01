"use client"

import { Button } from "@/components/ui/button"
import { Banner } from "@/components/banner"
import { ChevronRight, User, Building, GitBranch, Lightbulb, BarChart3, FileText, Bell, Shield, Home, ShoppingBag, Wallet,LogOut } from "lucide-react"
import { useRouter } from "next/navigation"
import api from "@/lib/axios"

interface ProductItem {
  id: number
  name: string
  image: string
  price: string
  discount?: string
}

interface MobileAccountProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account" | "rep-details" | "company-details") => void
  cart: Record<number, { product: ProductItem; quantity: number }>
  increment: (product: ProductItem) => void
  decrement: (product: ProductItem) => void
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number }
  clearCart: () => void
}

export function MobileAccount({ onNavigate }: MobileAccountProps) {
  const router = useRouter()

  const handleLogout = async () => {
    try {
      try {
        await api.post("/logout")
      } catch {}
      try {
        window.localStorage.removeItem("auth_token")
      } catch {}
    } finally {
      try {
        router.replace("/login")
      } catch {
        if (typeof window !== "undefined") {
          window.location.href = "/login"
        }
      }
    }
  }

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
      {/* Banner */}
      <Banner />

      {/* Account Menu Items */}
      <div className="py-4 pb-30 space-y-4">
        {/* Account Details Section */}
        <div className="space-y-2">
          <Button
            variant="outline"
            onClick={() => onNavigate("rep-details")}
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <User className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Rep Details</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>

          <Button
            variant="outline"
            onClick={() => onNavigate("company-details")}
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <Building className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Company</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>

          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <GitBranch className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Branches</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>
        </div>

        {/* Utilities Section */}
        <Button
          variant="outline"
          className="w-full py-5 justify-between text-left border-yellow-200 bg-yellow-50 hover:bg-yellow-100 hover:cursor-pointer"
        >
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
              <Lightbulb className="w-4 h-4 text-yellow-600" />
            </div>
            <span className="font-medium">Revo Utilities - guaranteed to reduce your bills!</span>
          </div>
          <ChevronRight className="w-5 h-5 text-gray-400" />
        </Button>

        {/* Services Section */}
        <div className="space-y-2">
          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <BarChart3 className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">Services & Display Solutions</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>

          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <FileText className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">Contracts</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>
        </div>

        {/* Settings Section */}
        <div className="space-y-2 pt-4">
          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <Bell className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Notification Preferences</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>

          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <Shield className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Authentication Settings</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>

          <Button
            variant="outline"
            className="w-full py-5 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent hover:cursor-pointer"
            onClick={handleLogout}
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <LogOut className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">Logout</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>
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
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Wallet className="w-5 h-5" />
            <span className="text-xs mt-1">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center py-2 text-green-600 hover:text-green-600 hover:cursor-pointer">
            <User className="w-5 h-5" />
            <span className="text-xs mt-1">Account</span>
          </button>
        </div>
      </nav>
    </div>
  )
}
