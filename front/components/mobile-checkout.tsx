"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package, ChevronRight, ChevronDown } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter, faTruck, faChevronUp, faChevronDown, faCheck } from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";
import FloatingInput from "./ui/floating-input";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
}

interface MobileCheckoutProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders") => void;
  onBack: () => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileCheckout({ onNavigate, onBack, cart, totals, clearCart }: MobileCheckoutProps) {
  const [isProcessing, setIsProcessing] = useState(false);
  const [isDispatchExpanded, setIsDispatchExpanded] = useState(false);
  const { toast } = useToast();
  const { refresh } = useCustomer();

  const handleContinueToPayment = async () => {
    setIsProcessing(true);
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
      setIsProcessing(false);
    }
  };
  return (
    <div className="min-h-screen w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
                <div className="flex items-center">
                    <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
                        <FontAwesomeIcon icon={faTruck} className="text-green-600" style={{ width: "27px", height: "24px" }} />
                    </div>
                    <span onClick={() => onNavigate("account")} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">Shop</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">Basket</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span className="text-[16px] font-semibold">Checkout</span>
                </div>
            </div>

      {/* Banner */}
      <Banner />

      <main className="mx-[10px] mb-[82px]">
        {/* Delivery Section */}
        <Card className="mb-[10px] border-none gap-0">
              <h2 className="text-[16px] font-semibold mt-[20px] mb-[10px] leading-[16px]">Delivery</h2>
              <div className="p-[14px] mb-[10px] rounded-md border border-gray-300 leading-[16px]">
                <div className="flex items-center justify-between">
                  <div className="flex-1 h-full items-center border-r border-gray-300">
                    <h3 className="font-semibold text-black mb-[10px] leading-[16px] text-[14px]">Dispatch To:</h3>
                    <div className="text-sm text-black leading-[16px]">
                      <span className="font-semibold">A & F Supplies Ltd</span>, 13 Fylde Road Industrial Estate, Fylde Road, Preston, Lancashire, PR1 2TY
                    </div>
                  </div>
                    <button 
                      onClick={() => setIsDispatchExpanded(!isDispatchExpanded)}
                      className="cursor-pointer rounded !leading-[16px]"
                    >
                      <FontAwesomeIcon 
                        icon={isDispatchExpanded ? faChevronUp : faChevronDown} 
                        className="text-green-600 ml-[10px] px-[8px] border-left border-gray-300 leading-[16px]" 
                        style={{ width: "21px", height: "24px" }} 
                      />
                    </button>
                </div>
                
                {isDispatchExpanded && (
                  <div className="mt-3 pt-3 border-t border-gray-200">
                    <div className="flex items-center mb-2">
                      <div className="w-4 h-4 border-2 border-blue-500 rounded-full mr-3 flex items-center justify-center">
                        <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                      </div>
                      <div className="text-sm text-black">
                        A & F Supplies Ltd, 13 Fylde Road Industrial Estate, Fylde Road, Preston, Lancashire, PR1 2TY
                      </div>
                    </div>
                  </div>
                )}
              </div>
              <div className="p-[14px] mb-[10px] rounded-md border border-gray-300 leading-[16px]">
                <h3 className="text-[14px] mb-[10px] font-semibold leading-[16px]">Delivery Method:</h3>
                <div className="flex items-center border-t border-gray-300 pt-[10px] mt-[10px] leading-[16px]">
                  <div className="w-4 h-4 border-2 border-green-600 rounded-full mr-2 flex items-center justify-center">
                    <div className="w-2 h-2 bg-green-600 rounded-full"></div>
                  </div>
                  <div className="leading-[16px]">
                    <span className="text-[14px] font-semibold leading-[16px]">Next Working Day Delivery</span>
                    <div className="text-[14px] text-green-500 leading-[16px]">Estimated: Tomorrow (£5.00)</div>

                  </div>
                </div>
              </div>
              <FloatingInput
                    label="Delivery Instructions"
                    placeholder="Additional delivery instructions..."
                />
        </Card>

        <h2 className="mt-[12px] mb-[10px] text-[16px] font-semibold leading-[16px]">Order Summary</h2>
        {/* Order Summary Section */}
        <Card className="border-gray-300 mb-[10px] p-[14px] gap-0">
            {/* Order details sub-card */}
            <Card className="border-none mt-[4px]">
                <div className="grid grid-cols-[1fr_120px_80px] grid-rows-[auto_16px_16px] text-right gap-x-[4px] gap-y-[8px]">
                    <span className="text-[14px] font-semibold text-left leading-[16px]">Order details</span>
                    <span className="text-sm text-black leading-[16px]">Units</span>
                    <span className="text-sm text-black leading-[16px]">5</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">SKUs</span>
                    <span className="text-sm text-black leading-[16px]">1</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Subtotal</span>
                    <span className="text-sm text-black leading-[16px]">£23.00</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Credit Awarded</span>
                    <span className="text-sm text-black leading-[16px]">£0.00</span>
                </div>
            </Card>
            <hr className="border-gray-300 my-[20px] leading-[16px]" />
            {/* Delivery sub-card */}
            <Card className="border-none">
                <div className="grid grid-cols-[1fr_200px] text-right gap-x-[4px] gap-y-[4px] leading-[16px]">
                  <span className="text-[14px] font-semibold text-left leading-[16px]">Delivery</span>
                  <span className="text-sm text-black leading-[16px]">Next Working Day Delivery</span>
                  <span className="h-[64px]"></span>
                  <div className="text-sm text-black h-[64px] leading-[16px]">
                    <span className="font-semibold">A & F Supplies Ltd,</span> 13 Fylde Road Industrial Estate, Fylde Road, Preston, Lancashire, PR1 2TY
                  </div>
                </div>
            </Card>
            <hr className="border-gray-300 my-[20px] leading-[16px]" />
            {/* Summary sub-card */}
            <Card className="mb-[8px] border-none">
              <div className="grid grid-cols-[1fr_120px_80px] grid-rows-[auto_16px_16px] text-right gap-x-[4px] gap-y-[8px]">
                  <span className="text-[14px] font-semibold text-left leading-[16px]">Summary</span>
                    <span className="text-sm text-black leading-[16px]">Subtotal</span>
                    <span className="text-sm text-black leading-[16px]">£23.00</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Wallet Discount</span>
                    <span className="text-sm text-black leading-[16px]">-£2.50</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Delivery</span>
                    <span className="text-sm text-black leading-[16px]">£5.00</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">VAT</span>
                    <span className="text-sm text-black leading-[16px]">£5.10</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px] font-semibold">Payment Total</span>
                    <span className="text-sm text-black leading-[16px] font-semibold">£30.60</span>
                </div>
            </Card>
        </Card>

        {/* Continue to Payment Button */}
        <div className="mt-4">
          <button 
            onClick={handleContinueToPayment} 
            disabled={isProcessing}
            className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-4 rounded-lg font-semibold text-lg hover:cursor-pointer transition-colors"
          >
            {isProcessing ? "Processing..." : "Continue to Payment"}
          </button>
        </div>
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
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