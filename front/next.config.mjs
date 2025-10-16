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
  // Ensure Next.js serves assets under the deployed subpath
  ...(isProd ? { basePath: '/nwb2b/front', assetPrefix: '/nwb2b/front' } : {}),
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
  // Always generate the service worker into the local public folder
  dest: 'public',
  // Disable SW in local dev only
  disable: !isProd,
  register: true,
  skipWaiting: true,
})(baseConfig)

export default nextConfig
