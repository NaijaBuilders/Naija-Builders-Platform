export type RootStackParamList = {
  Auth: undefined;
  Main: undefined;
};

export type AuthStackParamList = {
  Login: undefined;
  Signup: undefined;
  ForgotPassword: undefined;
};

export type MainTabParamList = {
  Home: undefined;
  Browse: undefined;
  Orders: undefined;
  Messages: undefined;
  Profile: undefined;
};

export type HomeStackParamList = {
  HomeMain: undefined;
  ProductDetail: { productId: string };
};

export type BrowseStackParamList = {
  BrowseMain: undefined;
  ProductDetail: { productId: string };
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
  Settings: undefined;
};
