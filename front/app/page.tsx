"use client"

import { useState } from "react"
import { MobileDashboard } from "@/components/mobile-dashboard"
import { MobileShop } from "@/components/mobile-shop"

export default function Home() {
  const [currentPage, setCurrentPage] = useState<"dashboard" | "shop">("dashboard")

  if (currentPage === "shop") {
    return <MobileShop onNavigate={setCurrentPage} />
  }

  return <MobileDashboard onNavigate={setCurrentPage} />
}
