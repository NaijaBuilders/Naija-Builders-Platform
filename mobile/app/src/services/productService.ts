import type { ApiPaginatedResponse, Category, Product } from '../types';
import { apiClient, handleServiceError } from './apiClient';
import {
  mapLaravelCategories,
  mapLaravelMaterial,
} from './adapters';

export type ProductListParams = {
  category?: string;
  search?: string;
};

function materialParams(params: ProductListParams = {}) {
  return {
    category: params.category && params.category !== 'All' ? params.category : undefined,
    search: params.search?.trim() || undefined,
  };
}

export const productService = {
  async listProducts(params: ProductListParams = {}): Promise<Product[]> {
    try {
      const response = await apiClient.get<ApiPaginatedResponse<unknown>>('/materials', {
        params: materialParams(params),
      });

      return response.data.data.map((item) =>
        mapLaravelMaterial(item as Parameters<typeof mapLaravelMaterial>[0])
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getProduct(productId: string): Promise<Product> {
    try {
      const response = await apiClient.get<{
        material: unknown;
        images?: unknown[];
      }>(`/materials/${productId}`);

      return mapLaravelMaterial(
        response.data.material as Parameters<typeof mapLaravelMaterial>[0],
        (response.data.images ?? []) as Parameters<typeof mapLaravelMaterial>[1]
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async listCategories(): Promise<Category[]> {
    try {
      const response = await apiClient.get<{
        lookups?: { categories?: Array<{ category?: string }> };
      }>('/materials');

      return mapLaravelCategories(response.data.lookups?.categories ?? []);
    } catch (error) {
      handleServiceError(error);
    }
  },
};
