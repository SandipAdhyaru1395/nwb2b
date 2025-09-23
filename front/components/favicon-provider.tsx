"use client"

import { useEffect } from "react"
import api from "@/lib/axios"
import { setFavicon } from "@/lib/utils"

export default function FaviconProvider() {
  useEffect(() => {
    const run = async () => {
      try {
        const res = await api.get('/settings')
        const url = res?.data?.settings?.company_logo_url
        if (typeof url === 'string' && url.length > 0) {
          setFavicon(url)
        }
      } catch (_) {
        // ignore
      }
    }
    run()
  }, [])
  return null
}


