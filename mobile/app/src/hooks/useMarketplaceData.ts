import React, { useCallback, useEffect, useState } from 'react';
import {
  dashboardService,
  messageService,
  orderService,
  productService,
  userService,
  type ProductListParams,
} from '../services';
import type {
  Category,
  CompanyProfile,
  Conversation,
  DashboardStat,
  Order,
  Product,
  QuickAction,
  UserRole,
} from '../types';

type ResourceState<T> = {
  data: T;
  loading: boolean;
  error: Error | null;
  refresh: () => void;
};

function useResource<T>(
  load: () => Promise<T>,
  dependencies: React.DependencyList,
  initialData: T
): ResourceState<T> {
  const [data, setData] = useState<T>(initialData);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);
  const [version, setVersion] = useState(0);

  const refresh = useCallback(() => {
    setVersion((current) => current + 1);
  }, []);

  useEffect(() => {
    let active = true;

    setLoading(true);
    setError(null);

    load()
      .then((nextData) => {
        if (active) {
          setData(nextData);
        }
      })
      .catch((reason: unknown) => {
        if (active) {
          setError(reason instanceof Error ? reason : new Error('Request failed'));
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [...dependencies, version]);

  return { data, loading, error, refresh };
}

export function useProducts(role: UserRole, params: ProductListParams = {}) {
  return useResource<Product[]>(
    () => (role === 'buyer' ? productService.listProducts(params) : Promise.resolve([])),
    [role, params.category, params.search],
    []
  );
}

export function useProduct(productId: string) {
  return useResource<Product | null>(
    () => productService.getProduct(productId),
    [productId],
    null
  );
}

export function useCategories() {
  return useResource<Category[]>(() => productService.listCategories(), [], []);
}

export function useOrders(role: UserRole) {
  return useResource<Order[]>(() => orderService.listOrders(role), [role], []);
}

export function useOrder(orderId: string) {
  return useResource<Order | null>(() => orderService.getOrder(orderId), [orderId], null);
}

export function useConversations() {
  return useResource<Conversation[]>(
    () => messageService.listConversations(),
    [],
    []
  );
}

export function useConversation(conversationId: string) {
  return useResource<Conversation | null>(
    () => messageService.getConversation(conversationId),
    [conversationId],
    null
  );
}

export function useCompany() {
  return useResource<CompanyProfile | null>(() => userService.getCompany(), [], null);
}

export function useSupplierDashboard(role: UserRole) {
  return useResource<{ stats: DashboardStat[]; quickActions: QuickAction[] }>(
    () =>
      role === 'supplier'
        ? dashboardService.getSupplierDashboard()
        : Promise.resolve({ stats: [], quickActions: [] }),
    [role],
    { stats: [], quickActions: [] }
  );
}
