"use client"

import { useEffect } from "react"
import { useSettings } from "./settings-provider"

/**
 * ThemeProvider component that applies theme colors from settings to all green color usages
 * This replaces all green color classes with the theme color:
 * - All bg-green-* backgrounds
 * - All text-green-* text colors
 * - All border-green-* borders
 * - All hover:bg-green-* hover states
 * - All hover:text-green-* hover text colors
 * - Login buttons (bg-black)
 */
export function ThemeProvider() {
  const { settings, loading } = useSettings()

  useEffect(() => {
    if (loading || !settings?.theme) return

    const theme = settings.theme

    // If default colors are enabled, don't apply any theme colors
    if (theme.use_default === true) {
      // Remove any existing theme styles
      removeThemeStyles()
      return
    }

    // Apply theme colors to all green color usages
    applyThemeColors(
      theme.button_color,
      theme.button_hover,
      theme.button_login
    )
  }, [settings?.theme, loading])

  return null
}

/**
 * Remove theme styles if default colors are selected
 */
function removeThemeStyles() {
  const styleId = 'dynamic-theme-styles'
  const styleElement = document.getElementById(styleId)
  if (styleElement) {
    styleElement.remove()
  }
}

/**
 * Apply theme colors dynamically to all green color classes
 */
function applyThemeColors(
  themeColor?: string | null,
  themeHover?: string | null,
  login?: string | null
) {
  const styleId = 'dynamic-theme-styles'
  let styleElement = document.getElementById(styleId) as HTMLStyleElement

  if (!styleElement) {
    styleElement = document.createElement('style')
    styleElement.id = styleId
    document.head.appendChild(styleElement)
  }

  let css = ''

  // Apply theme color to all background green classes
  if (themeColor) {
    css += `
      /* Background colors */
      .bg-green-50,
      .bg-green-100,
      .bg-green-200,
      .bg-green-300,
      .bg-green-400,
      .bg-green-500,
      .bg-green-600,
      .bg-green-700,
      .bg-green-800,
      .bg-green-900 {
        background-color: ${themeColor} !important;
      }
    `

    // Apply theme color to all text green classes
    css += `
      /* Text colors */
      .text-green-50,
      .text-green-100,
      .text-green-200,
      .text-green-300,
      .text-green-400,
      .text-green-500,
      .text-green-600,
      .text-green-700,
      .text-green-800,
      .text-green-900 {
        color: ${themeColor} !important;
      }
    `

    // Apply theme color to all border green classes
    css += `
      /* Border colors */
      .border-green-50,
      .border-green-100,
      .border-green-200,
      .border-green-300,
      .border-green-400,
      .border-green-500,
      .border-green-600,
      .border-green-700,
      .border-green-800,
      .border-green-900 {
        border-color: ${themeColor} !important;
      }
    `
  }

  // Apply theme hover color to all hover green classes
  if (themeHover) {
    css += `
      /* Hover background colors */
      .hover\\:bg-green-50:hover,
      .hover\\:bg-green-100:hover,
      .hover\\:bg-green-200:hover,
      .hover\\:bg-green-300:hover,
      .hover\\:bg-green-400:hover,
      .hover\\:bg-green-500:hover,
      .hover\\:bg-green-600:hover,
      .hover\\:bg-green-700:hover,
      .hover\\:bg-green-800:hover,
      .hover\\:bg-green-900:hover {
        background-color: ${themeHover} !important;
      }

      /* Hover text colors */
      .hover\\:text-green-50:hover,
      .hover\\:text-green-100:hover,
      .hover\\:text-green-200:hover,
      .hover\\:text-green-300:hover,
      .hover\\:text-green-400:hover,
      .hover\\:text-green-500:hover,
      .hover\\:text-green-600:hover,
      .hover\\:text-green-700:hover,
      .hover\\:text-green-800:hover,
      .hover\\:text-green-900:hover {
        color: ${themeHover} !important;
      }
    `
  }

  // Login buttons: bg-black
  if (login) {
    css += `
      .loginregisterform button,
      button.bg-black {
        background-color: ${login} !important;
      }
    `
  }

  styleElement.textContent = css
}
