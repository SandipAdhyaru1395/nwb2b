"use client";

import { ArrowLeft, User, Home, ShoppingBag, Wallet } from "lucide-react";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";

interface MobileRepDetailsProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void;
  onBack: () => void;
}

export function MobileRepDetails({ onNavigate, onBack }: MobileRepDetailsProps) {
  const { customer } = useCustomer();

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto pb-30 min-h-screen">
      {/* Header */}
      <div className="bg-white p-4 flex items-center gap-3 border-b">
        <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <User className="w-4 h-4 text-green-600" />
          </div>
          <span className="text-sm text-gray-600">Account / My Rep Details</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Rep Details Section */}
      <div className="bg-white mx-4 mt-4 rounded-lg border border-gray-200">
        {/* Header with Avatar and Name */}
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
              <span className="text-white font-bold text-lg">{getInitials(customer?.name || "RE")}</span>
            </div>
            <div>
              <h2 className="text-xl font-bold text-gray-900">{customer?.name || "Your Representative"}</h2>
            </div>
          </div>
        </div>

        {/* Contact Information */}
        <div className="p-4 space-y-6">
          {/* Phone Number */}
          <div className="space-y-2">
            <label className="text-sm font-medium text-gray-700">Phone Number</label>
            <p className="text-lg font-medium text-green-600">{customer?.phone || "Contact your representative"}</p>
          </div>

          {/* Email */}
          <div className="space-y-2">
            <label className="text-sm font-medium text-gray-700">Email</label>
            <p className="text-lg font-medium text-green-600">{customer?.email || "rep@company.com"}</p>
          </div>
        </div>
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50">
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
