"use client";

import { useEffect, useMemo, useState } from "react";
import { Search, Filter, X, ShoppingBag, ChevronDown, ChevronUp, Plus, Minus, Star, Home, Wallet, User } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/hooks/use-toast";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faGauge, faShop, faWallet, faUser, faBars, faFilter } from "@fortawesome/free-solid-svg-icons";
import api from "@/lib/axios";

interface MobileShopProps {
  onNavigate: (page: "dashboard" | "shop" | "basket" | "wallet" | "account", favorites?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  increment: (product: ProductItem) => void;
  decrement: (product: ProductItem) => void;
  totals: { units: number; skus: number; subtotal: number; totalDiscount: number; total: number };
  showFavorites?: boolean;
}

interface ProductItem {
  id: number;
  name: string;
  image: string;
  price: string;
  discount?: string;
  step_quantity?: number;
  wallet_credit?: number;
}

// Generic tree node that can be either a category (has subcategories)
// or a brand (has products). Brands appear as subcategories of leaf categories.
interface TreeNode {
  name: string;
  badge?: string;
  badgeColor?: string;
  // Optional comma-separated or array of tags coming from backend (e.g., "NEW,HOT")
  tags?: string[] | string;
  subcategories?: TreeNode[];
  products?: ProductItem[];
  is_special: number;
  image: string;
}

// fetched categories state
const initialCategories: TreeNode[] = [];

import { useCurrency } from "@/components/currency-provider";
import { useCustomer } from "@/components/customer-provider";
import { Banner } from "@/components/banner";

export function MobileShop({ onNavigate, cart, increment, decrement, totals, showFavorites = false }: MobileShopProps) {
  const { format, symbol } = useCurrency();
  const [searchQuery, setSearchQuery] = useState("");
  const [categories, setCategories] = useState<TreeNode[]>(initialCategories);
  // Track expanded nodes by path key (e.g., "Vaping", "Vaping::Disposables", "Vaping::Disposables::Brand X")
  const [expandedPaths, setExpandedPaths] = useState<string[]>([]);
  const { toast } = useToast();
  const { isFavorite, setFavorite } = useCustomer();

  // Calculate total wallet credit from cart items
  const totalWalletCredit = useMemo(() => {
    return Object.values(cart).reduce((sum, { product, quantity }) => {
      const credit = typeof product.wallet_credit === "number" ? product.wallet_credit : 0;
      return sum + credit * quantity;
    }, 0);
  }, [cart]);

  const togglePath = (path: string, singleRoot = false) => {
    setExpandedPaths((prev) => {
      const isOpen = prev.includes(path);
      if (singleRoot) {
        return isOpen ? [] : [path];
      }
      return isOpen ? prev.filter((p) => p !== path) : [...prev, path];
    });
  };

  useEffect(() => {
    let isMounted = true;
    const filterNodesWithProducts = (nodes: TreeNode[]): TreeNode[] => {
      return nodes
        .map((node) => {
          const filteredChildren = node.subcategories ? filterNodesWithProducts(node.subcategories) : undefined;
          const productsCount = Array.isArray(node.products) ? node.products.length : 0;
          const hasProductsHere = productsCount > 0;
          const hasProductsInChildren = Array.isArray(filteredChildren) && filteredChildren.length > 0;
          if (!hasProductsHere && !hasProductsInChildren) {
            return null as unknown as TreeNode;
          }
          return {
            ...node,
            ...(filteredChildren ? { subcategories: filteredChildren } : {}),
          };
        })
        .filter((n): n is TreeNode => Boolean(n));
    };

    const fetchData = async () => {
      try {
        if (typeof window !== "undefined") {
          window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-start" } }));
        }
        const res = await api.get("/products");
        const data = res.data;
        if (!isMounted) return;
        if (Array.isArray(data?.categories)) {
          const filtered = filterNodesWithProducts(data.categories as TreeNode[]);
          setCategories(filtered);
          try {
            sessionStorage.setItem("products_cache", JSON.stringify({ at: Date.now(), categories: filtered }));
          } catch {}
        }
      } catch (e) {
        // keep categories empty on error
      } finally {
        if (typeof window !== "undefined") {
          window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-stop" } }));
        }
      }
    };

    // Serve cache if present; if not, fetch once (e.g., after login)
    try {
      const raw = sessionStorage.getItem("products_cache");
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed?.categories)) {
          setCategories(parsed.categories);
        } else {
          fetchData();
        }
      } else {
        fetchData();
      }
    } catch {
      fetchData();
    }

    return () => {
      isMounted = false;
    };
  }, []);

  // Derived categories filtered by search query (product name) and/or favorites. Keeps hierarchy to matches.
  const displayedCategories = useMemo(() => {
    const query = searchQuery.trim().toLowerCase();

    const filterByQueryAndFavorites = (nodes: TreeNode[]): TreeNode[] => {
      return nodes
        .map((node) => {
          const filteredChildren = node.subcategories ? filterByQueryAndFavorites(node.subcategories) : undefined;
          let filteredProducts = node.products;

          if (filteredProducts) {
            // Filter by search query if provided
            if (query) {
              filteredProducts = filteredProducts.filter((p) => p.name.toLowerCase().includes(query));
            }

            // Filter by favorites if showFavorites is true
            if (showFavorites) {
              filteredProducts = filteredProducts.filter((p) => isFavorite(p.id));
            }
          }

          const hasChildren = Array.isArray(filteredChildren) && filteredChildren.length > 0;
          const hasProducts = Array.isArray(filteredProducts) && filteredProducts.length > 0;

          if (!hasChildren && !hasProducts) {
            return null as unknown as TreeNode;
          }

          return {
            ...node,
            ...(filteredChildren ? { subcategories: filteredChildren } : {}),
            ...(filteredProducts ? { products: filteredProducts } : {}),
          };
        })
        .filter((n): n is TreeNode => Boolean(n));
    };

    return filterByQueryAndFavorites(categories);
  }, [categories, searchQuery, showFavorites, isFavorite]);

  // Auto-expand paths when searching to reveal matches.
  // Do NOT collapse on unrelated state changes (e.g., favorites toggle)
  useEffect(() => {
    const query = searchQuery.trim();
    if (!query) return;

    const paths: string[] = [];
    const traverse = (nodes: TreeNode[], parentPath?: string) => {
      nodes.forEach((node) => {
        const path = parentPath ? `${parentPath}::${node.name}` : node.name;
        if ((node.subcategories && node.subcategories.length) || (node.products && node.products.length)) {
          paths.push(path);
        }
        if (node.subcategories && node.subcategories.length) {
          traverse(node.subcategories, path);
        }
      });
    };
    traverse(displayedCategories);
    setExpandedPaths(paths);
  }, [searchQuery, displayedCategories]);

  // cart and totals are provided by parent

  const handleIncrement = (product: ProductItem) => {
    increment(product);
  };

  const handleDecrement = (product: ProductItem) => {
    const step = typeof product.step_quantity === "number" && product.step_quantity > 0 ? product.step_quantity : 1;
    const currentQuantity = cart[product.id]?.quantity || 0;
    decrement(product);
    if (currentQuantity === step) {
      toast({
        title: "Removed from Cart",
        description: `${product.name} removed from your basket`,
      });
    }
  };

  const toggleFavorite = async (product: ProductItem) => {
    const current = isFavorite(product.id);
    const prevExpanded = expandedPaths;
    try {
      await setFavorite(product.id, !current);
      // Restore expansion state to prevent collapsing due to re-render
      setExpandedPaths(prevExpanded);
      toast({ title: !current ? "Added to favourites" : "Removed from favourites", description: product.name });
    } catch (e: any) {
      setExpandedPaths(prevExpanded);
      toast({ title: "Failed to update favourites", description: e?.message || "Please try again", variant: "destructive" });
    }
  };

  return (
    <div className="min-h-screen flex flex-col w-full max-w-[1000px] mx-auto">
      {/* Header */}
      <div className="bg-white py-2 flex items-center border-b">
        <div className="mx-5 w-6 h-6  flex items-center justify-center">{showFavorites ? <Star className="w-4 h-4 text-white" /> : <FontAwesomeIcon icon={faShop} className="text-green-600" style={{ width: "30px", height: "24px" }} />}</div>
        <h1 className="text-lg font-semibold">{showFavorites ? "Favorites" : "Shop"}</h1>
      </div>

      {/* Search Bar */}
      <div className="bg-white py-2 border-b box-shadow-bottom mb-2 sticky top-0 z-50">
        <div className="relative flex items-center">
          <FontAwesomeIcon icon={faFilter} className="text-[#3dbe59] text-[24px] mx-5" />
          <div className="flex-1 relative">
            <Search className="absolute top-1/2 transform -translate-y-1/2 w-7 h-7 text-green-500" />
            <Input value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-9 pr-9 bg-gray-50 border-0 py-0" placeholder="Search products..." />
            {searchQuery && (
              <button onClick={() => setSearchQuery("")} className="absolute right-3 top-1/2 transform -translate-y-1/2">
                <X className="w-4 h-4 text-gray-400" />
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Banner */}
      {/* <div className="mt-0">
        <Banner />
      </div> */}

      {/* Categories (recursive) */}
      <div className="space-y-2 overflow-y-auto pb-56">
        {displayedCategories.map((node) => (
          <CategoryNode key={node.name} node={node} path={node.name} depth={0} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={handleIncrement} onDecrement={handleDecrement} isFavorite={isFavorite} onToggleFavorite={toggleFavorite} />
        ))}
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        {/* Basket Summary (shows when items in cart) */}
        {totals.units > 0 && (
          <div className="bg-white border-b px-4 py-2 space-y-1 box-shadow-top">
            <div className="flex items-center justify-center text-sm gap-2 pt-1">
              <span className="text-black font-semibold">{totals.units} Units</span>
              <span className="spacer"> | </span>
              <span className="text-black font-semibold">{totals.skus} SKUs</span>
              <span className="spacer"> | </span>
              <span className="font-semibold text-black">{format(totals.total)}</span>
              <span className="spacer"> | </span>
              <div className="flex items-center">
                {totalWalletCredit > 0 && (
                  <span className="inline-flex items-center gap-1 text-green-600 text-sm font-semibold">
                    <Wallet className="w-4 h-4" />
                    <span>
                      {symbol}
                      {totalWalletCredit.toFixed(2)}
                    </span>
                  </span>
                )}
                {totals.totalDiscount > 0 && <span className="text-green-600 text-xs">{format(totals.totalDiscount)} off</span>}
              </div>
            </div>
            <div className="text-sm font-semibold text-center text-gray-400 pb-1">Spend {format(4.5)} more for FREE delivery</div>
            <button onClick={() => onNavigate("basket")} className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-sm font-semibold hover:cursor-pointer text-lg box-shadow-bottom">
              View Basket
            </button>
          </div>
        )}
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop", false)} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faShop} className="text-[#607565]" style={{ width: "30px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faWallet} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faUser} className="text-[#607565]" style={{ width: "21px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}

type CategoryNodeProps = {
  node: TreeNode;
  path: string;
  depth: number;
  expandedPaths: string[];
  togglePath: (path: string, singleRoot?: boolean) => void;
  cart: Record<number, { product: ProductItem; quantity: number }>;
  onIncrement: (product: ProductItem) => void;
  onDecrement: (product: ProductItem) => void;
  isFavorite: (productId: number) => boolean;
  onToggleFavorite: (product: ProductItem) => void;
};

function CategoryNode({ node, path, depth, expandedPaths, togglePath, cart, onIncrement, onDecrement, isFavorite, onToggleFavorite }: CategoryNodeProps) {
  const { symbol } = useCurrency();
  const isOpen = expandedPaths.includes(path);
  const hasChildren = Array.isArray(node.subcategories) && node.subcategories.length > 0;
  const hasProducts = Array.isArray(node.products) && node.products.length > 0;

  // Indentation only: increase left padding by depth
  const depthPad = ["", "", "", "", "", ""];
  // Left margin by depth to visually indent levels
  const depthMargin = ["", "", "", "", "", ""];
  const depthColors = [
    "bg-green-600", // depth 0
    "bg-green-50", // depth 1
    "bg-gray-100", // depth 2
    "", // depth 3
    "", // depth 4
    "", // depth 5+
  ];

  const padClass = depthPad[Math.min(depth, depthPad.length - 1)];
  const marginClass = depthMargin[Math.min(depth, depthMargin.length - 1)];

  if (node.is_special == 1) {
    var bgClass = "bg-yellow-400";
  } else {
    var bgClass = depthColors[Math.min(depth, depthColors.length - 1)];
  }

  const buttonClasses = `w-full ${bgClass} mb-2 flex items-center justify-between ${depth === 0 ? "font-medium" : ""}`;
  // Vertical gap between levels increases with depth
  const depthGap = ["mt-2", "mt-2", "mt-2", "mt-2", "mt-2", "mt-2"];
  const gapClass = depthGap[Math.min(depth, depthGap.length - 1)];

  const nameTextColorClass = depth === 0 && node.is_special !== 1 ? "text-white" : "text-gray-800";
  const iconColorClass = nameTextColorClass;

  return (
    <div className="space-y-2">
      <button onClick={() => togglePath(path, depth === 0)} className={`${buttonClasses} ${marginClass} hover:cursor-pointer h-[50px]`}>
        <div className={`flex items-center gap-2 ${padClass}`}>
          {hasProducts && (
            <div className="w-[42px] h-[42px] bg-white rounded-md border overflow-hidden flex items-center justify-center offer-products">
              <img src={node?.image || (node.products && node.products[0]?.image)} alt="" className="w-[42px] h-[42px] object-cover" />
            </div>
          )}
          <span className={`font-semibold ${nameTextColorClass}`}>{node.name}</span>
          {/* Render tags if provided */}
          {(() => {
            const raw = node.tags;
            const tags = Array.isArray(raw)
              ? raw
              : typeof raw === "string" && raw.trim().length
              ? raw
                  .split(",")
                  .map((t) => t.trim())
                  .filter(Boolean)
              : [];
            if (!tags.length) return null;
            const colors = ["bg-green-600", "bg-orange-500", "bg-red-600"];
            return (
              <div className="flex items-center gap-1">
                {tags.map((tag, idx) => (
                  <span key={`${tag}-${idx}`} className={`text-white text-[13px] leading-none px-4 py-2 rounded-full ${colors[idx % colors.length]}`}>
                    {tag.toUpperCase()}
                  </span>
                ))}
              </div>
            );
          })()}
          {node.badge && <Badge className={`${node.badgeColor} text-white text-xs px-2 py-1`}>{node.badge}</Badge>}
        </div>
        {isOpen ? <ChevronUp className={`w-9 h-9 ${iconColorClass}`} /> : <ChevronDown className={`w-9 h-9 ${iconColorClass}`} />}
      </button>

      {isOpen && (
        <div className={`space-y-3 ${gapClass}`}>
          {hasChildren &&
            node.subcategories!.map((child) => {
              const childPath = `${path}::${child.name}`;
              return <CategoryNode key={childPath} node={child} path={childPath} depth={depth + 1} expandedPaths={expandedPaths} togglePath={togglePath} cart={cart} onIncrement={onIncrement} onDecrement={onDecrement} isFavorite={isFavorite} onToggleFavorite={onToggleFavorite} />;
            })}

          {hasProducts && (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 2xl:grid-cols-8 gap-3 px-3">
              {node.products!.map((product) => (
                <div key={product.id} className="bg-white border-b relative pb-2 offer-plus-sign z-10 w-[113px]">
                  {cart[product.id]?.quantity ? (
                    <div className="offer-increase-sign absolute z-10 right-0 flex items-center gap-2 bg-black rounded-full px-1 shadow-sm w-[113px]">
                      <button onClick={() => onDecrement(product)} className="w-8 h-8 text-green-500 flex items-center justify-center hover:cursor-pointer">
                        <Minus className="w-6 h-6" />
                      </button>
                      <span className="min-w-[1.5rem] text-center text-lg font-medium text-white">{cart[product.id]?.quantity}</span>
                      <button onClick={() => onIncrement(product)} className="w-8 h-8 text-green-500 flex items-center justify-center hover:cursor-pointer">
                        <Plus className="w-6 h-6" />
                      </button>
                    </div>
                  ) : (
                    <button onClick={() => onIncrement(product)} className="offer-plus-sign z-10 absolute right-0 w-8 h-8 bg-black rounded-full flex items-center justify-center hover:cursor-pointer">
                      <Plus className="w-6 h-6 text-green-500" />
                    </button>
                  )}

                  <div className="aspect-square mb-2 flex items-center relative justify-center">
                    <img src={product.image || "/placeholder.svg"} alt={product.name} className="w-full h-[113px] object-contain" />
                    <div className="absolute right-0 bottom-0">
                      <button onClick={() => onToggleFavorite(product)} className="w-8 h-8 rounded-full border-2 flex items-center justify-center hover:cursor-pointer bg-white" aria-label="Toggle favourite" title="Toggle favourite">
                        <Star className={`w-[16px] h-[16px] ${isFavorite(product.id) ? "text-[#3dbe59] fill-[#3dbe59]" : "text-[#c0d3c4] fill-[#c0d3c4]"}`} />
                      </button>
                    </div>
                  </div>

                  <div className="space-y-1">
                    <div>
                      <span className="font-bold p-[2px] bg-[#f0f5f1] flex justify-between offer-product-price gap-3">
                        {product.price}
                        {typeof product.wallet_credit === "number" && (
                          <span className="inline-flex items-center text-green-600 text-xs font-semibold">
                            <Wallet className="w-3 h-3 mr-1" />
                            <span>
                              {symbol}
                              {product.wallet_credit.toFixed(2)}
                            </span>
                          </span>
                        )}
                      </span>
                    </div>
                    <div className="text-left offer-product-name p-1">
                      <span className="text-sm text-black">{product.name}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
