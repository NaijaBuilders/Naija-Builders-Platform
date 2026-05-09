import type { DashboardStat, QuickAction } from '../types';
import { apiClient, handleServiceError } from './apiClient';
import { mapSupplierMetrics } from './adapters';

const supplierQuickActions: QuickAction[] = [
  {
    id: 'action-create-listing',
    title: 'Create listing',
    description: 'Add a material to your supplier catalog.',
    icon: 'add-circle-outline',
    target: 'createListing',
  },
  {
    id: 'action-manage-stock',
    title: 'Manage stock',
    description: 'Review inventory and low-stock materials.',
    icon: 'cube-outline',
    target: 'manageStock',
  },
  {
    id: 'action-open-orders',
    title: 'Orders',
    description: 'Track paid orders and delivery progress.',
    icon: 'receipt-outline',
    target: 'orders',
  },
  {
    id: 'action-open-messages',
    title: 'Messages',
    description: 'Reply to buyer enquiries.',
    icon: 'chatbubble-ellipses-outline',
    target: 'messages',
  },
];

export const dashboardService = {
  async getSupplierDashboard(): Promise<{
    stats: DashboardStat[];
    quickActions: QuickAction[];
  }> {
    try {
      const response = await apiClient.get<{ metrics?: Record<string, unknown> }>(
        '/dashboard'
      );

      return {
        stats: mapSupplierMetrics(response.data.metrics ?? {}),
        quickActions: supplierQuickActions,
      };
    } catch (error) {
      handleServiceError(error);
    }
  },
};
