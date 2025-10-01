"use client"

import { ArrowLeft, User, Home, ShoppingBag, Wallet, Building, Check } from "lucide-react"
import { useCustomer } from "@/components/customer-provider"
import { Banner } from "@/components/banner"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { useToast } from "@/hooks/use-toast"
import { useState, useEffect } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import api from "@/lib/axios"

// Validation schema
const companyDetailsSchema = z.object({
  company_name: z.string().min(1, "Company name is required").max(255, "Company name must be less than 255 characters"),
  address_line1: z.string().min(1, "Address line 1 is required").max(255, "Address line 1 must be less than 255 characters"),
  address_line2: z.string().max(255, "Address line 2 must be less than 255 characters").optional(),
  city: z.string().min(1, "City is required").max(255, "City must be less than 255 characters"),
  country: z.string().max(255, "Country must be less than 255 characters").optional(),
  state: z.string().max(255, "State must be less than 255 characters").optional(),
  postcode: z.string().min(1, "Postcode is required").max(255, "Postcode must be less than 255 characters"),
})

type CompanyDetailsForm = z.infer<typeof companyDetailsSchema>

interface MobileCompanyDetailsProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account") => void
  onBack: () => void
}

export function MobileCompanyDetails({ onNavigate, onBack }: MobileCompanyDetailsProps) {
  const { customer, refresh } = useCustomer()
  const { toast } = useToast()
  
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)

  const form = useForm<CompanyDetailsForm>({
    resolver: zodResolver(companyDetailsSchema),
    defaultValues: {
      company_name: "",
      address_line1: "",
      address_line2: "",
      city: "",
      country: "",
      state: "",
      postcode: "",
    }
  })

  // Initialize form with customer data
  useEffect(() => {
    if (customer) {
      form.reset({
        company_name: customer.company_name || "",
        address_line1: customer.address_line1 || "",
        address_line2: customer.address_line2 || "",
        city: customer.city || "",
        country: customer.country || "",
        state: customer.state || "",
        postcode: customer.postcode || "",
      })
      setLoading(false)
    }
  }, [customer, form])

  // Save company details to API (calls updateCompanyDetails function)
  const saveCompanyDetails = async (data: CompanyDetailsForm) => {
    setSaving(true)
    try {
      const response = await api.put('/customer', data)
      
      if (response.data?.success) {
        toast({
          title: "Success! 🎉",
          description: "Company details updated successfully",
        })
        // Immediately refresh the global customer state so other screens see latest data
        await refresh()
      }
    } catch (error: any) {
      console.error('Failed to save company details:', error)
      
      // Handle field-specific validation errors from API
      if (error.response?.data?.errors) {
        const apiErrors = error.response.data.errors
        Object.keys(apiErrors).forEach((field) => {
          form.setError(field as keyof CompanyDetailsForm, {
            type: "server",
            message: apiErrors[field][0]
          })
        })
      } else {
        // Generic error for network issues
        toast({
          title: "Error",
          description: "Failed to update company details. Please try again.",
          variant: "destructive",
        })
      }
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="bg-white p-4 flex items-center gap-3 border-b">
        <button onClick={onBack} className="p-2 hover:bg-gray-100 hover:cursor-pointer rounded-full">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <Building className="w-4 h-4 text-green-600" />
          </div>
          <span className="text-sm text-gray-600">Account / My Company</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Company Details Form */}
      <div className="bg-white mx-4 mt-4 mb-20 rounded-lg border border-gray-200">
        {loading ? (
          <div className="p-4">
            <div className="animate-pulse space-y-4">
              <div className="h-4 bg-gray-200 rounded w-1/4"></div>
              <div className="h-10 bg-gray-200 rounded"></div>
              <div className="h-4 bg-gray-200 rounded w-1/3"></div>
              <div className="space-y-3">
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
                <div className="h-10 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
        ) : (
          <>
            {/* Company Name Section */}
            <div className="p-4 border-b border-gray-100">
              <label className="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
              <Input
                type="text"
                {...form.register("company_name")}
                className={`w-full ${form.formState.errors.company_name ? 'border-red-500' : ''}`}
                placeholder="Enter company name"
              />
              {form.formState.errors.company_name && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.company_name.message}</p>
              )}
            </div>

        {/* Invoice Address Section */}
        <div className="p-4">
          <h3 className="text-sm font-medium text-gray-700 mb-4">Invoice Address</h3>
          
          <div className="space-y-4">
            {/* Line 1 */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Line 1</label>
              <Input
                type="text"
                {...form.register("address_line1")}
                className={`w-full ${form.formState.errors.address_line1 ? 'border-red-500' : ''}`}
                placeholder="Enter address line 1"
              />
              {form.formState.errors.address_line1 && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.address_line1.message}</p>
              )}
            </div>

            {/* Line 2 */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Line 2</label>
              <Input
                type="text"
                {...form.register("address_line2")}
                className={`w-full ${form.formState.errors.address_line2 ? 'border-red-500' : ''}`}
                placeholder="Enter address line 2"
              />
              {form.formState.errors.address_line2 && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.address_line2.message}</p>
              )}
            </div>

            {/* City */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">City</label>
              <Input
                type="text"
                {...form.register("city")}
                className={`w-full ${form.formState.errors.city ? 'border-red-500' : ''}`}
                placeholder="Enter city"
              />
              {form.formState.errors.city && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.city.message}</p>
              )}
            </div>

            {/* Country */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Country</label>
              <Input
                type="text"
                {...form.register("country")}
                className={`w-full ${form.formState.errors.country ? 'border-red-500' : ''}`}
                placeholder="Enter country"
              />
              {form.formState.errors.country && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.country.message}</p>
              )}
            </div>

            {/* State */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">State</label>
              <Input
                type="text"
                {...form.register("state")}
                className={`w-full ${form.formState.errors.state ? 'border-red-500' : ''}`}
                placeholder="Enter state"
              />
              {form.formState.errors.state && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.state.message}</p>
              )}
            </div>

            {/* Postcode */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Postcode</label>
              <Input
                type="text"
                {...form.register("postcode")}
                className={`w-full ${form.formState.errors.postcode ? 'border-red-500' : ''}`}
                placeholder="Enter postcode"
              />
              {form.formState.errors.postcode && (
                <p className="text-red-500 text-sm mt-1">{form.formState.errors.postcode.message}</p>
              )}
            </div>
          </div>
        </div>

        {/* Save Button */}
        <div className="p-4">
          <Button
            onClick={form.handleSubmit(saveCompanyDetails)}
            disabled={saving}
            className="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium flex items-center justify-center gap-2"
          >
            <Check className="w-5 h-5" />
            {saving ? "Saving..." : "Save"}
          </Button>
        </div>
        </>
        )}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t">
        <div className="grid grid-cols-4 py-2">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Home className="w-5 h-5" />
            <span className="text-xs mt-1">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <ShoppingBag className="w-5 h-5" />
            <span className="text-xs mt-1">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center py-2 text-gray-400 hover:text-green-600 hover:cursor-pointer">
            <Wallet className="w-5 h-5" />
            <span className="text-xs mt-1">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center py-2 text-green-600 hover:text-green-600 hover:cursor-pointer">
            <User className="w-5 h-5" />
            <span className="text-xs mt-1">Account</span>
          </button>
        </div>
      </nav>
    </div>
  )
}
