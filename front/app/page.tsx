"use client"

import { useState } from "react"
import { MobileDashboard } from "@/components/mobile-dashboard"
import { MobileShop } from "@/components/mobile-shop"
import { MobileWallet } from "@/components/mobile-wallet"
import { MobileAccount } from "@/components/mobile-account"

export default function Home() {
  const [currentPage, setCurrentPage] = useState<"dashboard" | "shop" | "wallet" | "account">("dashboard")

  if (currentPage === "shop") {
    return <MobileShop onNavigate={setCurrentPage} />
  }

  if (currentPage === "wallet") {
    return <MobileWallet onNavigate={setCurrentPage} />
  }

  if (currentPage === "account") {
    return <MobileAccount onNavigate={setCurrentPage} />
  }

  return <MobileDashboard onNavigate={setCurrentPage} />
}
