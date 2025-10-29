"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package, ChevronRight, ChevronDown } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter, faTruck, faChevronUp, faChevronDown, faCheck } from "@fortawesome/free-solid-svg-icons";
import { useEffect, useMemo, useState } from "react";
import { Banner } from "@/components/banner";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";
import FloatingInput from "./ui/floating-input";
import { useCurrency } from "@/components/currency-provider";

interface Branch {
  id: number;
  name: string;
  address_line1: string;
  address_line2: string;
  city: string;
  zip_code: string;
  country: string;
}

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
  const [branches, setBranches] = useState<Branch[]>([]);
  const [selectedBranch, setSelectedBranch] = useState<Branch | null>(null);
  const [deliveryInstructions, setDeliveryInstructions] = useState("");
  const { toast } = useToast();
  const { refresh, customer } = useCustomer();
  const { format, symbol } = useCurrency();

  // Fetch branches on component mount
  const fetchBranches = async () => {
    try {
      const response = await api.get("/branches");
      if (response.data.success) {
        setBranches(response.data.branches);
        // Select the first branch (ignore is_default for checkout selection)
        if (Array.isArray(response.data.branches) && response.data.branches.length > 0) {
          setSelectedBranch(response.data.branches[0]);
        } else {
          setSelectedBranch(null);
        }
      }
    } catch (error) {
      console.error("Error fetching branches:", error);
    }
  };

  useEffect(() => {
    fetchBranches();
  }, []);

  // Calculate total wallet credit from cart items (Credit Awarded)
  const totalWalletCredit = useMemo(() => {
    return Object.values(cart).reduce((sum, { product, quantity }) => {
      const credit = typeof product.wallet_credit === "number" ? product.wallet_credit : 0;
      return sum + credit * quantity;
    }, 0);
  }, [cart]);

  // Calculate wallet discount (assuming 10% of subtotal as example)
  const walletDiscount = customer?.wallet_balance ? Math.min(customer.wallet_balance, totals.subtotal) : 0;

  const handleContinueToPayment = async () => {
    if (!selectedBranch) {
      toast({ title: "Select an branch", description: "Please choose a branch before continuing.", variant: "destructive" });
      return;
    }
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
        address_id: selectedBranch?.id,
        delivery_instructions: deliveryInstructions,
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
                      {selectedBranch ? (
                        <>
                          <span className="font-semibold">{selectedBranch.name}</span>, {selectedBranch.address_line1}
                          {selectedBranch.address_line2 && `, ${selectedBranch.address_line2}`}, {selectedBranch.city}, {selectedBranch.country}, {selectedBranch.zip_code}
                        </>
                      ) : (
                        <span className="text-gray-500">No branch selected</span>
                      )}
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
                    {branches.map((branch) => (
                      <div key={branch.id} className="flex items-center mb-2">
                        <input
                          type="radio"
                          id={`branch-${branch.id}`}
                          name="selectedBranch"
                          value={branch.id}
                          checked={selectedBranch?.id === branch.id}
                          onChange={() => setSelectedBranch(branch)}
                          className="w-4 h-4 border-2 border-blue-500 rounded-full mr-3"
                        />
                        <label htmlFor={`branch-${branch.id}`} className="text-sm text-black cursor-pointer flex-1">
                          <span className="font-semibold">{branch.name}</span>, {branch.address_line1}
                          {branch.address_line2 && `, ${branch.address_line2}`}, {branch.city}, {branch.country}, {branch.zip_code}
                        </label>
                      </div>
                    ))}
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
                    value={deliveryInstructions}
                    onChange={(e) => setDeliveryInstructions(e.target.value)}
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
                    <span className="text-sm text-black leading-[16px]">{totals.units}</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">SKUs</span>
                    <span className="text-sm text-black leading-[16px]">{totals.skus}</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Subtotal</span>
                    <span className="text-sm text-black leading-[16px]">{format(totals.subtotal)}</span>
                    <span className="text-[14px] font-semibold leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Credit Awarded</span>
                    <span className="text-sm text-black leading-[16px]">{format(totalWalletCredit)}</span>
                </div>
            </Card>
            <hr className="border-gray-300 my-[20px] leading-[16px]" />
            {/* Delivery sub-card */}
            <Card className="border-none">
                <div className="grid grid-cols-[1fr_200px] text-right gap-x-[4px] gap-y-[4px] leading-[16px]">
                  <span className="text-[14px] font-semibold text-left leading-[16px]">Delivery</span>
                  <span className="text-sm text-black leading-[16px]">Next Working Day Delivery</span>
                  <span className="min-h-[64px]"></span>
                  <div className="text-sm text-black min-h-[64px] leading-[16px]">
                    {selectedBranch ? (
                      <>
                        <span className="font-semibold">{selectedBranch.name},</span> {selectedBranch.address_line1}
                        {selectedBranch.address_line2 && `, ${selectedBranch.address_line2}`}, {selectedBranch.city}, {selectedBranch.country}, {selectedBranch.zip_code}
                      </>
                    ) : (
                      <span className="text-gray-500">No branch selected</span>
                    )}
                  </div>
                </div>
            </Card>
            <hr className="border-gray-300 my-[20px] leading-[16px]" />
            {/* Summary sub-card */}
            <Card className="mb-[8px] border-none">
              <div className="grid grid-cols-[1fr_120px_80px] grid-rows-[auto_16px_16px] text-right gap-x-[4px] gap-y-[8px]">
                  <span className="text-[14px] font-semibold text-left leading-[16px]">Summary</span>
                    <span className="text-sm text-black leading-[16px]">Subtotal</span>
                    <span className="text-sm text-black leading-[16px]">{format(totals.subtotal)}</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Wallet Discount</span>
                    <span className="text-sm text-black leading-[16px]">-{format(walletDiscount)}</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">Delivery</span>
                    <span className="text-sm text-black leading-[16px]">£5.00</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px]">VAT</span>
                    <span className="text-sm text-black leading-[16px]">£5.10</span>
                    <span className="text-sm text-black leading-[16px]"></span>
                    <span className="text-sm text-black leading-[16px] font-semibold">Payment Total</span>
                    <span className="text-sm text-black leading-[16px] font-semibold">{format(totals.subtotal - walletDiscount + 5.00 + 5.10)}</span>
                </div>
            </Card>
        </Card>

        {/* Continue to Payment Button */}
        <div className="mt-4">
          <button 
            onClick={handleContinueToPayment} 
            disabled={isProcessing || !selectedBranch}
            className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-4 rounded-lg font-semibold text-lg hover:cursor-pointer disabled:cursor-not-allowed transition-colors"
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