import withPWA from 'next-pwa'

/** @type {import('next').NextConfig} */
const isProd = !!process.env.NEXT_PUBLIC_API_URL

const baseConfig = {
  eslint: { 
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  // async rewrites() {
  //   return [
  //     {
  //       source: "/api/:path*",
  //       destination: "http://localhost:8000/api/:path*", // Laravel API
  //     },
  //   ];
  // },
}

const nextConfig = withPWA({
  // Always emit SW into local public folder
  dest: 'public',
  // Enable SW only in production
  disable: !isProd,
  register: true,
  skipWaiting: true,
  // Precache all public assets including nested icons; add prefix in prod
  workboxOptions: {
    globDirectory: 'public',
    globPatterns: [
      '**/*.{js,css,html,ico,png,svg,webp,jpg,jpeg,json,mp3,woff,woff2}'
    ],
    modifyURLPrefix: isProd ? { '': '/nwb2b/front/' } : undefined,
  },
})(baseConfig)

export default nextConfig
