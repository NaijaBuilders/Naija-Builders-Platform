import type {
  BuyerIdSubmissionPayload,
  EmailVerificationResult,
  OtpChannel,
} from '../types';
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

  async sendOtp(channel: OtpChannel): Promise<{ debug_code?: string | null }> {
    try {
      const response = await apiClient.post<{ otp: { debug_code?: string | null } }>(
        '/otp/send',
        { channel }
      );

      return response.data.otp;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async confirmOtp(channel: OtpChannel, otp: string): Promise<void> {
    try {
      await apiClient.post('/otp/confirm', { channel, otp });
    } catch (error) {
      handleServiceError(error);
    }
  },

  async submitBuyerId(payload: BuyerIdSubmissionPayload): Promise<{
    status: string;
    message: string;
  }> {
    try {
      const form = new FormData();
      form.append('document_type', payload.documentType);
      if (payload.verifiedIdName) {
        form.append('verified_id_name', payload.verifiedIdName);
      }
      if (payload.idDocumentUri) {
        form.append('id_document', filePart(payload.idDocumentUri, 'id-document.jpg'));
      }
      if (payload.selfieUri) {
        form.append('selfie', filePart(payload.selfieUri, 'selfie.jpg'));
      }

      const response = await apiClient.post<{
        message: string;
        verification: { status: string };
      }>('/kyc/buyer/id-document', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      return {
        status: response.data.verification.status,
        message: response.data.message,
      };
    } catch (error) {
      handleServiceError(error);
    }
  },
};

function filePart(uri: string, name: string) {
  return {
    uri,
    name,
    type: uri.toLowerCase().endsWith('.png') ? 'image/png' : 'image/jpeg',
  } as unknown as Blob;
}
