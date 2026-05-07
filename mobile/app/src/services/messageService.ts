import type { Conversation } from '../types';
import { apiClient, handleServiceError } from './apiClient';
import { mapLaravelContact } from './adapters';

type LaravelMessagesResponse = {
  contacts?: unknown[];
  selected_contact?: unknown;
  selected_contact_id?: number | string;
  thread_messages?: unknown[];
};

export const messageService = {
  async listConversations(): Promise<Conversation[]> {
    try {
      const response = await apiClient.get<LaravelMessagesResponse>('/messages');

      return (response.data.contacts ?? []).map((contact) =>
        mapLaravelContact(
          contact as Parameters<typeof mapLaravelContact>[0],
          String((contact as { id?: string | number }).id ?? '') ===
            String(response.data.selected_contact_id ?? '')
            ? (response.data.thread_messages as Parameters<typeof mapLaravelContact>[1])
            : []
        )
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async getConversation(conversationId: string): Promise<Conversation> {
    try {
      const response = await apiClient.get<LaravelMessagesResponse>('/messages', {
        params: { contact_id: conversationId },
      });

      return mapLaravelContact(
        response.data.selected_contact as Parameters<typeof mapLaravelContact>[0],
        response.data.thread_messages as Parameters<typeof mapLaravelContact>[1]
      );
    } catch (error) {
      handleServiceError(error);
    }
  },

  async sendMessage(conversationId: string, content: string, materialId?: string) {
    try {
      await apiClient.post('/messages', {
        receiver_id: conversationId,
        content,
        material_id: materialId,
      });
    } catch (error) {
      handleServiceError(error);
    }
  },
};
