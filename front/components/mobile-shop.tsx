"use client"

import { useState } from "react"
import { Search, Filter, X, ShoppingBag, ChevronDown, ChevronUp, Plus, Star, Home, Wallet, User } from "lucide-react"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"

interface MobileShopProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account") => void
}

const categories = [
  { name: "Deals & Offers", color: "bg-yellow-400", expanded: false },
  { name: "Vaping", color: "bg-green-500", expanded: true },
  { name: "Big Puff Devices", color: "bg-green-200", expanded: false },
  { name: "NEW Compliant 600 Puffs", color: "bg-green-200", expanded: false },
  { name: "E-liquids", color: "bg-green-200", expanded: true },
]

const brands = [
  { name: "IVG Intense Nic Salts", badge: "NEW", badgeColor: "bg-green-500", expanded: false },
  { name: "Elux Legend Nic Salt", badge: "HOT", badgeColor: "bg-red-500", expanded: true },
]

const products = [
  { id: 1, name: "Purple Vape", image: "/placeholder-5tl12.png", price: "£1.20", discount: "£0.05" },
  { id: 2, name: "Black Vape", image: "/black-vape-bottle.png", price: "£1.20", discount: "£0.05" },
  { id: 3, name: "Vape Device", image: "/placeholder-8ylx0.png", price: "£1.20", discount: "£0.05" },
  { id: 4, name: "Blue Vape", image: "/placeholder-yjfge.png", price: "£1.20", discount: "£0.05" },
  { id: 5, name: "Grey Vape", image: "/placeholder-9g5qg.png", price: "£1.20", discount: "£0.05" },
  { id: 6, name: "Vape Kit", image: "/placeholder-95zws.png", price: "£1.20", discount: "£0.05" },
]

export function MobileShop({ onNavigate }: MobileShopProps) {
  const [searchQuery, setSearchQuery] = useState("Grape")
  const [expandedCategories, setExpandedCategories] = useState<string[]>(["Vaping", "E-liquids"])
  const [expandedBrands, setExpandedBrands] = useState<string[]>(["Elux Legend Nic Salt"])

  const toggleCategory = (categoryName: string) => {
    setExpandedCategories((prev) =>
      prev.includes(categoryName) ? prev.filter((name) => name !== categoryName) : [...prev, categoryName],
    )
  }

  const toggleBrand = (brandName: string) => {
    setExpandedBrands((prev) =>
      prev.includes(brandName) ? prev.filter((name) => name !== brandName) : [...prev, brandName],
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col w-[820px] mx-auto">
      {/* Header */}
      <div className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <div className="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
          <ShoppingBag className="w-5 h-5 text-white" />
        </div>
        <h1 className="text-lg font-semibold">Shop</h1>
      </div>

      {/* Search Bar */}
      <div className="bg-white px-4 py-3 border-b">
        <div className="relative flex items-center gap-2">
          <Filter className="w-5 h-5 text-gray-400" />
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10 pr-10 bg-gray-50 border-gray-200"
              placeholder="Search products..."
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-3 top-1/2 transform -translate-y-1/2"
              >
                <X className="w-4 h-4 text-gray-400" />
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Categories */}
      <div className="flex-1 px-4 py-4 space-y-2">
        {categories.map((category) => (
          <div key={category.name} className="space-y-2">
            <button
              onClick={() => toggleCategory(category.name)}
              className={`w-full ${category.color} text-white px-4 py-3 rounded-lg flex items-center justify-between font-medium`}
            >
              <span>{category.name}</span>
              {expandedCategories.includes(category.name) ? (
                <ChevronUp className="w-5 h-5" />
              ) : (
                <ChevronDown className="w-5 h-5" />
              )}
            </button>
          </div>
        ))}

        {/* Brand Sections */}
        <div className="space-y-2 mt-4">
          {brands.map((brand) => (
            <div key={brand.name} className="space-y-2">
              <button
                onClick={() => toggleBrand(brand.name)}
                className="w-full bg-gray-100 px-4 py-3 rounded-lg flex items-center justify-between"
              >
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white rounded border flex items-center justify-center">
                    <div className="w-6 h-6 bg-gray-300 rounded"></div>
                  </div>
                  <span className="font-medium text-gray-800">{brand.name}</span>
                  <Badge className={`${brand.badgeColor} text-white text-xs px-2 py-1`}>{brand.badge}</Badge>
                </div>
                {expandedBrands.includes(brand.name) ? (
                  <ChevronUp className="w-5 h-5 text-gray-600" />
                ) : (
                  <ChevronDown className="w-5 h-5 text-gray-600" />
                )}
              </button>

              {/* Product Grid - Only show for expanded brands */}
              {expandedBrands.includes(brand.name) && (
                <div className="grid grid-cols-3 gap-3 px-2">
                  {products.map((product) => (
                    <div key={product.id} className="bg-white rounded-lg p-3 border relative">
                      <button className="absolute top-2 right-2 w-6 h-6 bg-black rounded-full flex items-center justify-center">
                        <Plus className="w-4 h-4 text-white" />
                      </button>

                      <div className="aspect-square mb-2 flex items-center justify-center">
                        <img
                          src={product.image || "/placeholder.svg"}
                          alt={product.name}
                          className="w-full h-full object-contain"
                        />
                      </div>

                      <div className="space-y-1">
                        <div className="flex items-center justify-between">
                          <span className="font-semibold text-sm">{product.price}</span>
                          <div className="bg-green-500 text-white text-xs px-1 py-0.5 rounded">{product.discount}</div>
                        </div>
                        <div className="flex items-center gap-1">
                          <Star className="w-3 h-3 text-gray-300 fill-gray-300" />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Bottom Navigation */}
      <div className="bg-white border-t px-4 py-2">
        <div className="grid grid-cols-4">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-1 py-2">
            <Home className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Dashboard</span>
          </button>
          <button className="flex flex-col items-center gap-1 py-2">
            <ShoppingBag className="w-6 h-6 text-gray-800" />
            <span className="text-xs text-gray-800 font-medium">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-1 py-2">
            <Wallet className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-1 py-2">
            <User className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Account</span>
          </button>
        </div>
      </div>
    </div>
  )
}
