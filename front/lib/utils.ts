import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function setFavicon(url: string) {
  if (typeof document === 'undefined') return
  const rels = ['icon', 'shortcut icon', 'apple-touch-icon']
  rels.forEach((rel) => {
    let link = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement | null
    if (!link) {
      link = document.createElement('link')
      link.rel = rel
      document.head.appendChild(link)
    }
    link.href = url
    if (rel === 'icon') link.type = 'image/png'
  })
}
