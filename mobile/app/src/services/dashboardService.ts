import type { DashboardStat, QuickAction } from '../types';
import { apiClient, handleServiceError } from './apiClient';
import { mapSupplierMetrics } from './adapters';

const supplierQuickActions: QuickAction[] = [
  {
    id: 'action-create-listing',
    title: 'Create listing',
    description: 'Add a material to your supplier catalog.',
    icon: 'add-circle-outline',
  },
  {
    id: 'action-manage-stock',
    title: 'Manage stock',
    description: 'Review inventory and low-stock materials.',
    icon: 'cube-outline',
  },
  {
    id: 'action-open-messages',
    title: 'Messages',
    description: 'Reply to buyer enquiries.',
    icon: 'chatbubble-ellipses-outline',
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
