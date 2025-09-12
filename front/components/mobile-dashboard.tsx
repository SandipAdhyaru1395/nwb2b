"use client"

import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { ShoppingBag, Heart, Home, Wallet, User, ChevronRight, Bell, Gift } from "lucide-react"

interface MobileDashboardProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account") => void
}

export function MobileDashboard({ onNavigate }: MobileDashboardProps) {
  return (
    <div className="min-h-screen bg-gray-50 w-[820px] mx-auto">
      {/* Header */}
      <header className="bg-white px-4 py-3 flex items-center justify-between border-b">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
            <span className="text-white font-bold text-sm">Z</span>
          </div>
          <h1 className="font-semibold text-gray-900">Dashboard</h1>
        </div>
      </header>

      {/* Main Content */}
      <main className="pb-20">
        {/* ZYN Promotional Banner */}
        <div className="relative bg-gradient-to-r from-cyan-400 to-blue-500 mx-4 mt-4 rounded-lg overflow-hidden">
          <div className="p-6 text-white">
            <div className="flex justify-between items-start">
              <div className="flex-1">
                <h2 className="text-xl font-bold mb-1">We stand for the best.</h2>
                <p className="text-sm opacity-90 mb-3">
                  The World's no.1 nicotine pouch brand, delivering long-lasting flavour.
                </p>
                <Badge className="bg-red-500 hover:bg-red-600 text-white border-0">Available Now</Badge>
              </div>
              <div className="ml-4">
                <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                  <span className="text-2xl font-bold">ZYN</span>
                </div>
                <Badge className="bg-white text-blue-600 text-xs mt-2 ml-2">WORLD'S NO.1</Badge>
              </div>
            </div>
          </div>
          {/* Background pattern */}
          <div className="absolute inset-0 opacity-10">
            <div className="text-6xl font-bold text-white transform rotate-12 absolute -right-4 top-4">ZYN ZYN ZYN</div>
          </div>
        </div>

        {/* Referral Rewards */}
        <Card className="mx-4 mt-4 bg-green-500 border-0 text-white">
          <div className="p-4 flex items-center justify-between">
            <div>
              <h3 className="font-semibold mb-1">Referral Rewards</h3>
              <p className="text-sm opacity-90">Refer a Retailer to earn Rewards</p>
            </div>
            <Gift className="w-8 h-8" />
          </div>
        </Card>

        {/* Wallet Credit */}
        <Card className="mx-4 mt-4">
          <div className="p-4 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <Wallet className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">£3.50 credit in your wallet</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </div>
        </Card>

        {/* Action Buttons */}
        <div className="grid grid-cols-2 gap-4 mx-4 mt-4">
          <Button
            onClick={() => onNavigate("shop")}
            className="bg-green-500 hover:bg-green-600 text-white h-12 rounded-lg"
          >
            <ShoppingBag className="w-5 h-5 mr-2" />
            Shop
          </Button>
          <Button className="bg-green-500 hover:bg-green-600 text-white h-12 rounded-lg">
            <Heart className="w-5 h-5 mr-2" />
            Favourites
          </Button>
        </div>

        {/* Recent Notifications */}
        <div className="mx-4 mt-6">
          <h3 className="font-semibold text-gray-900 mb-3">Recent Notifications</h3>

          <Card className="mb-3">
            <div className="p-4 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <Bell className="w-4 h-4 text-green-600" />
                </div>
                <span className="text-sm">You've left products in your basket</span>
              </div>
              <ChevronRight className="w-5 h-5 text-gray-400" />
            </div>
          </Card>

          <Card>
            <div className="p-4 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-between">
                  <Bell className="w-4 h-4 text-green-600" />
                </div>
                <span className="text-sm">Lost Mary Nera 30K ONE DAY PROMOTION!</span>
              </div>
              <ChevronRight className="w-5 h-5 text-gray-400" />
            </div>
          </Card>
        </div>
      </main>

      {/* Bottom Navigation - Updated to include account navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-[820px] bg-white border-t">
        <div className="grid grid-cols-4 py-2">
          <button className="flex flex-col items-center py-2 text-green-600">
            <Home className="w-5 h-5" />
            <span className="text-xs mt-1">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center py-2 text-gray-400">
            <ShoppingBag className="w-5 h-5" />
            <span className="text-xs mt-1">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center py-2 text-gray-400">
            <Wallet className="w-5 h-5" />
            <span className="text-xs mt-1">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center py-2 text-gray-400">
            <User className="w-5 h-5" />
            <span className="text-xs mt-1">Account</span>
          </button>
        </div>
      </nav>
    </div>
  )
}
