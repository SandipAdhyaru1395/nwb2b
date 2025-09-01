"use client"

import { useState } from "react"
import {
  Search,
  Filter,
  X,
  ShoppingBag,
  ChevronDown,
  ChevronUp,
  Plus,
  Star,
  Home,
  QrCode,
  Wallet,
  User,
} from "lucide-react"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"

interface MobileShopProps {
  onNavigate: (page: "dashboard" | "shop") => void
}

const categories = [
  { 
    name: "Vaping", 
    color: "bg-green-500", 
    expanded: false,
    brands: [
      { 
        name: "VapeMaster Pro", 
        badge: "PREMIUM", 
        badgeColor: "bg-purple-500", 
        expanded: false,
        products: [
          { id: 7, name: "Pro Vape Device", image: "/placeholder-8ylx0.png", price: "£25.00", discount: "£5.00", rating: 4.9, reviews: 312 },
          { id: 8, name: "Master Kit", image: "/placeholder-yjfge.png", price: "£35.00", discount: "£7.00", rating: 4.8, reviews: 289 }
        ]
      },
      { 
        name: "Cloud Chaser", 
        badge: "POPULAR", 
        badgeColor: "bg-indigo-500", 
        expanded: false,
        products: [
          { id: 9, name: "Cloud Device", image: "/placeholder-9g5qg.png", price: "£18.00", discount: "£3.00", rating: 4.7, reviews: 203 },
          { id: 10, name: "Chaser Kit", image: "/placeholder-95zws.png", price: "£22.00", discount: "£4.00", rating: 4.6, reviews: 178 }
        ]
      },
      { 
        name: "VapeTech Elite", 
        badge: "TRENDING", 
        badgeColor: "bg-pink-500", 
        expanded: false,
        products: [
          { id: 11, name: "Elite Device", image: "/placeholder-5tl12.png", price: "£28.00", discount: "£6.00", rating: 4.8, reviews: 245 },
          { id: 12, name: "Tech Kit", image: "/placeholder-8ylx0.png", price: "£32.00", discount: "£8.00", rating: 4.7, reviews: 198 }
        ]
      }
    ]
  },
  { 
    name: "Big Puff Devices", 
    color: "bg-green-200", 
    expanded: false,
    brands: [
      { 
        name: "BigPuff Max", 
        badge: "BEST", 
        badgeColor: "bg-green-600", 
        expanded: false,
        products: [
          { id: 13, name: "Max Device", image: "/placeholder-yjfge.png", price: "£15.00", discount: "£2.00", rating: 4.6, reviews: 156 },
          { id: 14, name: "Big Puff Kit", image: "/placeholder-9g5qg.png", price: "£19.00", discount: "£3.00", rating: 4.5, reviews: 134 }
        ]
      },
      { 
        name: "Cloud King", 
        badge: "TOP", 
        badgeColor: "bg-teal-500", 
        expanded: false,
        products: [
          { id: 15, name: "King Device", image: "/placeholder-95zws.png", price: "£17.00", discount: "£2.50", rating: 4.7, reviews: 189 },
          { id: 16, name: "Cloud Kit", image: "/placeholder-5tl12.png", price: "£21.00", discount: "£4.00", rating: 4.6, reviews: 167 }
        ]
      },
      { 
        name: "Vapor Giant", 
        badge: "HOT", 
        badgeColor: "bg-red-500", 
        expanded: false,
        products: [
          { id: 17, name: "Giant Device", image: "/placeholder-8ylx0.png", price: "£20.00", discount: "£3.50", rating: 4.8, reviews: 223 },
          { id: 18, name: "Vapor Kit", image: "/placeholder-yjfge.png", price: "£24.00", discount: "£5.00", rating: 4.7, reviews: 201 }
        ]
      }
    ]
  },
  { 
    name: "NEW Compliant 600 Puffs", 
    color: "bg-green-200", 
    expanded: false,
    brands: [
      { 
        name: "Compliance Plus", 
        badge: "NEW", 
        badgeColor: "bg-green-500", 
        expanded: false,
        products: [
          { id: 19, name: "Compliance Device", image: "/placeholder-9g5qg.png", price: "£12.00", discount: "£1.50", rating: 4.4, reviews: 98 },
          { id: 20, name: "Plus Kit", image: "/placeholder-95zws.png", price: "£14.00", discount: "£2.00", rating: 4.3, reviews: 87 }
        ]
      },
      { 
        name: "600 Puff Elite", 
        badge: "COMPLIANT", 
        badgeColor: "bg-blue-600", 
        expanded: false,
        products: [
          { id: 21, name: "600 Puff Device", image: "/placeholder-5tl12.png", price: "£10.00", discount: "£1.00", rating: 4.5, reviews: 112 },
          { id: 22, name: "Elite Kit", image: "/placeholder-8ylx0.png", price: "£13.00", discount: "£1.50", rating: 4.4, reviews: 95 }
        ]
      },
      { 
        name: "Regulated Vape", 
        badge: "SAFE", 
        badgeColor: "bg-emerald-500", 
        expanded: false,
        products: [
          { id: 23, name: "Regulated Device", image: "/placeholder-yjfge.png", price: "£11.00", discount: "£1.25", rating: 4.6, reviews: 103 },
          { id: 24, name: "Safe Kit", image: "/placeholder-9g5qg.png", price: "£15.00", discount: "£2.00", rating: 4.5, reviews: 89 }
        ]
      }
    ]
  },
  { 
    name: "E-liquids", 
    color: "bg-green-200", 
    expanded: false,
    brands: [
      { 
        name: "IVG Intense Nic Salts", 
        badge: "NEW", 
        badgeColor: "bg-green-500", 
        expanded: false,
        products: [
          { id: 25, name: "Strawberry Salt", image: "/placeholder-95zws.png", price: "£4.50", discount: "£0.50", rating: 4.8, reviews: 234 },
          { id: 26, name: "Mint Salt", image: "/placeholder-5tl12.png", price: "£4.50", discount: "£0.50", rating: 4.7, reviews: 198 },
          { id: 27, name: "Vanilla Salt", image: "/placeholder-8ylx0.png", price: "£4.50", discount: "£0.50", rating: 4.6, reviews: 167 }
        ]
      },
      { 
        name: "Elux Legend Nic Salt", 
        badge: "HOT", 
        badgeColor: "bg-red-500", 
        expanded: false,
        products: [
          { id: 28, name: "Cherry Legend", image: "/placeholder-yjfge.png", price: "£5.00", discount: "£0.75", rating: 4.9, reviews: 312 },
          { id: 29, name: "Coffee Legend", image: "/placeholder-9g5qg.png", price: "£5.00", discount: "£0.75", rating: 4.8, reviews: 289 },
          { id: 30, name: "Tropical Legend", image: "/placeholder-95zws.png", price: "£5.00", discount: "£0.75", rating: 4.7, reviews: 245 }
        ]
      },
      { 
        name: "VapeJuice Pro", 
        badge: "PREMIUM", 
        badgeColor: "bg-purple-500", 
        expanded: false,
        products: [
          { id: 31, name: "Premium Strawberry", image: "/placeholder-5tl12.png", price: "£6.50", discount: "£1.00", rating: 4.9, reviews: 178 },
          { id: 32, name: "Premium Mint", image: "/placeholder-8ylx0.png", price: "£6.50", discount: "£1.00", rating: 4.8, reviews: 156 }
        ]
      },
      { 
        name: "Cloud Liquid", 
        badge: "POPULAR", 
        badgeColor: "bg-indigo-500", 
        expanded: false,
        products: [
          { id: 33, name: "Cloud Berry", image: "/placeholder-yjfge.png", price: "£5.50", discount: "£0.75", rating: 4.7, reviews: 134 },
          { id: 34, name: "Cloud Citrus", image: "/placeholder-9g5qg.png", price: "£5.50", discount: "£0.75", rating: 4.6, reviews: 123 }
        ]
      }
    ]
  },
]

export function MobileShop({ onNavigate }: MobileShopProps) {
  const [searchQuery, setSearchQuery] = useState("")
  const [expandedCategories, setExpandedCategories] = useState<string[]>([])
  const [expandedBrands, setExpandedBrands] = useState<string[]>([])

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

            {/* Brand Sections - Only show for expanded categories */}
            {expandedCategories.includes(category.name) && (
              <div className="space-y-2 ml-4">
                {category.brands.map((brand) => (
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
                        {brand.products.map((product) => (
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
                                <span className="text-xs text-gray-500">{product.rating}</span>
                                <span className="text-xs text-gray-400">({product.reviews})</span>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Bottom Navigation */}
      <div className="bg-white border-t px-4 py-2">
        <div className="flex justify-around">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-1 py-2">
            <Home className="w-6 h-6 text-gray-400" />
            <span className="text-xs text-gray-600">Dashboard</span>
          </button>
          <button className="flex flex-col items-center gap-1 py-2">
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
