import type {
  ServiceOption,
  ServiceRequestPayload,
  ServiceRequestResult,
} from '../types';
import { apiClient, handleServiceError } from './apiClient';

type LaravelServiceOptionsResponse = {
  service_types?: ServiceOption[];
  budget_ranges?: ServiceOption[];
};

type LaravelServiceRequestResponse = {
  message?: string;
  service_request?: {
    id?: string | number;
    service_type?: string;
    status?: string;
    created_at?: string;
  };
};

export const fallbackServiceTypes: ServiceOption[] = [
  { value: 'architect', label: 'Architect' },
  { value: 'site_engineer', label: 'Site Engineer' },
  { value: 'structural_engineer', label: 'Structural Engineer' },
  { value: 'quantity_surveyor', label: 'Quantity Surveyor' },
  { value: 'project_manager', label: 'Project Manager' },
  { value: 'builder_contractor', label: 'Builder / Contractor' },
  { value: 'land_surveyor', label: 'Land Surveyor' },
  { value: 'interior_designer', label: 'Interior Designer' },
  { value: 'electrician', label: 'Electrician' },
  { value: 'plumber', label: 'Plumber' },
  { value: 'tiler', label: 'Tiler' },
  { value: 'painter', label: 'Painter' },
];

export const fallbackBudgetRanges: ServiceOption[] = [
  { value: 'under_500k', label: 'Under NGN 500,000' },
  { value: '500k_1m', label: 'NGN 500,000 - NGN 1,000,000' },
  { value: '1m_5m', label: 'NGN 1,000,000 - NGN 5,000,000' },
  { value: '5m_10m', label: 'NGN 5,000,000 - NGN 10,000,000' },
  { value: 'above_10m', label: 'Above NGN 10,000,000' },
  { value: 'not_sure', label: 'Not sure yet' },
];

export const serviceRequestService = {
  async options(): Promise<{
    serviceTypes: ServiceOption[];
    budgetRanges: ServiceOption[];
  }> {
    try {
      const response =
        await apiClient.get<LaravelServiceOptionsResponse>('/services/options');

      return {
        serviceTypes:
          response.data.service_types && response.data.service_types.length > 0
            ? response.data.service_types
            : fallbackServiceTypes,
        budgetRanges:
          response.data.budget_ranges && response.data.budget_ranges.length > 0
            ? response.data.budget_ranges
            : fallbackBudgetRanges,
      };
    } catch {
      return {
        serviceTypes: fallbackServiceTypes,
        budgetRanges: fallbackBudgetRanges,
      };
    }
  },

  async create(payload: ServiceRequestPayload): Promise<ServiceRequestResult> {
    try {
      const response =
        await apiClient.post<LaravelServiceRequestResponse>('/services/requests', {
          service_type: payload.serviceType,
          project_title: payload.projectTitle,
          project_location: payload.projectLocation,
          project_description: payload.projectDescription,
          budget_range: payload.budgetRange || undefined,
          preferred_start_date: payload.preferredStartDate || undefined,
          contact_name: payload.contactName,
          contact_phone: payload.contactPhone,
          contact_email: payload.contactEmail,
        });

      const serviceRequest = response.data.service_request ?? {};

      return {
        id: String(serviceRequest.id ?? ''),
        serviceType: String(serviceRequest.service_type ?? payload.serviceType),
        status: String(serviceRequest.status ?? 'new'),
        createdAt: serviceRequest.created_at,
      };
    } catch (error) {
      handleServiceError(error);
    }
  },
};
