import { createNativeStackNavigator } from '@react-navigation/native-stack';
import React from 'react';
import { AddressesScreen } from '../screens/AddressesScreen';
import { BrowseScreen } from '../screens/BrowseScreen';
import { BuyerVerificationScreen } from '../screens/BuyerVerificationScreen';
import { CartScreen } from '../screens/CartScreen';
import { ChangePasswordScreen } from '../screens/ChangePasswordScreen';
import { ChatScreen } from '../screens/ChatScreen';
import { CheckoutScreen } from '../screens/CheckoutScreen';
import { CreateListingScreen } from '../screens/CreateListingScreen';
import { DataPrivacyScreen } from '../screens/DataPrivacyScreen';
import { EditProfileScreen } from '../screens/EditProfileScreen';
import { HelpSupportScreen } from '../screens/HelpSupportScreen';
import { HireServiceScreen } from '../screens/HireServiceScreen';
import { HomeScreen } from '../screens/HomeScreen';
import { MessagesScreen } from '../screens/MessagesScreen';
import { NotificationSettingsScreen } from '../screens/NotificationSettingsScreen';
import { NotificationsScreen } from '../screens/NotificationsScreen';
import { OrderDetailScreen } from '../screens/OrderDetailScreen';
import { OrdersScreen } from '../screens/OrdersScreen';
import { ProfileScreen } from '../screens/ProfileScreen';
import { ProductDetailScreen } from '../screens/ProductDetailScreen';
import { SavedItemsScreen } from '../screens/SavedItemsScreen';
import { SettingsScreen } from '../screens/SettingsScreen';
import { SupplierOnboardingScreen } from '../screens/SupplierOnboardingScreen';
import { TermsScreen } from '../screens/TermsScreen';
import { defaultStackScreenOptions } from './transitionOptions';
import type {
  BrowseStackParamList,
  HomeStackParamList,
  MessagesStackParamList,
  OrdersStackParamList,
  ProfileStackParamList,
} from './types';

const HomeStackNavigator = createNativeStackNavigator<HomeStackParamList>();
const BrowseStackNavigator = createNativeStackNavigator<BrowseStackParamList>();
const OrdersStackNavigator = createNativeStackNavigator<OrdersStackParamList>();
const MessagesStackNavigator = createNativeStackNavigator<MessagesStackParamList>();
const ProfileStackNavigator = createNativeStackNavigator<ProfileStackParamList>();

export function HomeStack() {
  return (
    <HomeStackNavigator.Navigator screenOptions={defaultStackScreenOptions}>
      <HomeStackNavigator.Screen name="HomeMain" component={HomeScreen} />
      <HomeStackNavigator.Screen name="ProductDetail" component={ProductDetailScreen} />
      <HomeStackNavigator.Screen name="HireService" component={HireServiceScreen} />
      <HomeStackNavigator.Screen name="Cart" component={CartScreen} />
      <HomeStackNavigator.Screen name="Checkout" component={CheckoutScreen} />
      <HomeStackNavigator.Screen name="Notifications" component={NotificationsScreen} />
    </HomeStackNavigator.Navigator>
  );
}

export function BrowseStack() {
  return (
    <BrowseStackNavigator.Navigator screenOptions={defaultStackScreenOptions}>
      <BrowseStackNavigator.Screen name="BrowseMain" component={BrowseScreen} />
      <BrowseStackNavigator.Screen name="ProductDetail" component={ProductDetailScreen} />
      <BrowseStackNavigator.Screen
        name="CreateListing"
        component={CreateListingScreen}
      />
      <BrowseStackNavigator.Screen name="Cart" component={CartScreen} />
      <BrowseStackNavigator.Screen name="Checkout" component={CheckoutScreen} />
    </BrowseStackNavigator.Navigator>
  );
}

export function OrdersStack() {
  return (
    <OrdersStackNavigator.Navigator screenOptions={defaultStackScreenOptions}>
      <OrdersStackNavigator.Screen name="OrdersMain" component={OrdersScreen} />
      <OrdersStackNavigator.Screen name="OrderDetail" component={OrderDetailScreen} />
    </OrdersStackNavigator.Navigator>
  );
}

export function MessagesStack() {
  return (
    <MessagesStackNavigator.Navigator screenOptions={defaultStackScreenOptions}>
      <MessagesStackNavigator.Screen name="MessagesMain" component={MessagesScreen} />
      <MessagesStackNavigator.Screen name="Chat" component={ChatScreen} />
    </MessagesStackNavigator.Navigator>
  );
}

export function ProfileStack() {
  return (
    <ProfileStackNavigator.Navigator screenOptions={defaultStackScreenOptions}>
      <ProfileStackNavigator.Screen name="ProfileMain" component={ProfileScreen} />
      <ProfileStackNavigator.Screen name="EditProfile" component={EditProfileScreen} />
      <ProfileStackNavigator.Screen name="Settings" component={SettingsScreen} />
      <ProfileStackNavigator.Screen
        name="NotificationSettings"
        component={NotificationSettingsScreen}
      />
      <ProfileStackNavigator.Screen
        name="SupplierOnboarding"
        component={SupplierOnboardingScreen}
      />
      <ProfileStackNavigator.Screen
        name="BuyerVerification"
        component={BuyerVerificationScreen}
      />
      <ProfileStackNavigator.Screen name="SavedItems" component={SavedItemsScreen} />
      <ProfileStackNavigator.Screen name="Addresses" component={AddressesScreen} />
      <ProfileStackNavigator.Screen name="DataPrivacy" component={DataPrivacyScreen} />
      <ProfileStackNavigator.Screen name="Terms" component={TermsScreen} />
      <ProfileStackNavigator.Screen name="ChangePassword" component={ChangePasswordScreen} />
      <ProfileStackNavigator.Screen name="HelpSupport" component={HelpSupportScreen} />
    </ProfileStackNavigator.Navigator>
  );
}
