"use client"

import { useEffect, useState } from "react"
import {
  Search,
  Filter,
  X,
  ShoppingBag,
  ChevronDown,
  ChevronUp,
  Plus,
  Minus,
  Star,
  Home,
  QrCode,
  Wallet,
  User,
} from "lucide-react"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { useToast } from "@/hooks/use-toast"

interface MobileShopProps {
  onNavigate: (page: "dashboard" | "shop" | "basket") => void
  cart: Record<number, { product: ProductItem; quantity: number }>
  increment: (product: ProductItem) => void
  decrement: (product: ProductItem) => void
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number }
  formatMoney: (n: number) => string
}

interface ProductItem {
  id: number
  name: string
  image: string
  price: string
  discount?: string
}

interface SubcategoryItem {
  name: string
  badge?: string
  badgeColor?: string
  products: ProductItem[]
}

interface CategoryItem {
  name: string
  color: string
  subcategories: SubcategoryItem[]
}

// fetched categories state
const initialCategories: CategoryItem[] = []

export function MobileShop({ onNavigate, cart, increment, decrement, totals, formatMoney }: MobileShopProps) {
  const [searchQuery, setSearchQuery] = useState("")
  const [categories, setCategories] = useState<CategoryItem[]>(initialCategories)
  const [expandedCategories, setExpandedCategories] = useState<string[]>(["Vaping", "E-liquids"])
  const [expandedSubcategories, setExpandedSubcategories] = useState<string[]>(["Vaping::Disposables", "E-liquids::Nic Salts"])
  const { toast } = useToast()

  const toggleCategory = (categoryName: string) => {
    setExpandedCategories((prev) =>
      prev.includes(categoryName) ? [] : [categoryName] // only keep one open
    )
    // also collapse all subcategories when switching category
    setExpandedSubcategories([])
  }

  const toggleSubcategory = (categoryName: string, subcategoryName: string) => {
    const key = `${categoryName}::${subcategoryName}`
    setExpandedSubcategories((prev) => (prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key]))
  }

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await fetch('/api/products', { cache: 'no-store' })
        if (!res.ok) throw new Error('Failed to fetch structure')
        const data = await res.json()
        if (Array.isArray(data?.categories)) {
          setCategories(data.categories)
        }
      } catch (e) {
        // keep categories empty on error
      }
    }
    fetchData()
  }, [])

  // cart and totals are provided by parent

  const handleIncrement = (product: ProductItem) => {
    increment(product)
    toast({
      title: "Added to Cart",
      description: `${product.name} added to your basket`,
    })
  }

  const handleDecrement = (product: ProductItem) => {
    const currentQuantity = cart[product.id]?.quantity || 0
    decrement(product)
    if (currentQuantity === 1) {
      toast({
        title: "Removed from Cart",
        description: `${product.name} removed from your basket`,
      })
    } else {
      toast({
        title: "Quantity Updated",
        description: `${product.name} quantity decreased`,
      })
    }
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
      <div className="px-4 py-4 space-y-2 pb-48">
        {categories.map((category) => (
          <div key={category.name} className="space-y-2">
            <button
              onClick={() => toggleCategory(category.name)}
              className={`w-full bg-green-500 px-4 py-3 rounded-lg flex items-center justify-between font-medium`}
            >
              <span>{category.name}</span>
              {expandedCategories.includes(category.name) ? (
                <ChevronUp className="w-5 h-5" />
              ) : (
                <ChevronDown className="w-5 h-5" />
              )}
            </button>

            {expandedCategories.includes(category.name) && (
              <div className="space-y-2 mt-2">
                {category.subcategories.map((subcategory) => {
                  const key = `${category.name}::${subcategory.name}`
                  const isOpen = expandedSubcategories.includes(key)
                  return (
                    <div key={key} className="space-y-2">
                      <button
                        onClick={() => toggleSubcategory(category.name, subcategory.name)}
                        className="w-full bg-green-100 px-4 py-3 rounded-lg flex items-center justify-between"
                      >
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 bg-white rounded border flex items-center justify-center">
                            <div className="w-6 h-6 bg-gray-300 rounded"></div>
                          </div>
                          <span className="font-medium text-gray-800">{subcategory.name}</span>
                          {subcategory.badge && (
                            <Badge className={`${subcategory.badgeColor} text-white text-xs px-2 py-1`}>{subcategory.badge}</Badge>
                          )}
                        </div>
                        {isOpen ? (
                          <ChevronUp className="w-5 h-5 text-gray-600" />
                        ) : (
                          <ChevronDown className="w-5 h-5 text-gray-600" />
                        )}
                      </button>

                      {isOpen && (
                        <div className="grid grid-cols-3 gap-3 px-2">
                          {subcategory.products.map((product) => (
                            <div key={product.id} className="bg-white rounded-lg p-3 border relative">
                              {/* Add / Quantity controls */}
                              {cart[product.id]?.quantity ? (
                                <div className="absolute top-2 right-2 flex items-center gap-1 bg-white border rounded-full px-2 py-1 shadow-sm">
                                  <button onClick={() => handleDecrement(product)} className="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center">
                                    <Minus className="w-4 h-4" />
                                  </button>
                                  <span className="min-w-[1.5rem] text-center text-sm font-medium">{cart[product.id]?.quantity}</span>
                                  <button onClick={() => handleIncrement(product)} className="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center">
                                    <Plus className="w-4 h-4" />
                                  </button>
                                </div>
                              ) : (
                                <button onClick={() => handleIncrement(product)} className="absolute top-2 right-2 w-6 h-6 bg-black rounded-full flex items-center justify-center">
                                  <Plus className="w-4 h-4 text-white" />
                                </button>
                              )}

                              <div className="aspect-square mb-2 flex items-center justify-center">
                                <img
                                  src={product.image || "/placeholder.svg"}
                                  alt={product.name}
                                  className="w-full h-full object-contain"
                                />
                              </div>

                              <div className="space-y-1">
                                <div className="justify-items-end">
                                  <Star className="w-5 h-5 text-gray-300 fill-gray-300" />
                                </div>
                                <div className="flex items-center justify-between">
                                  <span className="font-semibold text-sm">{product.price}</span>
                                  {product.discount && (
                                    <div className="bg-green-500 text-white text-xs px-1 py-0.5 rounded">{product.discount}</div>
                                  )}
                                </div>
                                <div className="text-center">
                                  <span className="text-xs text-gray-600">{product.name}</span>
                                </div>

                              </div>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  )
                })}
              </div>
            )}
          </div>
        ))}
      </div>





      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-[820px] bg-white border-t">
        {/* Basket Summary (shows when items in cart) */}
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
            <button onClick={() => onNavigate("basket")} className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md font-medium">View Basket</button>
            <div className="text-[11px] text-center text-gray-500">Spend £4.50 more for FREE delivery</div>
          </div>
        )}
        <div className="grid grid-cols-5 py-2">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center py-2 text-gray-400">
            <Home className="w-5 h-5" />
            <span className="text-xs mt-1">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center py-2 text-green-600">
            <ShoppingBag className="w-5 h-5" />
            <span className="text-xs mt-1">Shop</span>
          </button>
          <button className="flex flex-col items-center py-2 text-gray-400">
            <QrCode className="w-5 h-5" />
            <span className="text-xs mt-1">Scan</span>
          </button>
          <button className="flex flex-col items-center py-2 text-gray-400">
            <Wallet className="w-5 h-5" />
            <span className="text-xs mt-1">Wallet</span>
          </button>
          <button className="flex flex-col items-center py-2 text-gray-400">
            <User className="w-5 h-5" />
            <span className="text-xs mt-1">Account</span>
          </button>
        </div>
      </nav>
    </div>
  )
}
