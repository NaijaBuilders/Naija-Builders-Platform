import type { NavigatorScreenParams } from '@react-navigation/native';

export type RootStackParamList = {
  Auth: undefined;
  Main: undefined;
};

export type AuthStackParamList = {
  Login: undefined;
  Signup: undefined;
  ForgotPassword: undefined;
};

export type HomeStackParamList = {
  HomeMain: undefined;
  ProductDetail: { productId: string };
  HireService: undefined;
  Cart: undefined;
  Checkout: undefined;
  Notifications: undefined;
};

export type BrowseStackParamList = {
  BrowseMain: undefined;
  ProductDetail: { productId: string };
  CreateListing: undefined;
  Cart: undefined;
  Checkout: undefined;
};

export type OrdersStackParamList = {
  OrdersMain: undefined;
  OrderDetail: { orderId: string };
};

export type MessagesStackParamList = {
  MessagesMain: undefined;
  Chat: { conversationId: string };
};

export type ProfileStackParamList = {
  ProfileMain: undefined;
  EditProfile: undefined;
  Settings: undefined;
  NotificationSettings: undefined;
  SupplierOnboarding: undefined;
  BuyerVerification: undefined;
  SavedItems: undefined;
  Addresses: undefined;
  DataPrivacy: undefined;
  Terms: undefined;
};

export type MainTabParamList = {
  Home: NavigatorScreenParams<HomeStackParamList> | undefined;
  Browse: NavigatorScreenParams<BrowseStackParamList> | undefined;
  Orders: NavigatorScreenParams<OrdersStackParamList> | undefined;
  Messages: NavigatorScreenParams<MessagesStackParamList> | undefined;
  Profile: NavigatorScreenParams<ProfileStackParamList> | undefined;
};
