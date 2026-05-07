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
  delivery_address?: string;
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
};
