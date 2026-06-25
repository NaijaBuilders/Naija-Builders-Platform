import type {
  NotificationPreference,
  NotificationPreferencesResponse,
  QuietHours,
} from '../types';
import { apiClient, handleServiceError } from './apiClient';

export const notificationService = {
  async getPreferences(): Promise<NotificationPreferencesResponse> {
    try {
      const response = await apiClient.get<NotificationPreferencesResponse>(
        '/notification-preferences'
      );
      return response.data;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async updatePreferences(
    preferences: NotificationPreference[],
    quietHours: QuietHours
  ): Promise<NotificationPreferencesResponse> {
    try {
      const response = await apiClient.post<NotificationPreferencesResponse>(
        '/notification-preferences',
        { preferences, quiet_hours: quietHours }
      );
      return response.data;
    } catch (error) {
      handleServiceError(error);
    }
  },
};
