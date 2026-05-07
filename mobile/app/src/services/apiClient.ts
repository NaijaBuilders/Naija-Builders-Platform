import AsyncStorage from '@react-native-async-storage/async-storage';
import axios, {
  AxiosError,
  type InternalAxiosRequestConfig,
} from 'axios';

const DEFAULT_API_BASE_URL =
  'https://api-dev.naijabuilders.com/api/mobile';
const STAGING_API_HOST = 'api-dev.naijabuilders.com';
const AUTH_TOKEN_STORAGE_KEY = 'naijabuilders.mobile.auth_token';
const API_BASE_URL_STORAGE_KEY = 'naijabuilders.mobile.api_base_url';
const LEGACY_DEV_API_BASE_URLS = new Set([
  'http://192.168.0.51:8081/api/mobile',
  'http://192.168.0.51:8080/api/mobile',
  'http://192.168.0.51/shop/legacy/Naijabuilders/laravel-app/public/api/mobile',
]);

function normalizeApiBaseUrl(url: string) {
  let normalizedUrl = url.trim().replace(/\/+$/, '');

  if (!normalizedUrl) {
    return DEFAULT_API_BASE_URL;
  }

  if (normalizedUrl.startsWith(STAGING_API_HOST)) {
    normalizedUrl = `https://${normalizedUrl}`;
  }

  if (normalizedUrl.startsWith(`http://${STAGING_API_HOST}`)) {
    normalizedUrl = normalizedUrl.replace('http://', 'https://');
  }

  const apiBaseMarker = '/api/mobile';
  const apiBaseIndex = normalizedUrl.indexOf(apiBaseMarker);
  if (apiBaseIndex >= 0) {
    normalizedUrl = normalizedUrl.slice(
      0,
      apiBaseIndex + apiBaseMarker.length
    );
  } else if (normalizedUrl === `https://${STAGING_API_HOST}`) {
    normalizedUrl = DEFAULT_API_BASE_URL;
  }

  if (
    LEGACY_DEV_API_BASE_URLS.has(normalizedUrl) ||
    /^https?:\/\/192\.168\.0\.51(?::\d+)?\/api\/mobile$/.test(normalizedUrl)
  ) {
    return DEFAULT_API_BASE_URL;
  }

  return normalizedUrl;
}

export const API_BASE_URL = normalizeApiBaseUrl(
  process.env.EXPO_PUBLIC_API_BASE_URL || DEFAULT_API_BASE_URL
);

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 30000,
});

let authToken: string | null = null;
let apiBaseUrl = API_BASE_URL;
let apiBaseUrlHydrated = false;

async function hydrateApiBaseUrl() {
  if (apiBaseUrlHydrated) {
    return apiBaseUrl;
  }

  const storedUrl = await AsyncStorage.getItem(API_BASE_URL_STORAGE_KEY);
  const normalizedStoredUrl = storedUrl ? normalizeApiBaseUrl(storedUrl) : '';

  if (normalizedStoredUrl && normalizedStoredUrl === API_BASE_URL) {
    await AsyncStorage.removeItem(API_BASE_URL_STORAGE_KEY);
  }

  apiBaseUrl = normalizedStoredUrl || API_BASE_URL;
  apiClient.defaults.baseURL = apiBaseUrl;
  apiBaseUrlHydrated = true;

  return apiBaseUrl;
}

apiClient.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  config.baseURL = await hydrateApiBaseUrl();

  if (!authToken) {
    authToken = await AsyncStorage.getItem(AUTH_TOKEN_STORAGE_KEY);
  }

  if (authToken) {
    config.headers.Authorization = `Bearer ${authToken}`;
  }

  return config;
});

export function getCurrentApiBaseUrl() {
  return apiBaseUrl;
}

export async function getApiBaseUrl() {
  return hydrateApiBaseUrl();
}

export function getApiOrigin() {
  return apiBaseUrl.replace(/\/api\/mobile\/?$/, '');
}

export async function setApiBaseUrl(nextUrl: string) {
  const normalizedUrl = normalizeApiBaseUrl(nextUrl || API_BASE_URL);
  apiBaseUrl = normalizedUrl;
  apiBaseUrlHydrated = true;
  apiClient.defaults.baseURL = normalizedUrl;

  if (normalizedUrl === API_BASE_URL) {
    await AsyncStorage.removeItem(API_BASE_URL_STORAGE_KEY);
  } else {
    await AsyncStorage.setItem(API_BASE_URL_STORAGE_KEY, normalizedUrl);
  }

  return normalizedUrl;
}

export class ApiServiceError extends Error {
  status?: number;

  constructor(message: string, status?: number) {
    super(message);
    this.name = 'ApiServiceError';
    this.status = status;
  }
}

export function handleServiceError(error: unknown): never {
  if (error instanceof AxiosError) {
    if (error.code === 'ECONNABORTED' || /timeout/i.test(error.message)) {
      throw new ApiServiceError(
        `Could not reach the backend at ${apiBaseUrl}. Try a tunnel URL or make sure your phone can open the API URL in Safari.`
      );
    }

    const message =
      typeof error.response?.data === 'object' &&
      error.response?.data &&
      'message' in error.response.data
        ? String(error.response.data.message)
        : error.message;

    throw new ApiServiceError(message, error.response?.status);
  }

  if (error instanceof Error) {
    throw new ApiServiceError(error.message);
  }

  throw new ApiServiceError('Unexpected API error');
}

export async function setAuthToken(token: string | null) {
  authToken = token;

  if (token) {
    await AsyncStorage.setItem(AUTH_TOKEN_STORAGE_KEY, token);
  } else {
    await AsyncStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
  }
}

export async function getStoredAuthToken() {
  if (!authToken) {
    authToken = await AsyncStorage.getItem(AUTH_TOKEN_STORAGE_KEY);
  }

  return authToken;
}
