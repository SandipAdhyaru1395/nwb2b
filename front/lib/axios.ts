import axios from "axios";

const rawBase = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000";
const normalizedBase = rawBase.endsWith("/api") ? rawBase : `${rawBase.replace(/\/$/, "")}/api`;

const api = axios.create({
  baseURL: normalizedBase,
  headers: { "Content-Type": "application/json", "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
});

// Attach token from localStorage on browser
if (typeof window !== "undefined") {
  api.interceptors.request.use((config) => {
    const token = window.localStorage.getItem("auth_token");
    if (token) {
      config.headers = config.headers || {};
      (config.headers as any)["Authorization"] = `Bearer ${token}`;
    }
    return config;
  });

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      const status = error?.response?.status;
      if (status === 401 || status === 403) {
        // Clear any potentially stale auth token
        try { window.localStorage.removeItem("auth_token"); } catch {}

        // Avoid redirect loops if we're already on the auth pages
        const currentPath = typeof window !== "undefined" ? window.location.pathname : "";
        const isOnAuthPage = currentPath.startsWith("/nwb2b/front/login") || currentPath.startsWith("/nwb2b/front/register") || currentPath.startsWith("/login") || currentPath.startsWith("/register");
        if (!isOnAuthPage) {
          // Redirect to login
          try {
            window.location.assign("/nwb2b/front/login");
          } catch {
            // Fallback
            window.location.href = "/nwb2b/front/login";
          }
        }
      }
      return Promise.reject(error);
    }
  );
}

export default api;
