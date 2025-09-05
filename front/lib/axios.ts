import axios from "axios";

const rawBase = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000";
const normalizedBase = rawBase.endsWith("/api") ? rawBase : `${rawBase.replace(/\/$/, "")}/api`;

const api = axios.create({
  baseURL: normalizedBase,
  headers: { "Content-Type": "application/json" },
});

export default api;
