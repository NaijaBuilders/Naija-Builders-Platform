import type { Cart, CartItem } from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelCartItem = {
  id?: number | string;
  name?: string;
  category?: string;
  company?: string;
  price?: number | string;
  stock_qty?: number | string;
  quantity?: number | string;
  image_path?: string;
  line_total?: number | string;
};

type LaravelCartResponse = {
  items?: LaravelCartItem[];
  total?: number | string;
};

function mapCart(response: LaravelCartResponse): Cart {
  const items: CartItem[] = (response.items ?? []).map((item) => ({
    id: String(item.id ?? ''),
    name: String(item.name ?? 'Material'),
    category: String(item.category ?? 'General'),
    company: String(item.company ?? ''),
    price: Number(item.price ?? 0),
    stockQty: Number(item.stock_qty ?? 0),
    quantity: Number(item.quantity ?? 1),
    imagePath: String(item.image_path ?? ''),
    lineTotal: Number(item.line_total ?? 0),
  }));

  return {
    items,
    total: Number(response.total ?? 0),
  };
}

export const cartService = {
  async getCart(): Promise<Cart> {
    try {
      const response = await apiClient.get<LaravelCartResponse>('/cart');

      return mapCart(response.data);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async addItem(materialId: string, quantity = 1): Promise<Cart> {
    try {
      const response = await apiClient.post<LaravelCartResponse>('/cart/items', {
        material_id: Number(materialId),
        quantity,
      });

      return mapCart(response.data);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async updateItem(materialId: string, quantity: number): Promise<Cart> {
    try {
      const response = await apiClient.put<LaravelCartResponse>(
        `/cart/items/${materialId}`,
        { quantity }
      );

      return mapCart(response.data);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async removeItem(materialId: string): Promise<Cart> {
    try {
      const response = await apiClient.delete<LaravelCartResponse>(
        `/cart/items/${materialId}`
      );

      return mapCart(response.data);
    } catch (error) {
      handleServiceError(error);
    }
  },
};
