import axios from 'axios';

const configuredApiBase = process.env.EXPO_PUBLIC_API_BASE_URL || '';

export const API_BASE_URL = configuredApiBase || 'http://10.0.2.2:8000/api/mobile';
export const API_ORIGIN = API_BASE_URL.replace(/\/api\/mobile\/?$/, '');
export const MEDIA_BASE_URL = process.env.EXPO_PUBLIC_MEDIA_BASE_URL || API_ORIGIN;

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
  },
});

export const setAuthToken = (token) => {
  if (token) {
    apiClient.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    delete apiClient.defaults.headers.common.Authorization;
  }
};

export const api = {
  login: (payload) => apiClient.post('/login', payload),
  register: (payload) => apiClient.post('/register', payload),
  getUser: () => apiClient.get('/user'),
  logout: () => apiClient.post('/logout'),

  getMaterials: (params) => apiClient.get('/materials', { params }),
  getMaterial: (materialId) => apiClient.get(`/materials/${materialId}`),
  rateMaterial: (materialId, payload) => apiClient.post(`/materials/${materialId}/review`, payload),
  rateSupplier: (supplierId, payload) => apiClient.post(`/suppliers/${supplierId}/review`, payload),

  getListings: (params) => apiClient.get('/listings', { params }),
  createListing: (formData) =>
    apiClient.post('/listings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  deleteListing: (listingId) => apiClient.delete(`/listings/${listingId}`),

  getCart: () => apiClient.get('/cart'),
  addCartItem: (payload) => apiClient.post('/cart/items', payload),
  updateCartItem: (materialId, payload) => apiClient.put(`/cart/items/${materialId}`, payload),
  removeCartItem: (materialId) => apiClient.delete(`/cart/items/${materialId}`),

  getMessages: (params) => apiClient.get('/messages', { params }),
  sendMessage: (payload) => apiClient.post('/messages', payload),

  getProfile: () => apiClient.get('/profile'),
  updateProfile: (formData) =>
    apiClient.post('/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  getSettings: () => apiClient.get('/settings'),
  saveSettings: (payload) => apiClient.post('/settings', payload),

  getSavedProducts: (params) => apiClient.get('/saved-products', { params }),
  saveProduct: (payload) => apiClient.post('/saved-products', payload),
  removeSavedProduct: (materialId) => apiClient.delete(`/saved-products/${materialId}`),

  getSubscription: () => apiClient.get('/subscription'),
  updateSubscription: (payload) => apiClient.post('/subscription', payload),

  getDashboard: () => apiClient.get('/dashboard'),
  getDashboardAnalysis: () => apiClient.get('/dashboard/analysis'),
  getBuyerDashboard: () => apiClient.get('/dashboard/buyer'),

  getTerms: () => apiClient.get('/terms'),
  getSupport: () => apiClient.get('/support'),
  getForgotPassword: () => apiClient.get('/forgot-password'),
};
