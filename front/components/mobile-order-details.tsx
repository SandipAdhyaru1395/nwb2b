"use client";

import { ArrowLeft, Home, ShoppingBag, User, Wallet, Package } from "lucide-react";
import { Card } from "@/components/ui/card";
import api from "@/lib/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter, faTruck } from "@fortawesome/free-solid-svg-icons";
import { useEffect, useState } from "react";
import { Banner } from "@/components/banner";

interface MobileOrderDetailsProps {
  orderNumber: string;
  onNavigate: (page: "dashboard" | "shop" | "wallet" | "account" | "orders" | "order-details") => void;
  onBack: () => void;
  onReorder: (items: Array<{ product: any; quantity: number }>) => void;
}

type Address = { line1?: string | null; line2?: string | null; city?: string | null; state?: string | null; zip?: string | null; country?: string | null };

type OrderDetails = {
  order_number: string;
  ordered_at: string;
  payment_status: string;
  fulfillment_status: string;
  units: number;
  skus: number;
  subtotal: number;
  vat_amount: number;
  delivery: string;
  wallet_discount: number;
  total_paid: number;
  currency_symbol: string;
  billing_address: Address;
  shipping_address: Address;
  items: Array<{ product_id: number; product_name?: string | null; product_image?: string | null; quantity: number; unit_price: number; wallet_credit_earned: number; total_price: number }>;
};

export function MobileOrderDetails({ orderNumber, onNavigate, onBack, onReorder }: MobileOrderDetailsProps) {
  const [order, setOrder] = useState<OrderDetails | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [reordering, setReordering] = useState<boolean>(false);

  useEffect(() => {
    const fetchDetails = async () => {
      setLoading(true);
      try {
        const res = await api.get(`/orders/${orderNumber}`);
        if (res?.data?.success && res.data.order) {
          setOrder(res.data.order);
        } else {
          setOrder(null);
        }
      } catch {
        setOrder(null);
      } finally {
        setLoading(false);
      }
    };
    fetchDetails();
  }, [orderNumber]);

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
          <span className="text-sm text-gray-600">Orders / Details - {orderNumber}</span>
        </div>
      </header> */}

      <header className="bg-white flex items-center border-b h-[50px]">
        <div className="w-[66px] h-[25px] flex items-center justify-center">
          <FontAwesomeIcon icon={faTruck} className="text-green-600" style={{ width: "30px", height: "24px" }} />
        </div>
        <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">
          Orders
        </span>
        &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
        &nbsp;<span className="text-[16px] font-semibold">Details - {orderNumber}</span>
      </header>

      {/* Banner */}
      <div className="mb-1">
        <Banner />
      </div>

      <main className="pb-24">
        {loading ? (
          <div className="p-[10px] space-y-3">
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
          </div>
        ) : !order ? (
          <div className="p-4 text-center text-gray-500">Order not found</div>
        ) : (
          <>
            {/* Order Summary (exact style with three sections and right-aligned values) */}
            <div className="px-[10px] pt-[10px]">
              <h3 className="font-semibold text-gray-900 mb-2">Order Summary</h3>
              <div className="bg-white border border-gray-300 rounded-md p-[14px]">
                {/* Order details row */}
                <div className="">
                  <div className="flex items-start justify-between py-1">
                    <span className="font-semibold text-sm text-black">Order details</span>
                    <div className="text-sm w-40">
                      <div className="flex justify-between mb-1 text-right">
                        <span className="text-black w-[120px]">Units</span>
                        <span className="text-black w-[80px]">{order.units}</span>
                      </div>
                      <div className="flex justify-between text-right">
                        <span className="text-black w-[120px]">SKUs</span>
                        <span className="text-black w-[80px]">{order.skus}</span>
                      </div>
                    </div>
                  </div>
                </div>
                {/* Delivery row */}
                <div className="border-t pt-5 mt-5">
                  <div className="flex items-start justify-between py-1">
                    <span className="font-semibold text-sm text-black">Delivery</span>
                    <div className="text-right text-sm text-black max-w-[60%]">
                      <div className="">Next Working Day Delivery</div>
                      <div>{order.shipping_address.line1}</div>
                      {order.shipping_address.line2 && <div>{order.shipping_address.line2}</div>}
                      <div>{order.shipping_address.city}</div>
                      <div>{order.shipping_address.state}</div>
                      <div>{order.shipping_address.zip}</div>
                      <div>{order.shipping_address.country}</div>
                    </div>
                  </div>
                </div>
                {/* Summary row */}
                <div className="border-t pt-5 mt-5">
                  <div className="flex items-start justify-between py-1">
                    <span className="font-semibold text-sm text-black">Summary</span>
                    <div className="text-sm w-44">
                      <div className="flex justify-between text-right mb-1">
                        <span className="text-black w-[120px]">Subtotal</span>
                        <span className="text-black w-[80px]">
                          {order.currency_symbol}
                          {order.subtotal.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between text-right mb-1">
                        <span className="text-black w-[120px]">Wallet Discount</span>
                        <span className="text-black w-[80px]">
                          {order.currency_symbol}
                          {order.wallet_discount.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between text-right mb-1">
                        <span className="text-black w-[120px]">Delivery</span>
                        <span className="text-black w-[80px]">{order.delivery}</span>
                      </div>
                      <div className="flex justify-between text-right mb-1">
                        <span className="text-black w-[120px]">VAT (20%)</span>
                        <span className="text-black w-[80px]">
                          {order.currency_symbol}
                          {order.vat_amount.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between font-semibold text-right">
                        <span className="text-black w-[120px]">Payment Total</span>
                        <span className="text-black w-[80px]">
                          {order.currency_symbol}
                          {((order as any).payment_amount ?? Math.max(0, (order.total_paid ?? 0) - ((order as any).wallet_credit_used ?? 0))).toFixed(2)}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Reorder Items CTA */}
              <div className="py-[10px]">
                <button
                  disabled={reordering}
                  onClick={async () => {
                    if (!orderNumber) return;
                    try {
                      setReordering(true);
                      const res = await api.post(`/orders/${orderNumber}/reorder`);
                      const items = Array.isArray(res?.data?.items) ? res.data.items : [];
                      const mapped = items.map((it: any) => ({ product: it.product, quantity: it.quantity }));
                      onReorder(mapped);
                    } catch (e) {
                      // noop; optionally show a toast in future
                    } finally {
                      setReordering(false);
                    }
                  }}
                  className="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold text-lg py-[13px] rounded-md box-shadow-bottom"
                >
                  {reordering ? "Reordering…" : "Reorder Items"}
                </button>
              </div>
            </div>

            {/* Addresses removed per request */}

            {/* Order Lines */}
            <div className="px-[10px]">
              <h3 className="font-semibold text-gray-900 mb-[10px] mt-[20px] line-16">Order Lines</h3>
              <div className="mt-[10px]">
                {order.items.map((it, idx) => (
                  <div key={it.product_id + "-" + idx} className="py-[16px] border-t last:border-y">
                    <div className="flex">
                      {it.product_image ? <img src={it.product_image} alt="" className="w-[50px] h-[50px] mr-[10px] object-cover" /> : <div className="w-[50px] h-[50px] mr-[10px] bg-gray-100" />}
                      <div className="font-medium text-gray-900 text-[16px] leading-[16px] flex-5">{it.product_name || `Product #${it.product_id}`}</div>
                      <div className="text-gray-800 text-[16px] leading-[16px] flex-1 text-center">{it.quantity}</div>
                      <div className="font-semibold text-right text-[16px] leading-[16px]">
                        {order.currency_symbol}
                        {it.total_price.toFixed(2)}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </>
        )}
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 box-shadow-top px-[18px]">
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
