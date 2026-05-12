import type { EmailVerificationResult } from '../types';
import { apiClient, handleServiceError } from './apiClient';

type EmailVerificationResponse = {
  message: string;
  verification: EmailVerificationResult;
};

export const kycService = {
  async verifyEmail(email: string): Promise<EmailVerificationResponse> {
    try {
      const response = await apiClient.post<EmailVerificationResponse>(
        '/kyc/email-verification',
        { email }
      );

      return response.data;
    } catch (error) {
      handleServiceError(error);
    }
  },
};
