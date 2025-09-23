import type { Metadata } from 'next'
import { GeistSans } from 'geist/font/sans'
import { GeistMono } from 'geist/font/mono'
import './globals.css'
import { Toaster } from '@/components/ui/toaster'
import FaviconProvider from '@/components/favicon-provider'
import { CurrencyProvider } from '@/components/currency-provider'

export const metadata: Metadata = {
  title: 'NWB2B',
  description: '',
  generator: 'v0.app',
  icons: {},
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
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
        <link rel="icon" href="/placeholder-logo.png" type="image/png" />
        <link rel="apple-touch-icon" href="/placeholder-logo.png" />
      </head>
      <body>
          <CurrencyProvider>
            {children}
            <Toaster />
            <FaviconProvider />
          </CurrencyProvider>
      </body>
    </html>
  )
}
