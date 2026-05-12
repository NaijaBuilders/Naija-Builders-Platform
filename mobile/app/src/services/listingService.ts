import type {
  ListingStatus,
  SupplierListing,
  SupplierListingCreatePayload,
} from '../types';
import { apiClient, handleServiceError } from './apiClient';
import { assetSource } from './adapters';

export type SupplierListingListParams = {
  search?: string;
  status?: ListingStatus | 'All';
};

type LaravelListing = {
  id?: number | string;
  name?: string;
  category?: string;
  price?: number | string;
  price_unit?: string;
  stock_qty?: number | string;
  status?: string;
  is_negotiable?: boolean | number | string;
  image_path?: string;
  image_url?: string;
  created_at?: string;
};

type ReactNativeFile = {
  uri: string;
  name: string;
  type: string;
};

const listingStatuses: ListingStatus[] = ['active', 'inactive', 'out_of_stock'];

function normalizeStatus(status?: string): ListingStatus {
  return listingStatuses.includes(status as ListingStatus)
    ? (status as ListingStatus)
    : 'active';
}

function mapSupplierListing(listing: LaravelListing): SupplierListing {
  return {
    id: String(listing.id ?? ''),
    name: String(listing.name ?? 'Unnamed listing'),
    category: String(listing.category ?? 'General'),
    price: Number(listing.price ?? 0),
    unit: String(listing.price_unit ?? 'item'),
    image: listing.image_url
      ? { uri: String(listing.image_url) }
      : assetSource(listing.image_path),
    stockCount: Number(listing.stock_qty ?? 0),
    status: normalizeStatus(listing.status),
    negotiable:
      listing.is_negotiable === true ||
      Number(listing.is_negotiable ?? 0) === 1,
    created_at: String(listing.created_at ?? new Date().toISOString()),
  };
}

function listingParams(params: SupplierListingListParams = {}) {
  return {
    search: params.search?.trim() || undefined,
    status:
      params.status && params.status !== 'All' ? params.status : undefined,
  };
}

function imageFileFromUri(uri: string, index: number): ReactNativeFile {
  const cleanUri = uri.split('?')[0];
  const extension = cleanUri.split('.').pop()?.toLowerCase();
  const safeExtension =
    extension === 'png' || extension === 'webp' || extension === 'jpg'
      ? extension
      : 'jpeg';

  const type =
    safeExtension === 'png'
      ? 'image/png'
      : safeExtension === 'webp'
        ? 'image/webp'
        : 'image/jpeg';

  return {
    uri,
    name: `listing-image-${index + 1}.${safeExtension}`,
    type,
  };
}

export const listingService = {
  async listSupplierListings(
    params: SupplierListingListParams = {}
  ): Promise<SupplierListing[]> {
    try {
      const response = await apiClient.get<{ data?: unknown[] }>('/listings', {
        params: listingParams(params),
      });

      return (response.data.data ?? []).map((item) =>
        mapSupplierListing(item as LaravelListing)
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async createSupplierListing(
    payload: SupplierListingCreatePayload
  ): Promise<void> {
    try {
      const formData = new FormData();

      formData.append('name', payload.name.trim());
      formData.append('category', payload.category.trim());
      formData.append('description', payload.description.trim());
      formData.append('price', payload.price);
      formData.append('price_unit', payload.price_unit);
      formData.append('stock_qty', payload.stock_qty);
      formData.append('status', payload.status);
      formData.append('is_negotiable', payload.is_negotiable ? '1' : '0');

      payload.imageUris.forEach((uri, index) => {
        formData.append(
          'images[]',
          imageFileFromUri(uri, index) as unknown as Blob
        );
      });

      await apiClient.post('/listings', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    } catch (error) {
      handleServiceError(error);
    }
  },
};
