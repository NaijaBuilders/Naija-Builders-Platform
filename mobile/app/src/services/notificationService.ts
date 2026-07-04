import { Platform } from 'react-native';
import type {
  AppNotificationItem,
  NotificationPreference,
  NotificationPreferencesResponse,
  QuietHours,
} from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelNotification = {
  id?: number | string;
  event_key?: string;
  title?: string;
  body?: string | null;
  data?: Record<string, string> | null;
  read?: boolean;
  created_at?: string | null;
};

type LaravelInboxResponse = {
  notifications?: LaravelNotification[];
  unread_count?: number;
};

export const notificationService = {
  async getInbox(): Promise<{
    notifications: AppNotificationItem[];
    unreadCount: number;
  }> {
    try {
      const response = await apiClient.get<LaravelInboxResponse>('/notifications');

      return {
        notifications: (response.data.notifications ?? []).map((item) => ({
          id: String(item.id ?? ''),
          eventKey: String(item.event_key ?? 'general'),
          title: String(item.title ?? ''),
          body: String(item.body ?? ''),
          data: item.data ?? {},
          read: Boolean(item.read),
          createdAt: String(item.created_at ?? ''),
        })),
        unreadCount: Number(response.data.unread_count ?? 0),
      };
    } catch (error) {
      handleServiceError(error);
    }
  },

  async markRead(notificationId?: string): Promise<void> {
    try {
      await apiClient.post('/notifications/read', {
        notification_id: notificationId ? Number(notificationId) : undefined,
      });
    } catch (error) {
      handleServiceError(error);
    }
  },

  /**
   * Register this device for push notifications. Best-effort: in Expo Go or
   * without a configured EAS project this silently no-ops, so the app works
   * the same before push credentials are set up.
   */
  async registerDeviceForPush(): Promise<void> {
    try {
      const Notifications = await import('expo-notifications');
      const permissions = await Notifications.getPermissionsAsync();

      let status = permissions.status;
      if (status !== 'granted') {
        status = (await Notifications.requestPermissionsAsync()).status;
      }

      if (status !== 'granted') {
        return;
      }

      const token = (await Notifications.getExpoPushTokenAsync()).data;
      if (!token) {
        return;
      }

      await apiClient.post('/device-tokens', {
        token,
        platform: Platform.OS === 'ios' || Platform.OS === 'android' ? Platform.OS : 'unknown',
      });
    } catch {
      // Push tokens are unavailable in Expo Go / without EAS credentials.
    }
  },
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
