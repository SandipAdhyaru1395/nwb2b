"use client"

import React, { InputHTMLAttributes, forwardRef, useId } from "react"

export type FloatingInputProps = {
  label: string
  value?: string
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void
  containerClassName?: string
  error?: string | null
} & Omit<InputHTMLAttributes<HTMLInputElement>, "value" | "onChange"> 

const FloatingInput = forwardRef<HTMLInputElement, FloatingInputProps>(function FloatingInput(
  { label, value, onChange, containerClassName = "", type = "text", placeholder, error, ...rest },
  ref
) {
  const autoId = useId()
  const id = rest.id || autoId
  return (
    <div className={`relative ${containerClassName}`}>
      <input
        id={id}
        ref={ref}
        type={type}
        value={value}
        onChange={onChange}
        className={`peer w-full rounded px-3 pt-5 pb-2 mt-1 outline-none placeholder-gray-400 focus:placeholder-transparent transition-all duration-150 peer-focus:pt-7 ${error ? 'border-red-500 focus:border-red-600 border' : 'border focus:border-black/70'} ${rest.className || ''}`}
        placeholder={placeholder}
        {...rest}
      />
      <label
        htmlFor={id}
        className={`absolute left-3 top-2 ${error ? 'text-red-600' : 'text-gray-600'} text-[10px] transition-opacity duration-150 pointer-events-none bg-transparent
          opacity-0 peer-focus:opacity-100
        `}
      >
        {label}
      </label>
      {error && <p className="text-red-600 text-xs mt-1">{error}</p>}
    </div>
  )
})

export default FloatingInput


