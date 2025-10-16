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
  // Service worker file name and scope without using Next.js basePath
  sw: 'sw.js',
  scope: '/nwb2b/front/',
  // Runtime caching to ensure icons and images under the subpath are cached
  runtimeCaching: [
    {
      urlPattern: /\/nwb2b\/front\/(?:icons\/.*|.*\.(?:png|jpg|jpeg|svg|webp|ico))$/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'images',
        expiration: {
          maxEntries: 200,
          maxAgeSeconds: 60 * 60 * 24 * 30, // 30 days
        },
      },
    },
  ],
})(baseConfig)

export default nextConfig
