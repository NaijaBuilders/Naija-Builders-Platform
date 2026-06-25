import { createNativeStackNavigator } from '@react-navigation/native-stack';
import React from 'react';
import { BrowseScreen } from '../screens/BrowseScreen';
import { BuyerVerificationScreen } from '../screens/BuyerVerificationScreen';
import { ChatScreen } from '../screens/ChatScreen';
import { CreateListingScreen } from '../screens/CreateListingScreen';
import { EditProfileScreen } from '../screens/EditProfileScreen';
import { HireServiceScreen } from '../screens/HireServiceScreen';
import { HomeScreen } from '../screens/HomeScreen';
import { MessagesScreen } from '../screens/MessagesScreen';
import { OrderDetailScreen } from '../screens/OrderDetailScreen';
import { OrdersScreen } from '../screens/OrdersScreen';
import { ProfileScreen } from '../screens/ProfileScreen';
import { ProductDetailScreen } from '../screens/ProductDetailScreen';
import { SettingsScreen } from '../screens/SettingsScreen';
import { SupplierOnboardingScreen } from '../screens/SupplierOnboardingScreen';
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
        name="SupplierOnboarding"
        component={SupplierOnboardingScreen}
      />
      <ProfileStackNavigator.Screen
        name="BuyerVerification"
        component={BuyerVerificationScreen}
      />
    </ProfileStackNavigator.Navigator>
  );
}
