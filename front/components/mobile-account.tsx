"use client";

import { Button } from "@/components/ui/button";
import { Banner } from "@/components/banner";
import { ChevronRight, User, Building, GitBranch, Lightbulb, BarChart3, FileText, Bell, Shield, Home, ShoppingBag, Wallet, LogOut } from "lucide-react";
import { useRouter } from "next/navigation";
import api from "@/lib/axios";

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
}

interface MobileAccountProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account" | "rep-details" | "company-details") => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  clearCart: () => void;
}

export function MobileAccount({ onNavigate }: MobileAccountProps) {
  const router = useRouter();

  const handleLogout = async () => {
    try {
      try {
        await api.post("/logout");
      } catch { }
      try {
        window.localStorage.removeItem("auth_token");
      } catch { }
    } finally {
      try {
        router.replace("/nwb2b/front/login");
      } catch {
        if (typeof window !== "undefined") {
          window.location.href = "/login";
        }
      }
    }
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
      {/* Header */}
      <div className="h-[50px] bg-white flex items-center">
        <div className="w-[66px] h-8 rounded-full flex items-center justify-center">
          <User className="w-[24px] h-[24x] text-green-600" />
        </div>
        <h1 className="text-lg font-semibold text-black-600 text-[16px]">Account</h1>
      </div>

      {/* Banner */}
      <Banner />

      {/* Account Menu Items */}
      <div className="p-[10px] mb-[80px]">
        {/* Account Details Section */}
        <Button variant="outline" onClick={() => onNavigate("rep-details")} className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <User className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Rep Details</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        <Button variant="outline" onClick={() => onNavigate("company-details")} className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <Building className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Company</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        <Button variant="outline" className="h-[42px] w-full p-[12px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <GitBranch className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">My Branches</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        <hr className="mt-[20px] mb-[20px]"></hr>

        {/* Utilities Section */}
        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <Lightbulb className="w-4 h-4 text-yellow-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Revo Utilities - guaranteed to reduce your bills!</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        {/* Services Section */}
        <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <BarChart3 className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Services & Display Solutions</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        <Button variant="outline" className="h-[42px] w-full p-[12px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
          <div className="flex items-center">
            <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
              <FileText className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-[14px] font-semibold ml-[10px]">Contracts</span>
          </div>
          <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
        </Button>

        <hr className="mt-[20px] mb-[20px]"></hr>

        {/* Settings Section */}
        
          <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
            <div className="flex items-center">
              <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
                <Bell className="w-4 h-4 text-green-600" />
              </div>
              <span className="text-[14px] font-semibold ml-[10px]">My Notification Preferences</span>
            </div>
            <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
          </Button>

          <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer">
            <div className="flex items-center">
              <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
                <Shield className="w-4 h-4 text-green-600" />
              </div>
              <span className="text-[14px] font-semibold ml-[10px]">My Authentication Settings</span>
            </div>
            <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
          </Button>

          <Button variant="outline" className="h-[42px] w-full p-[12px] mb-[10px] justify-between text-left border border-green-600 hover:bg-gray-50 bg-transparent hover:cursor-pointer" onClick={handleLogout}>
            <div className="flex items-center">
              <div className="w-[16px] h-[16px] bg-green-100 rounded-full flex items-center justify-center">
                <LogOut className="w-4 h-4 text-green-600" />
              </div>
              <span className="text-[14px] font-semibold ml-[10px]">Logout</span>
            </div>
            <ChevronRight width={12.5} height={16} strokeWidth={1.5} className="scale-200 text-green-600" />
          </Button>
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t footer-nav">
        <div className="grid grid-cols-4 py-3 footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-gray-400 hover:text-green-600 hover:cursor-pointer">
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
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-green-600 hover:text-green-600 hover:cursor-pointer">
            <User className="w-7 h-7 mb-1" />
            <span className="text-xs">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
