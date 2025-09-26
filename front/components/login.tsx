"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import Link from "next/link";
import api from "@/lib/axios";
import { useSettings } from "@/components/settings-provider";
import FloatingInput from "@/components/ui/floating-input";

export default function Login() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const { settings } = useSettings();
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<{email: string; password: string}>({
        mode: 'onSubmit',
        reValidateMode: 'onChange',
        defaultValues: { email: '', password: '' }
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const token = typeof window !== "undefined" ? window.localStorage.getItem("auth_token") : null;
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
                const redirect = searchParams.get("redirect") || "/";
                router.replace(redirect);
            } else {
                setError(data?.message || "Login failed");
            }
        } catch (err: any) {
            const message = err?.response?.data?.message || "Invalid credentials";
            setError(message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="w-full max-w-[1000px] mx-auto">
            {/* Logo */}
            <div className="flex justify-center py-6">
                <img width={67.8} height={36} src={settings?.company_logo_url || "/placeholder-logo.png"} alt={settings?.company_title || "Logo"} className="h-10" />
            </div>

            {/* Tabs */}
            <div className="mx-4 border rounded overflow-hidden">
                <div className="grid grid-cols-2">
                    <button className="bg-green-500 text-white py-3 font-medium">Login</button>
                    <Link href="/register" className="text-gray-700 bg-gray-100 py-3 text-center font-medium">Register</Link>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit(onSubmit)} noValidate className="p-4 space-y-4">
                    <FloatingInput
                        type="email"
                        label="Email"
                        placeholder="Please enter your email address..."
                        {...register('email', { required: 'Email is required', pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Enter a valid email address' } })}
                        error={errors.email?.message}
                        
                      />
                    <FloatingInput
                        type="password"
                        label="Password"
                        placeholder="Please enter your password..."
                        {...register('password', { required: 'Password is required', minLength: { value: 6, message: 'Password must be at least 6 characters' } })}
                        error={errors.password?.message}
                        
                      />
                    {error && <p className="text-red-600 text-sm">{error}</p>}

                    <button
                        type="submit"
                        disabled={loading || isSubmitting}
                        className="w-full bg-black text-white rounded py-3 disabled:opacity-60 hover:cursor-pointer"
                    >
                        {loading ? "Signing in..." : "Login"}
                    </button>

                    <p className="text-xs text-gray-600">By selecting Login, you agree to our <a className="text-blue-600 underline" href="#">Terms & Conditions</a> and <a className="text-blue-600 underline" href="#">Privacy Policy</a>.</p>
                </form>
            </div>
        </div>
    );
}


