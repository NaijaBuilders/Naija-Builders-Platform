import type { ImageSourcePropType } from 'react-native';

export type UserRole = 'buyer' | 'supplier';

export type StatusTone = 'primary' | 'success' | 'warning' | 'danger' | 'neutral';

export type ApiResponse<T> = {
  data: T;
  message?: string;
};

export type AuthSession = {
  token: string;
  user: UserProfile;
};

export type ApiPaginatedResponse<T> = ApiResponse<T[]> & {
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};

export interface UserProfile {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  company?: string;
  phone?: string;
  location?: string;
  created_at: string;
  updated_at: string;
}

export interface CompanyProfile {
  id: string;
  name: string;
  industry: string;
  address: string;
  city: string;
  verified: boolean;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  description: string;
}

export interface SupplierSummary {
  id: string;
  name: string;
  company: string;
  location: string;
  rating: number;
  verified: boolean;
  response_time: string;
}

export interface Product {
  id: string;
  name: string;
  slug: string;
  description: string;
  category: string;
  price: number;
  unit: string;
  location: string;
  image: ImageSourcePropType;
  images: ImageSourcePropType[];
  rating: number;
  inStock: boolean;
  stock_count: number;
  supplierName: string;
  supplier: SupplierSummary;
  created_at: string;
  updated_at: string;
}

export type OrderStatus = 'Pending' | 'Processing' | 'Delivered' | 'Cancelled';

export interface Order {
  id: string;
  title: string;
  reference: string;
  supplierName: string;
  buyerName: string;
  total: number;
  itemCount: number;
  placedAt: string;
  status: OrderStatus;
  visibleTo: UserRole | 'both';
  delivery_address: string;
  items: Array<{
    id: string;
    name: string;
    quantity: number;
    unit_price: number;
  }>;
  created_at: string;
  updated_at: string;
}

export interface ConversationMessage {
  id: string;
  sender: 'me' | 'them';
  body: string;
  created_at: string;
}

export interface Conversation {
  id: string;
  participantName: string;
  company: string;
  lastMessage: string;
  lastMessageAt: string;
  unreadCount: number;
  messages: ConversationMessage[];
  created_at: string;
  updated_at: string;
}

export interface DashboardStat {
  id: string;
  label: string;
  value: string;
  change: string;
  tone: StatusTone;
}

export type QuickActionTarget =
  | 'createListing'
  | 'manageStock'
  | 'orders'
  | 'messages';

export interface QuickAction {
  id: string;
  title: string;
  description: string;
  icon: string;
  target: QuickActionTarget;
}

export type ListingStatus = 'active' | 'inactive' | 'out_of_stock';

export interface SupplierListing {
  id: string;
  name: string;
  category: string;
  price: number;
  unit: string;
  stockCount: number;
  status: ListingStatus;
  negotiable: boolean;
  created_at: string;
}

export interface SupplierListingCreatePayload {
  name: string;
  category: string;
  description: string;
  price: string;
  price_unit: string;
  stock_qty: string;
  status: ListingStatus;
  is_negotiable: boolean;
  imageUris: string[];
}

export interface UserPreferences {
  push_notifications: boolean;
  email_updates: boolean;
  compact_cards: boolean;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface SignupPayload {
  name: string;
  email: string;
  phone: string;
  location: string;
  password: string;
  confirmPassword: string;
  role: UserRole;
  company?: string;
  termsAccepted: boolean;
}
