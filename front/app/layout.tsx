import type { Metadata } from "next";
import { GeistSans } from "geist/font/sans";
import { GeistMono } from "geist/font/mono";
import "./globals.css";
import "./custom.css";

import { Toaster } from "@/components/ui/toaster";
import FaviconProvider from "@/components/favicon-provider";
import { CurrencyProvider } from "@/components/currency-provider";
import { SettingsProvider } from "@/components/settings-provider";
import { CustomerProvider } from "@/components/customer-provider";
import LoadingProvider from "@/components/loading-provider";
import StoreMaintenanceGate from "@/components/store-maintenance-gate";
import PwaInstall from "@/components/pwa-install";
import SwRegister from "@/components/sw-register";

export const metadata: Metadata = {
  title: "NWB2B",
  description: "",
  generator: "v0.app",
  icons: {},
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <head>
        <style>{`
html {
  font-family: ${GeistSans.style.fontFamily};
  --font-sans: ${GeistSans.variable};
  --font-mono: ${GeistMono.variable};
}
        `}</style>
        <link rel="manifest" href="/manifest.webmanifest" />
        <meta name="theme-color" content="#000000" />
        <link rel="icon" href="/icons/icon-192x192.png" type="image/png" />
        <link rel="apple-touch-icon" href="/icons/icon-192x192.png" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="NWB2B" />
        <meta name="mobile-web-app-capable" content="yes" />
      </head>
      <body>
        {/* Early redirect to login before any paint if no token */}
        <script
          dangerouslySetInnerHTML={{
            __html: `(() => { try { var p = window.location.pathname || '/'; var base = p.startsWith('/nwb2b/front') ? '/nwb2b/front' : '/'; var login = (base.replace(/\/$/, '')) + '/login'; if (!p.startsWith(login)) { var t = localStorage.getItem('auth_token'); if (!t) { var q = new URLSearchParams({ redirect: p }).toString(); var sep = login.indexOf('?') === -1 ? '?' : '&'; window.location.replace(login + sep + q); } } } catch (e) {} })();`,
          }}
        />
        <LoadingProvider>
          <SettingsProvider>
            <StoreMaintenanceGate>
              <CurrencyProvider>
                <CustomerProvider>
                  {children}
                  <Toaster />
                  <FaviconProvider />
                  <PwaInstall />
                  <SwRegister />
                </CustomerProvider>
              </CurrencyProvider>
            </StoreMaintenanceGate>
          </SettingsProvider>
        </LoadingProvider>
      </body>
    </html>
  );
}
