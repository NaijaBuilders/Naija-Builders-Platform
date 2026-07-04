import type { SavedProduct } from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelSavedMaterial = {
  material_id?: number | string;
  name?: string;
  category?: string;
  price?: number | string;
  price_unit?: string;
  company?: string;
  full_name?: string;
  saved_at?: string;
};

type LaravelSavedResponse = {
  saved_materials?: LaravelSavedMaterial[];
};

export const savedService = {
  async list(): Promise<SavedProduct[]> {
    try {
      const response = await apiClient.get<LaravelSavedResponse>('/saved-products');

      return (response.data.saved_materials ?? []).map((item) => ({
        id: String(item.material_id ?? ''),
        name: String(item.name ?? 'Material'),
        category: String(item.category ?? 'General'),
        price: Number(item.price ?? 0),
        priceUnit: String(item.price_unit ?? 'item'),
        company: String(item.company ?? item.full_name ?? 'Supplier'),
        savedAt: String(item.saved_at ?? ''),
      }));
    } catch (error) {
      handleServiceError(error);
    }
  },

  async save(materialId: string): Promise<void> {
    try {
      await apiClient.post('/saved-products', {
        material_id: Number(materialId),
      });
    } catch (error) {
      handleServiceError(error);
    }
  },

  async unsave(materialId: string): Promise<void> {
    try {
      await apiClient.delete(`/saved-products/${materialId}`);
    } catch (error) {
      handleServiceError(error);
    }
  },
};
