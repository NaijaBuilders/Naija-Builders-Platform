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
        is_saved?: boolean;
        product_reviews?: Array<{
          rating?: number | string;
          review_text?: string | null;
          reviewer_full_name?: string | null;
          reviewer_company?: string | null;
          created_at?: string | null;
        }>;
        product_rating_avg?: number | string | null;
        product_rating_count?: number | string;
        supplier_rating_avg?: number | string | null;
        supplier_rating_count?: number | string;
        current_user_product_rating?: number | null;
      }>(`/materials/${productId}`);

      const product = mapLaravelMaterial(
        response.data.material as Parameters<typeof mapLaravelMaterial>[0],
        (response.data.images ?? []) as Parameters<typeof mapLaravelMaterial>[1]
      );

      product.detail = {
        isSaved: Boolean(response.data.is_saved),
        productRatingAvg:
          response.data.product_rating_avg === null ||
          response.data.product_rating_avg === undefined
            ? null
            : Number(response.data.product_rating_avg),
        productRatingCount: Number(response.data.product_rating_count ?? 0),
        supplierRatingAvg:
          response.data.supplier_rating_avg === null ||
          response.data.supplier_rating_avg === undefined
            ? null
            : Number(response.data.supplier_rating_avg),
        supplierRatingCount: Number(response.data.supplier_rating_count ?? 0),
        currentUserProductRating:
          response.data.current_user_product_rating ?? null,
        reviews: (response.data.product_reviews ?? []).map((review) => ({
          rating: Number(review.rating ?? 0),
          reviewText: String(review.review_text ?? ''),
          reviewerName: String(
            review.reviewer_company || review.reviewer_full_name || 'Verified Buyer'
          ),
          createdAt: String(review.created_at ?? ''),
        })),
      };

      return product;
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
