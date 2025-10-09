"use client";

import api from "@/lib/axios";
import { useEffect, useState, useMemo } from "react";
import { Minus, Plus, Home, ShoppingBag, User, Wallet, Star } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter } from "@fortawesome/free-solid-svg-icons";
import { useCustomer } from "@/components/customer-provider";
import { useCurrency } from "@/components/currency-provider";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
}

interface MobileBasketProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileBasket({ onNavigate, cart, increment, decrement, totals, clearCart }: MobileBasketProps) {
  const [isCheckingOut, setIsCheckingOut] = useState(false);
  const [favourites, setFavourites] = useState<Record<number, boolean>>({});
  const { toast } = useToast();
  const { symbol, format } = useCurrency();
  const { refresh } = useCustomer();

  // Sync favourites from shared customer provider to local map
  const { favoriteProductIds, setFavorite } = useCustomer();
  useEffect(() => {
    const map: Record<number, boolean> = {};
    favoriteProductIds.forEach((id) => {
      map[id] = true;
    });
    setFavourites(map);
  }, [favoriteProductIds]);

  // Calculate total wallet credit from cart items
  const totalWalletCredit = useMemo(() => {
    return Object.values(cart).reduce((sum, { product, quantity }) => {
      const credit = typeof product.wallet_credit === "number" ? product.wallet_credit : 0;
      return sum + credit * quantity;
    }, 0);
  }, [cart]);

  const handleCheckout = async () => {
    setIsCheckingOut(true);
    try {
      const items = Object.values(cart).map(({ product, quantity }) => ({
        product_id: product.id,
        quantity: quantity,
      }));

      const { data: result } = await api.post("/checkout", {
        items,
        total: totals.total,
        units: totals.units,
        skus: totals.skus,
      });

      if (result.success) {
        toast({
          title: "Order Placed Successfully! 🎉",
          description: `Order Number: ${result.order_number}`,
          variant: "default",
        });
        // Ask dashboard to refresh orders list on next visit/mount
        try {
          sessionStorage.setItem("orders_needs_refresh", "1");
        } catch {}
        if (typeof window !== "undefined") {
          try {
            window.dispatchEvent(new Event("orders-refresh"));
          } catch {}
        }
        // Do not refresh product listing after checkout as requested
        // Refresh customer wallet balance
        try {
          await refresh();
        } catch {}
        // Clear the cart after successful checkout
        clearCart();
        onNavigate("dashboard");
      } else {
        toast({
          title: "Checkout Failed",
          description: result.message,
          variant: "destructive",
        });
      }
    } catch (error) {
      toast({
        title: "Checkout Failed",
        description: "Please try again later.",
        variant: "destructive",
      });
      console.error("Checkout error:", error);
    } finally {
      setIsCheckingOut(false);
    }
  };

  const toggleFavorite = async (product: ProductItem) => {
    const productId = product.id;
    const next = !favourites[productId];
    // optimistic update
    setFavourites((prev) => ({ ...prev, [productId]: next }));
    try {
      await setFavorite(productId, next);
      toast({ title: next ? "Added to favourites" : "Removed from favourites", description: product.name });
    } catch (e: any) {
      // revert on failure
      setFavourites((prev) => ({ ...prev, [productId]: !next }));
      toast({ title: "Failed to update favourites", description: e?.message || "Please try again", variant: "destructive" });
    }
  };
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col w-full max-w-[1000px] mx-auto">
      <div className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <div className="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
          <ShoppingBag className="w-5 h-5 text-white" />
        </div>
        <h1 className="text-lg font-semibold">Basket</h1>
      </div>

      <div className="flex-1 divide-y bg-white overflow-y-auto pb-48">
        {Object.values(cart).map(({ product, quantity }) => (
          <div key={product.id} className="px-4 py-3 flex items-center gap-3">
            <div className="w-12 h-12 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
              <img src={product.image || "/placeholder.svg"} alt={product.name} className="w-full h-full object-contain" />
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-sm text-gray-800 truncate">{product.name}</div>
              <div className="text-xs text-gray-500 flex items-center gap-2">
                <span>{product.price}</span>
                {typeof product.wallet_credit === "number" && product.wallet_credit > 0 && (
                  <span className="inline-flex items-center gap-1 text-green-600">
                    <Wallet className="w-4 h-4" />
                    <span className="font-medium">{product.wallet_credit.toFixed(2)}</span>
                  </span>
                )}
                {product.discount && <span className="text-green-600 ml-2">{product.discount} off</span>}
              </div>
            </div>
            <div className="flex items-center gap-2">
              {/* Favourite toggle */}
              <button onClick={() => toggleFavorite(product)} className={`w-8 h-8 rounded-full border flex items-center justify-center ${favourites[product.id] ? "bg-green-50 border-green-300" : "border-gray-300 bg-white"}`} aria-label="Toggle favourite">
                <Star className={`w-4 h-4 ${favourites[product.id] ? "text-green-600 fill-green-600" : "text-gray-400"}`} />
              </button>
              {/* Delete removes the product from cart */}
              <button
                onClick={() => {
                  let count = quantity;
                  while (count > 0) {
                    decrement(product);
                    count--;
                  }
                }}
                className="px-3 h-8 rounded border text-xs text-gray-700 bg-white hover:bg-gray-50 hover:cursor-pointer"
              >
                Delete
              </button>
              <button onClick={() => decrement(product)} className="rounded-full bg-green-500 p-2 text-black flex items-center justify-center hover:cursor-pointer">
                <Minus className="w-4 h-4" />
              </button>
              <span className="w-8 text-center font-medium">{quantity}</span>
              <button onClick={() => increment(product)} className="rounded-full bg-green-500 p-2 text-black flex items-center justify-center hover:cursor-pointer">
                <Plus className="w-4 h-4" />
              </button>
            </div>
          </div>
        ))}
        {Object.keys(cart).length === 0 && <div className="px-4 py-6 text-center text-sm text-gray-500">Your basket is empty</div>}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        {totals.units > 0 && (
          <div className="bg-white border-b px-4 py-2 space-y-1 box-shadow-top">
            <div className="flex items-center justify-center text-sm gap-2 pt-1">
              <span className="text-black font-semibold">{totals.units} Units</span>
              <span className="spacer"> | </span>
              <span className="text-black font-semibold">{totals.skus} SKUs</span>
              <span className="spacer"> | </span>
              <span className="font-semibold text-black">{format(totals.total)}</span>
              <span className="spacer"> | </span>
              <div className="flex items-center">
                {totalWalletCredit > 0 && (
                  <span className="inline-flex items-center gap-1 text-green-600 text-sm font-semibold">
                    <Wallet className="w-4 h-4" />
                    <span>
                      {symbol}
                      {totalWalletCredit.toFixed(2)}
                    </span>
                  </span>
                )}
                {totals.totalDiscount > 0 && <span className="text-green-600 text-xs">{format(totals.totalDiscount)} off</span>}
              </div>
            </div>
            <div className="text-sm font-semibold text-center text-gray-400 pb-1">Spend {format(4.5)} more for FREE delivery</div>
            <button onClick={() => onNavigate("basket")} className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-sm font-semibold hover:cursor-pointer text-lg box-shadow-bottom">
              View Basket
            </button>
          </div>
        )}
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faShop} className="text-[#607565]" style={{ width: "30px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faWallet} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faUser} className="text-[#607565]" style={{ width: "21px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
