"use client"

import { Card } from "@/components/ui/card"
import { Home, ShoppingBag, Wallet, User } from "lucide-react"

interface MobileWalletProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account") => void
}

export function MobileWallet({ onNavigate }: MobileWalletProps) {
  return (
    <div className="w-[820px] mx-auto bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="bg-white p-4 flex items-center gap-3 border-b">
        <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
          <Wallet className="w-5 h-5 text-white" />
        </div>
        <h1 className="text-lg font-semibold text-gray-900">Wallet</h1>
      </div>

      {/* ZYN Promotional Banner */}
      <div className="relative">
        <img
          src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/frame_060.jpg-6z0boXtncnN21CU1nNOmFLhLqMEObP.jpeg"
          alt="ZYN Promotional Banner"
          className="w-full h-48 object-cover object-top"
        />
      </div>

      {/* Wallet Content */}
      <div className="p-4 space-y-6">
        {/* Wallet Balance */}
        <div>
          <h2 className="text-base font-medium text-gray-900 mb-3">Your wallet balance</h2>
          <Card className="p-4 border border-gray-200">
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                <span className="text-white text-xs font-bold">£</span>
              </div>
              <span className="text-xl font-semibold text-gray-900">£3.50</span>
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

      {/* Bottom Navigation - Updated to include account navigation */}
      <div className="fixed bottom-0 w-[820px] bg-white border-t border-gray-200">
        <div className="flex">
          <button
            onClick={() => onNavigate("dashboard")}
            className="flex-1 flex flex-col items-center py-2 px-4 text-gray-600 hover:text-green-600"
          >
            <Home className="w-6 h-6 mb-1" />
            <span className="text-xs">Dashboard</span>
          </button>
          <button
            onClick={() => onNavigate("shop")}
            className="flex-1 flex flex-col items-center py-2 px-4 text-gray-600 hover:text-green-600"
          >
            <ShoppingBag className="w-6 h-6 mb-1" />
            <span className="text-xs">Shop</span>
          </button>
          <button
            onClick={() => onNavigate("wallet")}
            className="flex-1 flex flex-col items-center py-2 px-4 text-green-600"
          >
            <Wallet className="w-6 h-6 mb-1" />
            <span className="text-xs">Wallet</span>
          </button>
          <button
            onClick={() => onNavigate("account")}
            className="flex-1 flex flex-col items-center py-2 px-4 text-gray-600 hover:text-green-600"
          >
            <User className="w-6 h-6 mb-1" />
            <span className="text-xs">Account</span>
          </button>
        </div>
      </div>
    </div>
  )
}
