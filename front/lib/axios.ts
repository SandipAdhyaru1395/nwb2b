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
        try {
          window.localStorage.removeItem("auth_token");
        } catch {}
        if (typeof window !== "undefined") {
          const current = window.location.pathname;
          if (!current.includes("/login")) {
            const search = new URLSearchParams({ redirect: current }).toString();
            window.location.href = `login?${search}`;
          }
        }
      }
      return Promise.reject(error);
    }
  );
}

export default api;
