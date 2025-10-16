"use client";

import React from "react";

export default function SwRegister() {
  React.useEffect(() => {
    // console.log(process.env.NEXT_PUBLIC_API_URL);
    if (!process.env.NEXT_PUBLIC_API_URL) return;
    if (!("serviceWorker" in navigator)) return;
    const register = async () => {
      try {
        const registration = await navigator.serviceWorker.register("/sw.js");
        // Ensure the SW controls the page ASAP
        if (!navigator.serviceWorker.controller) {
          await navigator.serviceWorker.ready;
        }
      } catch {
        // noop
      }
    };
    register();
  }, []);
  return null;
}



