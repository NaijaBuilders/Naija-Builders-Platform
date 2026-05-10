import type {
  SupplierBankDetailsPayload,
  SupplierBusinessDetailsPayload,
  SupplierIdentityVerificationPayload,
  SupplierOnboardingApplication,
} from '../types';
import { apiClient, handleServiceError } from './apiClient';

type OnboardingResponse = {
  application: SupplierOnboardingApplication;
  message?: string;
};

function appendIfPresent(formData: FormData, key: string, value?: string) {
  if (value && value.trim()) {
    formData.append(key, value.trim());
  }
}

function fileFromUri(uri: string, fallbackName: string) {
  const extension = uri.split('.').pop()?.split('?')[0] || 'jpg';
  const lowerExtension = extension.toLowerCase();
  const type =
    lowerExtension === 'png'
      ? 'image/png'
      : lowerExtension === 'webp'
        ? 'image/webp'
        : 'image/jpeg';

  return {
    name: `${fallbackName}.${lowerExtension}`,
    type,
    uri,
  } as unknown as Blob;
}

export const supplierOnboardingService = {
  async getStatus(): Promise<SupplierOnboardingApplication> {
    try {
      const response = await apiClient.get<OnboardingResponse>(
        '/supplier/onboarding/status'
      );
      return response.data.application;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async submitBusinessDetails(
    payload: SupplierBusinessDetailsPayload
  ): Promise<SupplierOnboardingApplication> {
    try {
      const response = await apiClient.post<OnboardingResponse>(
        '/supplier/onboarding/business-details',
        payload
      );
      return response.data.application;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async submitIdentityVerification(
    payload: SupplierIdentityVerificationPayload
  ): Promise<SupplierOnboardingApplication> {
    try {
      const formData = new FormData();
      appendIfPresent(formData, 'bvn', payload.bvn);
      appendIfPresent(formData, 'nin', payload.nin);
      appendIfPresent(formData, 'id_document_type', payload.id_document_type);

      if (payload.selfieUri) {
        formData.append('selfie', fileFromUri(payload.selfieUri, 'selfie'));
      }

      if (payload.idDocumentUri) {
        formData.append(
          'id_document',
          fileFromUri(payload.idDocumentUri, 'id-document')
        );
      }

      const response = await apiClient.post<OnboardingResponse>(
        '/supplier/onboarding/identity-verification',
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      return response.data.application;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async submitBankDetails(
    payload: SupplierBankDetailsPayload
  ): Promise<SupplierOnboardingApplication> {
    try {
      const response = await apiClient.post<OnboardingResponse>(
        '/supplier/onboarding/bank-details',
        payload
      );
      return response.data.application;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async submitForReview(): Promise<SupplierOnboardingApplication> {
    try {
      const response = await apiClient.post<OnboardingResponse>(
        '/supplier/onboarding/submit'
      );
      return response.data.application;
    } catch (error) {
      handleServiceError(error);
    }
  },
};
