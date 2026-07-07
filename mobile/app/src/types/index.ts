import type { ImageSourcePropType } from 'react-native';

export type UserRole = 'buyer' | 'supplier';
export type SignupAccountType = UserRole | 'service_provider';

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
  username?: string;
  profileImage?: string;
  role: UserRole;
  company?: string;
  phone?: string;
  location?: string;
  kyc_status?: string;
  offers_services?: boolean;
  service_category?: string | null;
  is_verified_badge?: boolean;
  email_confirmed?: boolean;
  phone_confirmed?: boolean;
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
  /** Present only when loaded via the product detail endpoint. */
  detail?: ProductDetailExtras;
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
  supplierId?: string;
  buyerId?: string;
  total: number;
  itemCount: number;
  placedAt: string;
  status: OrderStatus;
  visibleTo: UserRole | 'both';
  delivery_address: string;
  verificationTier?: number;
  verificationStatus?: string;
  reviewStatus?: string | null;
  paymentProvider?: string | null;
  paymentMethodType?: string | null;
  paymentCurrency?: string | null;
  paymentAmount?: number | null;
  gatewayRiskLevel?: string | null;
  recipient?: {
    name: string;
    phone: string;
    relationship?: string | null;
  };
  delivery?: {
    status: string;
    photos: DeliveryPhoto[];
    otp_generated_at?: string | null;
    otp_confirmed_at?: string | null;
    dispute_window_ends_at?: string | null;
    dispute_status?: string | null;
    dispute_outcome?: string | null;
    escrow_release_at?: string | null;
  };
  items: Array<{
    id: string;
    name: string;
    quantity: number;
    unit_price: number;
  }>;
  created_at: string;
  updated_at: string;
}

export interface DeliveryPhoto {
  id: string;
  path: string;
  captured_at: string;
  gps_lat?: number | null;
  gps_lng?: number | null;
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
  profileImage?: string;
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
  image: ImageSourcePropType;
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

export interface EditableProfile {
  firstName: string;
  lastName: string;
  username: string;
  email: string;
  phone: string;
  company: string;
  businessCategory: string;
  location: string;
  businessAddress: string;
  businessDescription: string;
  profileImage?: string;
}

export interface EditProfilePayload {
  firstName: string;
  lastName: string;
  username: string;
  email: string;
  phone: string;
  location: string;
  company?: string;
  businessCategory?: string;
  businessAddress?: string;
  businessDescription?: string;
  photoUri?: string;
}

export type NotificationChannel = 'push' | 'email' | 'sms' | 'in_app';

export interface NotificationPreference {
  event_key: string;
  push: boolean;
  email: boolean;
  sms: boolean;
  in_app: boolean;
}

export interface QuietHours {
  enabled: boolean;
  from: string;
  to: string;
}

export interface NotificationPreferencesResponse {
  preferences: NotificationPreference[];
  quiet_hours: QuietHours;
}

export interface LoginPayload {
  login: string;
  password: string;
}

export interface SignupPayload {
  name: string;
  email: string;
  username?: string;
  phone: string;
  location: string;
  password: string;
  confirmPassword: string;
  role: SignupAccountType;
  company?: string;
  termsAccepted: boolean;
}

export interface ServiceOption {
  value: string;
  label: string;
}

export interface ServiceRequestPayload {
  serviceType: string;
  projectTitle: string;
  projectLocation: string;
  projectDescription: string;
  budgetRange?: string;
  preferredStartDate?: string;
  contactName: string;
  contactPhone: string;
  contactEmail: string;
}

export interface ServiceRequestResult {
  id: string;
  serviceType: string;
  status: string;
  createdAt?: string;
}

export type SupplierOnboardingStatus =
  | 'DRAFT'
  | 'SUBMITTED'
  | 'VERIFYING'
  | 'APPROVED'
  | 'REJECTED'
  | 'MANUAL_REVIEW'
  | 'MORE_INFO_REQUIRED'
  | 'SUSPENDED';

export interface SupplierBusinessDetailsPayload {
  cac_number: string;
  business_name: string;
  business_type: string;
  business_address: string;
  state: string;
  contact_name?: string;
  contact_email?: string;
  contact_phone?: string;
}

export interface SupplierIdentityVerificationPayload {
  bvn?: string;
  nin?: string;
  id_document_type?: string;
  selfieUri?: string;
  idDocumentUri?: string;
}

export interface SupplierBankDetailsPayload {
  bank_name: string;
  bank_code: string;
  account_number: string;
  account_name?: string;
}

export type EmailVerificationStatus = 'VERIFIED' | 'REVIEWING' | 'NOT_VERIFIED';

export interface EmailVerificationResult {
  status: EmailVerificationStatus;
  provider: string;
}

export type OtpChannel = 'email' | 'phone';

export interface BuyerIdSubmissionPayload {
  documentType: 'nin_slip' | 'international_passport' | 'drivers_licence';
  verifiedIdName?: string;
  idDocumentUri?: string;
  selfieUri?: string;
}

export interface CartItem {
  id: string;
  name: string;
  category: string;
  company: string;
  price: number;
  stockQty: number;
  quantity: number;
  imagePath: string;
  lineTotal: number;
}

export interface Cart {
  items: CartItem[];
  total: number;
}

export interface DeliveryAddress {
  id: string;
  label: string;
  contactName: string;
  contactPhone: string;
  state: string;
  lga: string;
  address: string;
  instructions: string;
  isDefault: boolean;
}

export interface DeliveryAddressPayload {
  label?: string;
  contactName?: string;
  contactPhone?: string;
  state?: string;
  lga?: string;
  address: string;
  instructions?: string;
  isDefault?: boolean;
}

export type PaymentMethodChoice = 'pay_on_delivery' | 'bank_transfer' | 'card';

export interface PlaceOrderPayload {
  items: Array<{ materialId: string; quantity: number }>;
  deliveryAddress: string;
  recipientName: string;
  recipientPhone: string;
  paymentMethodType: PaymentMethodChoice;
}

export interface PlaceOrderResult {
  orderId: string;
  reference: string;
  message: string;
  verificationStatus: string;
  nextAction: string | null;
}

export interface ProductReview {
  rating: number;
  reviewText: string;
  reviewerName: string;
  createdAt: string;
}

export interface ProductDetailExtras {
  isSaved: boolean;
  productRatingAvg: number | null;
  productRatingCount: number;
  supplierRatingAvg: number | null;
  supplierRatingCount: number;
  currentUserProductRating: number | null;
  reviews: ProductReview[];
}

export interface SavedProduct {
  id: string;
  name: string;
  category: string;
  price: number;
  priceUnit: string;
  company: string;
  savedAt: string;
}

export interface AppNotificationItem {
  id: string;
  eventKey: string;
  title: string;
  body: string;
  data: Record<string, string>;
  read: boolean;
  createdAt: string;
}

export interface SupplierOnboardingApplication {
  id: number;
  status: SupplierOnboardingStatus;
  current_stage: string;
  supplier_message?: string | null;
  more_info_message?: string | null;
  business: {
    cac_number?: string | null;
    business_name?: string | null;
    business_type?: string | null;
    state?: string | null;
  };
  identity: {
    bvn?: string | null;
    nin?: string | null;
    id_document_type?: string | null;
  };
  bank: {
    bank_name?: string | null;
    bank_code?: string | null;
    account_number?: string | null;
    account_name?: string | null;
  };
  submitted_at?: string | null;
  decided_at?: string | null;
  updated_at?: string | null;
}
