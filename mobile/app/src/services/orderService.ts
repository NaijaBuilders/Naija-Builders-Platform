import type { Order, OrderStatus, UserRole } from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelOrderItem = {
  id?: number | string;
  name?: string;
  quantity?: number | string;
  unit_price?: number | string;
};

type LaravelOrder = {
  id?: number | string;
  reference?: string;
  title?: string;
  total_amount?: number | string;
  item_count?: number | string;
  order_status?: string;
  verification_tier?: number | string;
  verification_status?: string;
  review_status?: string | null;
  payment_provider?: string | null;
  payment_method_type?: string | null;
  payment_currency?: string | null;
  payment_amount?: number | string | null;
  gateway_risk_level?: string | null;
  delivery_address?: string;
  recipient?: {
    name?: string;
    phone?: string;
    relationship?: string | null;
  };
  delivery?: {
    status?: string;
    photos?: Array<{
      id?: number | string;
      path?: string;
      captured_at?: string;
      gps_lat?: number | string | null;
      gps_lng?: number | string | null;
    }>;
    otp_generated_at?: string | null;
    otp_confirmed_at?: string | null;
    dispute_window_ends_at?: string | null;
    dispute_status?: string | null;
    dispute_outcome?: string | null;
    escrow_release_at?: string | null;
  };
  supplier_name?: string;
  buyer_name?: string;
  items?: LaravelOrderItem[];
  created_at?: string;
  updated_at?: string;
};

function normalizeOrderStatus(status?: string): OrderStatus {
  if (status === 'completed' || status === 'delivered') {
    return 'Delivered';
  }

  if (status === 'cancelled') {
    return 'Cancelled';
  }

  if (status === 'processing') {
    return 'Processing';
  }

  return 'Pending';
}

function mapLaravelOrder(order: LaravelOrder, role: UserRole | 'both'): Order {
  const id = String(order.id ?? '');
  const items = (order.items ?? []).map((item) => ({
    id: String(item.id ?? `${id}-item`),
    name: String(item.name ?? 'Material'),
    quantity: Number(item.quantity ?? 1),
    unit_price: Number(item.unit_price ?? 0),
  }));

  return {
    id,
    title: String(order.title ?? `Order ${order.reference ?? id}`),
    reference: String(order.reference ?? `NB-${id}`),
    supplierName: String(order.supplier_name ?? 'Supplier'),
    buyerName: String(order.buyer_name ?? 'Buyer'),
    total: Number(order.total_amount ?? 0),
    itemCount: Number(order.item_count ?? items.length),
    placedAt: String(order.created_at ?? ''),
    status: normalizeOrderStatus(order.order_status),
    visibleTo: role,
    delivery_address: String(order.delivery_address ?? ''),
    verificationTier: Number(order.verification_tier ?? 1),
    verificationStatus: order.verification_status
      ? String(order.verification_status)
      : undefined,
    reviewStatus: order.review_status ?? null,
    paymentProvider: order.payment_provider ?? null,
    paymentMethodType: order.payment_method_type ?? null,
    paymentCurrency: order.payment_currency ?? null,
    paymentAmount:
      order.payment_amount === null || order.payment_amount === undefined
        ? null
        : Number(order.payment_amount),
    gatewayRiskLevel: order.gateway_risk_level ?? null,
    recipient: order.recipient
      ? {
          name: String(order.recipient.name ?? ''),
          phone: String(order.recipient.phone ?? ''),
          relationship: order.recipient.relationship ?? null,
        }
      : undefined,
    delivery: order.delivery
      ? {
          status: String(order.delivery.status ?? 'pending'),
          photos: (order.delivery.photos ?? []).map((photo) => ({
            id: String(photo.id ?? ''),
            path: String(photo.path ?? ''),
            captured_at: String(photo.captured_at ?? ''),
            gps_lat:
              photo.gps_lat === null || photo.gps_lat === undefined
                ? null
                : Number(photo.gps_lat),
            gps_lng:
              photo.gps_lng === null || photo.gps_lng === undefined
                ? null
                : Number(photo.gps_lng),
          })),
          otp_generated_at: order.delivery.otp_generated_at ?? null,
          otp_confirmed_at: order.delivery.otp_confirmed_at ?? null,
          dispute_window_ends_at: order.delivery.dispute_window_ends_at ?? null,
          dispute_status: order.delivery.dispute_status ?? null,
          dispute_outcome: order.delivery.dispute_outcome ?? null,
          escrow_release_at: order.delivery.escrow_release_at ?? null,
        }
      : undefined,
    items,
    created_at: String(order.created_at ?? ''),
    updated_at: String(order.updated_at ?? order.created_at ?? ''),
  };
}

export const orderService = {
  async listOrders(role: UserRole): Promise<Order[]> {
    try {
      const response = await apiClient.get<{ data?: unknown[] }>('/orders');

      return (response.data.data ?? []).map((item) =>
        mapLaravelOrder(item as LaravelOrder, role)
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getOrder(orderId: string): Promise<Order> {
    try {
      const response = await apiClient.get<{ order: unknown }>(`/orders/${orderId}`);

      return mapLaravelOrder(response.data.order as LaravelOrder, 'both');
    } catch (error) {
      handleServiceError(error);
    }
  },

  async confirmDeliveryOtp(orderId: string, otp: string): Promise<void> {
    try {
      await apiClient.post(`/orders/${orderId}/delivery/otp/confirm`, { otp });
    } catch (error) {
      handleServiceError(error);
    }
  },

  async raiseDispute(orderId: string, reason: string): Promise<void> {
    try {
      await apiClient.post(`/orders/${orderId}/disputes`, { reason });
    } catch (error) {
      handleServiceError(error);
    }
  },
};
