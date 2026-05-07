import type { CompanyProfile, UserPreferences, UserProfile } from '../types';
import { apiClient, handleServiceError } from './apiClient';
import {
  mapLaravelCompany,
  mapLaravelSettings,
  mapLaravelUser,
} from './adapters';

export const userService = {
  async getCurrentUser(): Promise<UserProfile> {
    try {
      const response = await apiClient.get<{ user: unknown }>('/user');
      return mapLaravelUser(response.data.user as Parameters<typeof mapLaravelUser>[0]);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getCompany(): Promise<CompanyProfile> {
    try {
      const response = await apiClient.get<{ user: unknown }>('/profile');
      return mapLaravelCompany(
        response.data.user as Parameters<typeof mapLaravelCompany>[0]
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getPreferences(): Promise<UserPreferences> {
    try {
      const response = await apiClient.get<{ settings: unknown }>('/settings');
      return mapLaravelSettings(
        response.data.settings as Parameters<typeof mapLaravelSettings>[0]
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async updatePreferences(preferences: UserPreferences): Promise<UserPreferences> {
    try {
      const response = await apiClient.post<{ settings: unknown }>('/settings', {
        notifications_push: preferences.push_notifications ? '1' : '0',
        notifications_email: preferences.email_updates ? '1' : '0',
        compact_dashboard: preferences.compact_cards ? '1' : '0',
      });

      return mapLaravelSettings(
        response.data.settings as Parameters<typeof mapLaravelSettings>[0]
      );
    } catch (error) {
      handleServiceError(error);
    }
  },
};
