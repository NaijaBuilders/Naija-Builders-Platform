import type { DeliveryAddress, DeliveryAddressPayload } from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelAddress = {
  id?: number | string;
  label?: string | null;
  contact_name?: string | null;
  contact_phone?: string | null;
  state?: string | null;
  lga?: string | null;
  address?: string;
  instructions?: string | null;
  is_default?: boolean | number;
};

function mapAddress(address: LaravelAddress): DeliveryAddress {
  return {
    id: String(address.id ?? ''),
    label: String(address.label ?? ''),
    contactName: String(address.contact_name ?? ''),
    contactPhone: String(address.contact_phone ?? ''),
    state: String(address.state ?? ''),
    lga: String(address.lga ?? ''),
    address: String(address.address ?? ''),
    instructions: String(address.instructions ?? ''),
    isDefault: address.is_default === true || Number(address.is_default) === 1,
  };
}

function toLaravelPayload(payload: DeliveryAddressPayload) {
  return {
    label: payload.label || undefined,
    contact_name: payload.contactName || undefined,
    contact_phone: payload.contactPhone || undefined,
    state: payload.state || undefined,
    lga: payload.lga || undefined,
    address: payload.address,
    instructions: payload.instructions || undefined,
    is_default: payload.isDefault ?? undefined,
  };
}

export const addressService = {
  async list(): Promise<DeliveryAddress[]> {
    try {
      const response = await apiClient.get<{ addresses?: LaravelAddress[] }>(
        '/addresses'
      );

      return (response.data.addresses ?? []).map(mapAddress);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async create(payload: DeliveryAddressPayload): Promise<DeliveryAddress> {
    try {
      const response = await apiClient.post<{ address: LaravelAddress }>(
        '/addresses',
        toLaravelPayload(payload)
      );

      return mapAddress(response.data.address);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async update(
    addressId: string,
    payload: DeliveryAddressPayload
  ): Promise<DeliveryAddress> {
    try {
      const response = await apiClient.put<{ address: LaravelAddress }>(
        `/addresses/${addressId}`,
        toLaravelPayload(payload)
      );

      return mapAddress(response.data.address);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async remove(addressId: string): Promise<void> {
    try {
      await apiClient.delete(`/addresses/${addressId}`);
    } catch (error) {
      handleServiceError(error);
    }
  },
};
