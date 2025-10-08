"use client";
import { Button } from "@/components/ui/button";
import { useCurrency } from "@/components/currency-provider";
import React, { useEffect, useState } from "react";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ShoppingBag, Heart, Home, Wallet, User, ChevronRight, Bell, Gift, Package, CheckCircle, House } from "lucide-react";
import api from "@/lib/axios";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";

interface MobileDashboardProps {
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders", favorites?: boolean) => void;
  onOpenOrder?: (orderNumber: string) => void;
}

export function MobileDashboard({ onNavigate, onOpenOrder }: MobileDashboardProps) {
  const { symbol } = useCurrency();
  const { customer } = useCustomer();
  const wallet = Number(customer?.wallet_balance || 0);
  const [orders, setOrders] = useState<Array<{ order_number: string; ordered_at: string; payment_status: string; fulfillment_status: string; units: number; skus: number; total_paid: number; currency_symbol: string }>>([]);

  useEffect(() => {
    let isMounted = true;
    const fetchOrders = async () => {
      try {
        if (typeof window !== "undefined") {
          window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-start" } }));
        }
        const res = await api.get("/orders");
        const json = res.data;
        if (!isMounted) return;
        if (json?.success && Array.isArray(json.orders)) {
          setOrders(json.orders);
          try {
            sessionStorage.setItem("orders_cache", JSON.stringify({ at: Date.now(), orders: json.orders }));
          } catch {}
        }
      } catch (e) {
        // ignore
      } finally {
        if (typeof window !== "undefined") {
          window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-stop" } }));
        }
        try {
          sessionStorage.removeItem("orders_needs_refresh");
        } catch {}
      }
    };

    // 1) If a refresh is requested (e.g., after checkout), fetch now
    let refreshed = false;
    try {
      const needs = sessionStorage.getItem("orders_needs_refresh");
      if (needs === "1") {
        refreshed = true;
        fetchOrders();
      }
    } catch {}

    // 2) Otherwise, use cache if present; if not, fetch once (first login)
    if (!refreshed) {
      try {
        const raw = sessionStorage.getItem("orders_cache");
        if (raw) {
          const parsed = JSON.parse(raw);
          if (Array.isArray(parsed?.orders)) {
            setOrders(parsed.orders);
          } else {
            fetchOrders();
          }
        } else {
          fetchOrders();
        }
      } catch {
        fetchOrders();
      }
    }

    // 3) Listen for explicit refresh events (e.g., after checkout while dashboard is mounted)
    const onRefresh = () => fetchOrders();
    if (typeof window !== "undefined") {
      window.addEventListener("orders-refresh", onRefresh);
    }

    return () => {
      isMounted = false;
      if (typeof window !== "undefined") {
        window.removeEventListener("orders-refresh", onRefresh);
      }
    };
  }, []);

  // Wallet balance now comes from CustomerProvider

  return (
    <div className="min-h-screen w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <header className="bg-white flex items-center justify-between inner-header-shadow">
        <div className="flex items-center">
          <div className="mx-5 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
            <House className="w-4 h-4 text-white" />
          </div>
          <h1 className="font-semibold text-gray-900">Dashboard</h1>
        </div>
      </header>

      {/* Main Content */}
      <main className="pb-20">
        {/* Banner */}
        <div className="mt-0">
          <Banner />
        </div>

        <div className="quickLinksWrapper bg-gray-100 p-3 my-3">
          {/* Referral Rewards */}
          <Card className="bg-green-500 border-0 text-white mb-3 rounded-s">
            <div className="p-3 flex items-center justify-between">
              <div>
                <h3 className="font-semibold text-l">Referral Rewards</h3>
                <p className="text-sm mt-1 text-black font-semibold">Refer a Retailer to earn Rewards</p>
              </div>
              <Gift className="w-8 h-8" />
            </div>
          </Card>

          {/* Wallet Credit */}

          <Card className="hover:bg-green-100 hover:cursor-pointer mb-3">
            <div className="p-2 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <Wallet className="w-4 h-4 text-green-600" />
                </div>
                <span className="font-semibold text-sm">
                  {symbol}
                  {wallet.toFixed(2)} credit in your wallet
                </span>
              </div>
              <ChevronRight className="w-6 h-6 text-gray-400" />
            </div>
          </Card>
          {/* Action Buttons */}
          <div className="grid grid-cols-2 gap-3">
            <Button onClick={() => onNavigate("shop")} className="bg-green-500 hover:bg-green-600 hover:cursor-pointer text-white h-12 rounded-lg">
              <ShoppingBag className="w-5 h-5 mr-2" />
              Shop
            </Button>
            <Button onClick={() => onNavigate("shop", true)} className="bg-green-500 hover:bg-green-600 hover:cursor-pointer text-white h-12 rounded-lg">
              <Heart className="w-5 h-5 mr-2" />
              Favourites
            </Button>
          </div>
        </div>

        {/* Recent Notifications */}
        <div className="mx-4 mt-6">
          <h3 className="font-semibold text-gray-900 mb-3 text-sm">Recent Notifications</h3>

          <Card className="mb-3 hover:bg-green-100 hover:cursor-pointer">
            <div className="p-2 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <Bell className="w-4 h-4 text-green-600" />
                </div>
                <span className="text-sm font-semibold">You've left products in your basket</span>
              </div>
              <ChevronRight className="w-6 h-6 text-gray-400" />
            </div>
          </Card>

          <Card className="hover:bg-green-100 hover:cursor-pointer">
            <div className="p-2 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <Bell className="w-4 h-4 text-green-600" />
                </div>
                <span className="text-sm font-semibold">Lost Mary Nera 30K ONE DAY PROMOTION!</span>
              </div>
              <ChevronRight className="w-6 h-6 text-gray-400" />
            </div>
          </Card>
        </div>

        {/* Recent Orders */}
        {orders.length > 0 && (
          <div className="mx-4 mt-6">
            <h3 className="font-semibold text-gray-900 mb-3">Recent Orders</h3>

            {orders.map((o, idx) => (
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
                      <div className="flex items-center gap-1">
                        <span className="text-gray-900">{o.fulfillment_status}</span>
                      </div>
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
                  <ChevronRight onClick={() => onOpenOrder && onOpenOrder(o.order_number)} className="w-6 h-6 text-green-600 self-center ml-2 cursor-pointer" />
                </div>
              </Card>
            ))}
            {/** View All Orders button if more than 10 orders exist (API exposes has_more) **/}
            <div className="mt-2">
              <Button
                onClick={async () => {
                  try {
                    // const res = await api.get('/orders')
                    // if (res?.data?.has_more) {
                    onNavigate("orders");
                    // }
                  } catch {
                    // ignore
                  }
                }}
                className="w-full bg-white border text-green-600 hover:bg-green-50 hover:cursor-pointer"
              >
                View All Orders
              </Button>
            </div>
          </div>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t footer-nav">
        <div className="grid grid-cols-4 py-3 footer-nav-col">
          <button className="flex flex-col items-center text-green-600  hover:cursor-pointer">
            <Home className="w-7 h-7 mb-1" />
            <span className="text-xs">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <ShoppingBag className="w-7 h-7 mb-1" />
            <span className="text-xs">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Wallet className="w-7 h-7 mb-1" />
            <span className="text-xs">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <User className="w-7 h-7 mb-1" />
            <span className="text-xs">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
