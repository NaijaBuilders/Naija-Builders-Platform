import type {
  Category,
  CompanyProfile,
  Conversation,
  ConversationMessage,
  DashboardStat,
  Product,
  SignupAccountType,
  UserProfile,
  UserRole,
} from '../types';
import { capitalizeWords } from '../utils/format';
import { getApiOrigin } from './apiClient';

const fallbackImage = require('../../assets/images/dashboard-materials.jpg');

type LaravelUser = {
  id?: number | string;
  name?: string;
  full_name?: string;
  email?: string;
  role?: string;
  phone?: string;
  company?: string;
  location?: string;
  business_category?: string;
  business_address?: string;
  is_verified_badge?: boolean | number;
  kyc_status?: string;
  offers_services?: boolean | number;
  service_category?: string | null;
  email_confirmed?: boolean | number;
  phone_confirmed?: boolean | number;
  created_at?: string;
  updated_at?: string;
};

type LaravelMaterial = {
  id?: number | string;
  supplier_id?: number | string;
  name?: string;
  category?: string;
  price?: number | string;
  stock_qty?: number | string;
  description?: string;
  created_at?: string;
  updated_at?: string;
  company?: string;
  full_name?: string;
  location?: string;
  image_path?: string;
  price_unit?: string;
  is_verified_badge?: boolean | number;
  product_rating_avg?: number | string | null;
  supplier_rating_avg?: number | string | null;
};

type LaravelMaterialImage = {
  image_path?: string;
};

type LaravelContact = {
  id?: number | string;
  full_name?: string;
  company?: string;
  last_message?: string;
  last_message_at?: string | null;
  unread_count?: number | string;
};

type LaravelThreadMessage = {
  id?: number | string;
  sender_id?: number | string;
  content?: string;
  created_at?: string;
};

type LaravelSettings = {
  notifications_push?: boolean;
  notifications_email?: boolean;
  compact_dashboard?: boolean;
};

export function normalizeRole(role?: string): UserRole {
  return role === 'supplier' ? 'supplier' : 'buyer';
}

export function toLaravelAccountType(role: SignupAccountType) {
  if (role === 'service_provider') {
    return 'service_provider';
  }

  return role === 'supplier' ? 'supplier' : 'builder';
}

export function assetSource(path?: string) {
  if (!path) {
    return fallbackImage;
  }

  if (/^https?:\/\//i.test(path)) {
    return { uri: path };
  }

  return { uri: `${getApiOrigin()}/${path.replace(/^\/+/, '')}` };
}

export function mapLaravelUser(user: LaravelUser): UserProfile {
  const timestamp = new Date().toISOString();
  const name = String(user.name ?? user.full_name ?? 'User');

  return {
    id: String(user.id ?? ''),
    name: capitalizeWords(name),
    email: String(user.email ?? ''),
    role: normalizeRole(String(user.role ?? 'builder')),
    company: user.company ? String(user.company) : undefined,
    phone: user.phone ? String(user.phone) : undefined,
    location: user.location ? String(user.location) : undefined,
    kyc_status: user.kyc_status ? String(user.kyc_status) : undefined,
    offers_services:
      user.offers_services === true || Number(user.offers_services) === 1,
    service_category: user.service_category ? String(user.service_category) : null,
    is_verified_badge:
      user.is_verified_badge === true || Number(user.is_verified_badge) === 1,
    email_confirmed:
      user.email_confirmed === true || Number(user.email_confirmed) === 1,
    phone_confirmed:
      user.phone_confirmed === true || Number(user.phone_confirmed) === 1,
    created_at: String(user.created_at ?? timestamp),
    updated_at: String(user.updated_at ?? timestamp),
  };
}

export function mapLaravelMaterial(
  material: LaravelMaterial,
  images: LaravelMaterialImage[] = []
): Product {
  const id = String(material.id ?? '');
  const supplierCompany = String(material.company ?? '').trim();
  const supplierFullName = String(material.full_name ?? '').trim();
  const supplierName =
    supplierCompany || (supplierFullName ? capitalizeWords(supplierFullName) : 'Supplier');
  const imageSources = images
    .map((image) => image.image_path)
    .filter(Boolean)
    .map((path) => assetSource(path));
  const primaryImage = imageSources[0] || assetSource(material.image_path);
  const rating = Number(material.product_rating_avg ?? material.supplier_rating_avg ?? 0);
  const stockCount = Number(material.stock_qty ?? 0);
  const normalizedRating = Number.isFinite(rating) && rating > 0 ? rating : 0;

  return {
    id,
    name: String(material.name ?? 'Unnamed material'),
    slug: String(material.name ?? id).toLowerCase().replace(/\s+/g, '-'),
    description: String(material.description ?? 'No description provided.'),
    supplierName,
    supplier: {
      id: String(material.supplier_id ?? ''),
      name: supplierFullName ? capitalizeWords(supplierFullName) : supplierName,
      company: supplierName,
      location: String(material.location ?? ''),
      rating: normalizedRating,
      response_time: 'Contact supplier',
      verified: material.is_verified_badge === true || Number(material.is_verified_badge) === 1,
    },
    category: String(material.category ?? 'General'),
    price: Number(material.price ?? 0),
    unit: String(material.price_unit ?? 'item'),
    location: String(material.location ?? ''),
    image: primaryImage,
    images: imageSources.length > 0 ? imageSources : [primaryImage],
    rating: normalizedRating,
    inStock: stockCount > 0,
    stock_count: stockCount,
    created_at: String(material.created_at ?? new Date().toISOString()),
    updated_at: String(material.updated_at ?? material.created_at ?? new Date().toISOString()),
  };
}

export function mapLaravelCategories(values: Array<Category | { category?: string }>) {
  return values
    .map((value) => {
      const name = 'name' in value ? value.name : value.category;

      if (!name) {
        return null;
      }

      return {
        id: `cat-${String(name).toLowerCase().replace(/\s+/g, '-')}`,
        name: String(name),
        slug: String(name).toLowerCase().replace(/\s+/g, '-'),
        description: `${name} materials`,
      };
    })
    .filter((value): value is Category => Boolean(value));
}

export function mapLaravelContact(
  contact: LaravelContact | null | undefined,
  threadMessages: LaravelThreadMessage[] = [],
  currentUserId?: string
): Conversation {
  const contactData = contact ?? {};
  const id = String(contactData.id ?? '');

  return {
    id,
    participantName: capitalizeWords(String(contactData.full_name ?? 'Contact')),
    company: String(contactData.company ?? 'Company not listed'),
    lastMessage: String(contactData.last_message ?? 'No conversation.'),
    lastMessageAt: contactData.last_message_at ? String(contactData.last_message_at) : 'Now',
    unreadCount: Number(contactData.unread_count ?? 0),
    messages: threadMessages.map((message): ConversationMessage => {
      const senderId = String(message.sender_id ?? '');

      return {
        id: String(message.id ?? `${id}-${message.created_at}`),
        sender: currentUserId
          ? senderId === String(currentUserId)
            ? 'me'
            : 'them'
          : senderId === id
            ? 'them'
            : 'me',
        body: String(message.content ?? ''),
        created_at: String(message.created_at ?? new Date().toISOString()),
      };
    }),
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };
}

export function mapLaravelCompany(profileUser: LaravelUser): CompanyProfile {
  const timestamp = new Date().toISOString();

  return {
    id: String(profileUser.id ?? 'company-profile'),
    name: String(profileUser.company ?? 'No company listed'),
    industry: String(profileUser.business_category ?? 'Not set'),
    address: String(profileUser.business_address ?? 'Not set'),
    city: String(profileUser.location ?? 'Not set'),
    verified:
      profileUser.is_verified_badge === true ||
      Number(profileUser.is_verified_badge) === 1,
    created_at: String(profileUser.created_at ?? timestamp),
    updated_at: String(profileUser.updated_at ?? timestamp),
  };
}

export function mapLaravelSettings(settings: LaravelSettings) {
  return {
    push_notifications: Boolean(settings.notifications_push),
    email_updates: Boolean(settings.notifications_email),
    compact_cards: Boolean(settings.compact_dashboard),
  };
}

export function mapSupplierMetrics(metrics: Record<string, unknown>): DashboardStat[] {
  return [
    {
      id: 'stat-listings',
      label: 'Listings',
      value: String(metrics.activeListings ?? metrics.totalListings ?? 0),
      change: `${metrics.totalListings ?? 0} total`,
      tone: 'primary',
    },
    {
      id: 'stat-stock',
      label: 'Stock',
      value: String(metrics.totalStock ?? 0),
      change: `${metrics.lowStockListings ?? 0} low stock`,
      tone: 'warning',
    },
    {
      id: 'stat-inventory',
      label: 'Inventory',
      value: `NGN ${Math.round(Number(metrics.inventoryValue ?? 0)).toLocaleString('en-NG')}`,
      change: `${metrics.inStockListings ?? 0} in-stock listings`,
      tone: 'success',
    },
    {
      id: 'stat-messages',
      label: 'Messages',
      value: String(metrics.unreadMessages ?? 0),
      change: 'Unread messages',
      tone: 'neutral',
    },
  ];
}
