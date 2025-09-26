"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import api from "@/lib/axios";
import FloatingInput from "@/components/ui/floating-input";
import { useForm } from "react-hook-form";
import { useSettings } from "@/components/settings-provider";

export default function Register() {
  const router = useRouter();
  const { settings } = useSettings();
  const { register, handleSubmit, watch, setError: setFormError, formState: { errors, isSubmitting } } = useForm({
    mode: 'onSubmit',
    reValidateMode: 'onChange',
    defaultValues: {
      company: '', invoice1: '', invoice2: '', city: '', county: '', postcode: '',
      mobile: '', email: '', password: '', password2: '', repCode: ''
    }
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  async function onSubmit(values: any) {
    setError(null);
    setSuccess(null);
    setLoading(true);
    try {
      const { data } = await api.post("/register", {
        company: values.company,
        name: values.company, // simple mapping if no separate name on form
        invoice_address_line1: values.invoice1,
        invoice_address_line2: values.invoice2,
        invoice_city: values.city,
        invoice_county: values.county,
        invoice_postcode: values.postcode,
        mobile: values.mobile,
        email: values.email,
        password: values.password,
        password_confirmation: values.password2,
        rep_code: values.repCode,
      });
      if (data?.success) {
        setSuccess("Registration submitted successfully. You can now log in.");
        setTimeout(() => router.replace("/login"), 1200);
      } else {
        setError(data?.message || "Registration failed");
      }
    } catch (err: any) {
      const resp = err?.response?.data;
      if (resp?.errors) {
        Object.entries(resp.errors).forEach(([field, msgs]: any) => {
          const msg = Array.isArray(msgs) ? msgs[0] : String(msgs)
          // map backend fields to our form fields
          const map: Record<string, string> = {
            name: 'company',
            email: 'email',
            password: 'password',
            mobile: 'mobile',
            'invoice_address_line1': 'invoice1',
            'invoice_address_line2': 'invoice2',
            'invoice_city': 'city',
            'invoice_county': 'county',
            'invoice_postcode': 'postcode',
          }
          const target = map[field] || field
          // @ts-ignore
          setFormError(target as any, { type: 'server', message: msg })
        })
      }
      const message = resp?.message || "Registration failed";
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

      <div className="mx-4 border rounded overflow-hidden">
        <div className="grid grid-cols-2">
          <Link href="/login" className="text-gray-700 bg-gray-100 py-3 text-center font-medium">Login</Link>
          <button className="bg-green-500 text-white py-3 font-medium">Register</button>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="p-4 space-y-4">
          {/* Company */}
          <FloatingInput label="Company name" placeholder="Please enter your company name..." {...register('company', { required: 'Company name is required' })} error={(errors as any).company?.message as string} />

          {/* Invoice Address */}
          <div>
            <p className="font-medium mb-2">Invoice Address</p>
            <div className="space-y-3">
              <FloatingInput label="Address line 1" placeholder="Please enter invoice address line 1..." {...register('invoice1', { required: 'Address line 1 is required' })} error={(errors as any).invoice1?.message as string} />
              <FloatingInput label="Address line 2" placeholder="Please enter invoice address line 2..." {...register('invoice2')} />
              <FloatingInput label="City" placeholder="Please enter invoice address city..." {...register('city', { required: 'City is required' })} error={(errors as any).city?.message as string} />
              <FloatingInput label="County" placeholder="Please enter invoice address county..." {...register('county', { required: 'County is required' })} error={(errors as any).county?.message as string} />
              <FloatingInput label="Postcode" placeholder="Please enter invoice address postcode..." {...register('postcode', { required: 'Postcode is required' })} error={(errors as any).postcode?.message as string} />
            </div>
          </div>

          {/* Mobile */}
          <div>
            <p className="font-medium mb-2">Mobile Number</p>
            <FloatingInput label="Mobile number" placeholder="Please enter your mobile phone number..." {...register('mobile', { required: 'Mobile number is required' })} error={(errors as any).mobile?.message as string} />
          </div>

          {/* Login Details */}
          <div>
            <p className="font-medium mb-2">Login Details</p>
            <div className="space-y-3">
              <FloatingInput type="email" label="Email" placeholder="Please enter your email address..." {...register('email', { required: 'Email is required', pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Enter a valid email address' } })} error={(errors as any).email?.message as string} />
              <FloatingInput type="password" label="Password" placeholder="Please enter your password..." {...register('password', { required: 'Password is required', minLength: { value: 6, message: 'Password must be at least 6 characters' } })} error={(errors as any).password?.message as string} />
              <FloatingInput type="password" label="Confirm password" placeholder="Please re-enter your password..." {...register('password2', { required: 'Confirm your password', validate: (v) => v === watch('password') || "Passwords don't match" })} error={(errors as any).password2?.message as string} />
            </div>
          </div>

          {/* Rep Code */}
          <FloatingInput label="Rep code" placeholder="Please enter rep code..." {...register('repCode')} />

          {error && <p className="text-red-600 text-sm">{error}</p>}
          {success && <p className="text-green-700 text-sm">{success}</p>}

          <button type="submit" disabled={loading || isSubmitting} className="w-full bg-black text-white rounded py-3 disabled:opacity-60 hover:cursor-pointer">{loading ? "Registering..." : "Register"}</button>
        </form>
      </div>
    </div>
  )
}


