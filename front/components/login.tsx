"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import api from "@/lib/axios";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";
import { useToast } from "@/hooks/use-toast";
import { useCustomer } from "@/components/customer-provider";

export default function Login() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { settings, refresh: refreshSettings } = useSettings();
  const { toast } = useToast();
  const { refresh } = useCustomer();
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<{ email: string; password: string }>({
    mode: "onSubmit",
    reValidateMode: "onChange",
    defaultValues: { email: "", password: "" },
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const token = typeof window !== "undefined" ? window.localStorage.getItem("auth_token") : null;
    // If redirected due to account deletion, show a clear message
    try {
      const deleted = sessionStorage.getItem("account_deleted");
      if (deleted === "1") {
        sessionStorage.removeItem("account_deleted");
        // Ensure no stale token
        try {
          window.localStorage.removeItem("auth_token");
        } catch {}
        // Show toast
        try {
          const { toast } = require("@/hooks/use-toast");
          toast({ variant: "destructive", title: "Your account has been deleted", description: "Please contact support if you believe this is a mistake." });
        } catch {}
      }
    } catch {}
    if (token) {
      const redirect = searchParams.get("redirect") || "/";
      router.replace(redirect);
    }
  }, [router, searchParams]);

  async function onSubmit(values: { email: string; password: string }) {
    setError(null);
    setLoading(true);
    try {
      const { data } = await api.post("/login", {
        email: values.email,
        password: values.password,
        device_name: "nextjs-web",
      });
      if (data?.success && data?.token) {
        window.localStorage.setItem("auth_token", data.token);
        // Refresh customer and settings after login; providers will cache to sessionStorage
        await refresh();
        try {
          await refreshSettings();
        } catch {}
        try {
          sessionStorage.removeItem("orders_cache");
          sessionStorage.removeItem("products_cache");
        } catch {}
        toast({ title: "Hello there 👋", description: "You've logged in successfully." });
           window.location.replace("/nwb2b/front");
      } else {
        const message = data?.message || "Login failed";
        setError(message);
        toast({ variant: "destructive", title: "Login failed", description: message });
      }
    } catch (err: any) {
      const message = err?.response?.data?.message || "Invalid credentials";
      setError(message);
      toast({ variant: "destructive", title: "Login error", description: message });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="w-full max-w-[1000px] mx-auto pb-50 login-main-wrap">
      {/* Logo */}
      <div className="flex items-center justify-center h-[50px] shadow-[0_6px_6px_-6px_#666]">
        <img className="h-[36px] w-[67.8px] my-[7px]" src={settings?.company_logo_url || "placeholder-logo.png"} alt={settings?.company_title || "Logo"} />
      </div>

      {/* Tabs */}
      <div className="overflow-hidden wrapper-space">
        <div className="grid grid-cols-2 loginRegisterWrapper">
          <button className="bg-green-500 text-white py-3 font-medium">Login</button>
          <Link href="/nwb2b/front/register" className="text-gray-700 bg-gray-100 py-3 text-center font-medium">
            Register
          </Link>
        </div>

        <div className="border-t my-5"></div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} noValidate className="loginregisterform">
          <FloatingInput type="email" label="Email Address" placeholder="Please enter your email address..." {...register("email", { required: "Email is required", pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "Enter a valid email address" } })} error={errors.email?.message} />
          <FloatingInput type="password" label="Password" placeholder="Please enter your password..." {...register("password", { required: "Password is required", minLength: { value: 6, message: "Password must be at least 6 characters" } })} error={errors.password?.message} />
          {error && <p className="text-red-600 text-sm">{error}</p>}

          <div className="border-t my-5"></div>

          <button type="submit" disabled={loading || isSubmitting} className="w-full bg-black text-white rounded py-3 disabled:opacity-60 hover:cursor-pointer">
            {loading ? "Signing in..." : "Login"}
          </button>

          <p className="text-s text-black-600 my-[16px] leading-[16px]">
            By selecting Login, you agree to our{" "}
            <a className="text-blue-600" href="#">
              Terms & Conditions
            </a>{" "}
            and{" "}
            <a className="text-blue-600" href="#">
              Privacy Policy
            </a>
            .
          </p>
        </form>
      </div>
    </div>
  );
}
