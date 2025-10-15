"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package, ChevronRight } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter, faTruck } from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";

interface MobileOrdersProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders") => void;
  onBack: () => void;
  onOpenOrder?: (orderNumber: string) => void;
}

type OrderItem = {
  order_number: string;
  ordered_at: string;
  payment_status: string;
  fulfillment_status: string;
  units: number;
  skus: number;
  total_paid: number;
  currency_symbol: string;
};

export function MobileOrders({ onNavigate, onBack, onOpenOrder }: MobileOrdersProps) {
  const [orders, setOrders] = useState<OrderItem[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchAll = async () => {
      setLoading(true);
      try {
        // Fetch a large number to approximate "all"; could be paginated later
        const res = await api.get("/orders", { params: { limit: 500 } });
        const json = res?.data;
        if (json?.success && Array.isArray(json.orders)) {
          setOrders(json.orders);
        } else {
          setOrders([]);
        }
      } catch {
        setOrders([]);
      } finally {
        setLoading(false);
      }
    };
    fetchAll();
  }, []);

  return (
    <div className="min-h-screen w-full max-w-[1000px] mx-auto">
      {/* Header */}
      {/* <header className="bg-white px-4 py-3 flex items-center gap-3 border-b">
        <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <Package className="w-4 h-4 text-green-600" />
          </div>
          <span className="text-sm text-gray-600">Orders</span>
        </div>
      </header> */}

      <header className="bg-white flex items-center border-b h-[50px]">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faTruck} className="text-green-600" style={{ width: "30px", height: "24px" }} />
        </div>
        <span className="text-[16px] font-semibold">Orders</span>
      </header>

      {/* Banner */}
      <Banner />

      <main className="pb-20">
        {loading ? (
          <div className="p-4">
            <div className="space-y-3">
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
              <div className="h-16 bg-gray-200 rounded animate-pulse" />
            </div>
          </div>
        ) : (
          <div className="mx-4 mt-4">
            {orders.length === 0 ? (
              <div className="text-center text-gray-500 text-sm">No orders found</div>
            ) : (
              orders.map((o, idx) => (
                <Card key={o.order_number + idx} className="mb-3">
                  <div className="p-4 flex">
                    <div className="space-y-2 text-sm w-full pr-4 border-r border-gray-200">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Order No:</span>
                        <span className="text-gray-900">{o.order_number}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Ordered:</span>
                        <span className="text-gray-900">{o.ordered_at}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Payment Status:</span>
                        <span className="text-gray-900">{o.payment_status}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Fulfillment Status:</span>
                        <span className="text-gray-900">{o.fulfillment_status}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Units:</span>
                        <span className="text-gray-900">{o.units}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">SKUs:</span>
                        <span className="text-gray-900">{o.skus}</span>
                      </div>
                      <div className="flex justify-between font-semibold">
                        <span className="text-gray-600">Total Paid:</span>
                        <span className="text-gray-900">
                          {o.currency_symbol}
                          {o.total_paid.toFixed(2)}
                        </span>
                      </div>
                    </div>
                    <ChevronRight
                      onClick={() => onOpenOrder && onOpenOrder(o.order_number)}
                      className="w-6 h-6 text-green-600 self-center ml-2 cursor-pointer"
                    />
                  </div>
                </Card>
              ))
            )}
          </div>
        )}
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
