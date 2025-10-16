/**
 * Versioned SW to avoid old Workbox precache issues and improve navigation caching.
 */

// Use the same AMD/define pattern as the existing bundled SW file.
define(['./workbox-e43f5367'], (function (workbox) { 'use strict';

  // Ensure the new worker takes control ASAP
  self.skipWaiting();
  workbox.clientsClaim();

  // Clean up any old Workbox precache caches from previous SWs
  self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
      const cacheNames = await caches.keys();
      await Promise.all(
        cacheNames.map((name) => {
          // Typical patterns: 'workbox-precache-v2-<scope>'
          if (name.startsWith('workbox-precache') || name.includes('precache-v2')) {
            return caches.delete(name);
          }
        })
      );
    })());
  });

  // Helper plugin to treat opaqueredirect as OK (useful when frameworks redirect start URL)
  const okOnOpaqueRedirectPlugin = {
    cacheWillUpdate: async ({ response }) => {
      if (response && response.type === 'opaqueredirect') {
        return new Response(response.body, {
          status: 200,
          statusText: 'OK',
          headers: response.headers,
        });
      }
      return response;
    },
  };

  // Prefer network for navigations, fall back to cache
  workbox.registerRoute(
    ({ request }) => request.mode === 'navigate',
    new workbox.NetworkFirst({
      cacheName: 'start-url',
      plugins: [okOnOpaqueRedirectPlugin],
    }),
    'GET'
  );

  // Default: network only for all other requests in dev-style config
  workbox.registerRoute(
    /.*/i,
    new workbox.NetworkOnly({ cacheName: 'dev', plugins: [] }),
    'GET'
  );

}));


