"use client"

import { Button } from "@/components/ui/button"
import { ChevronRight, User, Building, GitBranch, Lightbulb, BarChart3, FileText, Bell, Shield } from "lucide-react"

interface MobileAccountProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account") => void
}

export function MobileAccount({ onNavigate }: MobileAccountProps) {
  return (
    <div className="w-[820px] mx-auto bg-white min-h-screen">
      {/* ZYN Promotional Banner */}
      <div className="relative bg-gradient-to-r from-cyan-400 to-blue-500 p-6 text-white">
        <div className="flex items-center justify-between">
          <div className="flex-1">
            <h1 className="text-2xl font-bold mb-2">We stand for the best.</h1>
            <p className="text-sm mb-1">The World's no.1 nicotine pouch brand,</p>
            <p className="text-sm">delivering long-lasting flavour.</p>
            <div className="inline-block bg-red-600 text-white px-3 py-1 rounded text-sm font-semibold mt-2">
              Available Now
            </div>
          </div>
          <div className="relative">
            <div className="w-32 h-32 bg-white rounded-full flex items-center justify-center">
              <div className="text-blue-600 font-bold text-lg">ZYN</div>
            </div>
            <div className="absolute -top-2 -right-2 bg-white text-blue-600 px-2 py-1 rounded text-xs font-bold">
              WORLD'S
              <br />
              NO.1
            </div>
          </div>
        </div>
        <div className="text-xs mt-4 opacity-90">
          For Trade Only. Not for Distribution to Consumers. *PMI reported global shipment volumes and in-market sales
          estimates of nicotine pouch units, from December 2023 to December 2024.
        </div>
        <div className="text-xs mt-2 opacity-90">
          18+ This product is not risk free and contains nicotine, which is addictive. Only for use by adults who would
          otherwise continue to smoke or use nicotine.
        </div>
      </div>

      {/* Account Menu Items */}
      <div className="p-4 space-y-4">
        {/* Account Details Section */}
        <div className="space-y-2">
          <Button
            variant="outline"
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
          className="w-full h-14 justify-between text-left border-yellow-200 bg-yellow-50 hover:bg-yellow-100"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
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
            className="w-full h-14 justify-between text-left border-gray-200 hover:bg-gray-50 bg-transparent"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <Shield className="w-4 h-4 text-green-600" />
              </div>
              <span className="font-medium">My Authentication Settings</span>
            </div>
            <ChevronRight className="w-5 h-5 text-gray-400" />
          </Button>
        </div>
      </div>

      {/* Bottom Navigation */}
      <div className="fixed bottom-0 w-[820px] bg-white border-t border-gray-200">
        <div className="grid grid-cols-4 h-16">
          <Button
            variant="ghost"
            className="flex flex-col items-center justify-center h-full space-y-1"
            onClick={() => onNavigate("dashboard")}
          >
            <div className="w-6 h-6 rounded-full bg-gray-300"></div>
            <span className="text-xs text-gray-600">Dashboard</span>
          </Button>
          <Button
            variant="ghost"
            className="flex flex-col items-center justify-center h-full space-y-1"
            onClick={() => onNavigate("shop")}
          >
            <div className="w-6 h-6 rounded-full bg-gray-300"></div>
            <span className="text-xs text-gray-600">Shop</span>
          </Button>
          <Button
            variant="ghost"
            className="flex flex-col items-center justify-center h-full space-y-1"
            onClick={() => onNavigate("wallet")}
          >
            <div className="w-6 h-6 rounded-full bg-gray-300"></div>
            <span className="text-xs text-gray-600">Wallet</span>
          </Button>
          <Button variant="ghost" className="flex flex-col items-center justify-center h-full space-y-1 text-green-600">
            <div className="w-6 h-6 rounded-full bg-green-600"></div>
            <span className="text-xs">Account</span>
          </Button>
        </div>
      </div>
    </div>
  )
}
