import { API_ORIGIN, MEDIA_BASE_URL } from '../services/api';

export const formatMoney = (value, currency = 'NGN') => {
  const numericValue = Number(value || 0);
  try {
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency,
      maximumFractionDigits: 2,
    }).format(numericValue);
  } catch (error) {
    return `${currency} ${numericValue.toLocaleString()}`;
  }
};

export const humanize = (value) =>
  String(value || '')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase());

export const imageUrl = (path) => {
  const cleanPath = String(path || '').trim();
  if (!cleanPath) {
    return '';
  }

  if (/^https?:\/\//i.test(cleanPath)) {
    return cleanPath;
  }

  const base = MEDIA_BASE_URL || API_ORIGIN;
  if (!base) {
    return cleanPath;
  }

  return `${base.replace(/\/$/, '')}/${cleanPath.replace(/^\//, '')}`;
};

export const supplierName = (item) => {
  const company = String(item?.company || '').trim();
  if (company) {
    return company;
  }

  return String(item?.full_name || item?.supplier_name || 'Supplier');
};

export const isSupplier = (user) => String(user?.role || '').toLowerCase() === 'supplier';

