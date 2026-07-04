import type {
  CompanyProfile,
  EditableProfile,
  EditProfilePayload,
  UserPreferences,
  UserProfile,
} from '../types';
import { apiClient, getApiOrigin, handleServiceError } from './apiClient';
import {
  mapLaravelCompany,
  mapLaravelSettings,
  mapLaravelUser,
} from './adapters';

type LaravelEditProfile = {
  user?: {
    username?: string | null;
    email?: string | null;
    phone?: string | null;
    company?: string | null;
    business_category?: string | null;
    location?: string | null;
    business_address?: string | null;
    business_description?: string | null;
    profile_image_path?: string | null;
  };
  first_name?: string;
  last_name?: string;
};

export type ProfileStats = {
  role: 'buyer' | 'supplier';
  ordersCount: number;
  savedCount: number;
  reviewsGiven: number;
  listingsCount: number;
  ratingAvg: number | null;
  ratingCount: number;
};

export type SupportContent = {
  prompts: string[];
  rules: Array<{ keys: string[]; reply: string }>;
};

export const userService = {
  async getProfileStats(): Promise<ProfileStats> {
    try {
      const response = await apiClient.get<{
        role?: string;
        orders_count?: number;
        saved_count?: number;
        reviews_given?: number;
        listings_count?: number;
        rating_avg?: number | null;
        rating_count?: number;
      }>('/profile/stats');

      return {
        role: response.data.role === 'supplier' ? 'supplier' : 'buyer',
        ordersCount: Number(response.data.orders_count ?? 0),
        savedCount: Number(response.data.saved_count ?? 0),
        reviewsGiven: Number(response.data.reviews_given ?? 0),
        listingsCount: Number(response.data.listings_count ?? 0),
        ratingAvg:
          response.data.rating_avg === null ||
          response.data.rating_avg === undefined
            ? null
            : Number(response.data.rating_avg),
        ratingCount: Number(response.data.rating_count ?? 0),
      };
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getSupportContent(): Promise<SupportContent> {
    try {
      const response = await apiClient.get<Partial<SupportContent>>('/support');

      return {
        prompts: response.data.prompts ?? [],
        rules: response.data.rules ?? [],
      };
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getCurrentUser(): Promise<UserProfile> {
    try {
      const response = await apiClient.get<{ user: unknown }>('/user');
      return mapLaravelUser(response.data.user as Parameters<typeof mapLaravelUser>[0]);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getEditableProfile(): Promise<EditableProfile> {
    try {
      const response = await apiClient.get<LaravelEditProfile>('/profile');
      const user = response.data.user ?? {};
      const rawImage = String(user.profile_image_path ?? '');
      const profileImage = rawImage
        ? /^https?:\/\//i.test(rawImage)
          ? rawImage
          : `${getApiOrigin()}/${rawImage.replace(/^\/+/, '')}`
        : undefined;

      return {
        firstName: String(response.data.first_name ?? ''),
        lastName: String(response.data.last_name ?? ''),
        username: String(user.username ?? ''),
        email: String(user.email ?? ''),
        phone: String(user.phone ?? ''),
        company: String(user.company ?? ''),
        businessCategory: String(user.business_category ?? ''),
        location: String(user.location ?? ''),
        businessAddress: String(user.business_address ?? ''),
        businessDescription: String(user.business_description ?? ''),
        profileImage,
      };
    } catch (error) {
      handleServiceError(error);
    }
  },

  async updateProfile(payload: EditProfilePayload): Promise<UserProfile> {
    try {
      const form = new FormData();
      form.append('first_name', payload.firstName);
      form.append('last_name', payload.lastName);
      form.append('username', payload.username);
      form.append('email', payload.email);
      form.append('phone', payload.phone);
      form.append('location', payload.location);

      if (payload.company !== undefined) {
        form.append('company', payload.company);
      }
      if (payload.businessCategory !== undefined) {
        form.append('business_category', payload.businessCategory);
      }
      if (payload.businessAddress !== undefined) {
        form.append('business_address', payload.businessAddress);
      }
      if (payload.businessDescription !== undefined) {
        form.append('business_description', payload.businessDescription);
      }

      if (payload.photoUri) {
        const fileName = payload.photoUri.split('/').pop() || 'profile.jpg';
        const extension = (fileName.split('.').pop() || 'jpg').toLowerCase();
        const mimeType =
          extension === 'png'
            ? 'image/png'
            : extension === 'webp'
              ? 'image/webp'
              : 'image/jpeg';

        form.append('profile_picture', {
          name: fileName,
          type: mimeType,
          uri: payload.photoUri,
        } as unknown as Blob);
      }

      await apiClient.post('/profile', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      return this.getCurrentUser();
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
