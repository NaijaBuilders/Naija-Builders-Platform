import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import { cartService } from '../services';
import type { Cart } from '../types';
import { useAppState } from './AppContext';

const emptyCart: Cart = { items: [], total: 0 };

type CartContextValue = {
  cart: Cart;
  itemCount: number;
  loading: boolean;
  refreshCart: () => Promise<void>;
  addToCart: (materialId: string, quantity?: number) => Promise<void>;
  updateQuantity: (materialId: string, quantity: number) => Promise<void>;
  removeFromCart: (materialId: string) => Promise<void>;
  clearLocalCart: () => void;
};

const CartContext = createContext<CartContextValue | undefined>(undefined);

export function CartProvider({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAppState();
  const [cart, setCart] = useState<Cart>(emptyCart);
  const [loading, setLoading] = useState(false);

  const refreshCart = useCallback(async () => {
    if (!isAuthenticated) {
      setCart(emptyCart);
      return;
    }

    setLoading(true);
    try {
      setCart(await cartService.getCart());
    } catch {
      // Keep the last known cart on transient failures.
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated]);

  useEffect(() => {
    refreshCart();
  }, [refreshCart]);

  const addToCart = useCallback(async (materialId: string, quantity = 1) => {
    setCart(await cartService.addItem(materialId, quantity));
  }, []);

  const updateQuantity = useCallback(
    async (materialId: string, quantity: number) => {
      setCart(await cartService.updateItem(materialId, quantity));
    },
    []
  );

  const removeFromCart = useCallback(async (materialId: string) => {
    setCart(await cartService.removeItem(materialId));
  }, []);

  const clearLocalCart = useCallback(() => {
    setCart(emptyCart);
  }, []);

  const itemCount = useMemo(
    () => cart.items.reduce((sum, item) => sum + item.quantity, 0),
    [cart.items]
  );

  const value = useMemo(
    () => ({
      addToCart,
      cart,
      clearLocalCart,
      itemCount,
      loading,
      refreshCart,
      removeFromCart,
      updateQuantity,
    }),
    [
      addToCart,
      cart,
      clearLocalCart,
      itemCount,
      loading,
      refreshCart,
      removeFromCart,
      updateQuantity,
    ]
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const context = useContext(CartContext);

  if (!context) {
    throw new Error('useCart must be used inside CartProvider');
  }

  return context;
}
