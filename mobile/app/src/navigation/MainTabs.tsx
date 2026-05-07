import { Ionicons } from '@expo/vector-icons';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import React from 'react';
import { StyleSheet, View } from 'react-native';
import { theme } from '../theme';
import {
  BrowseStack,
  HomeStack,
  MessagesStack,
  OrdersStack,
  ProfileStack,
} from './TabStacks';
import type { MainTabParamList } from './types';

const Tab = createBottomTabNavigator<MainTabParamList>();

type IconName = React.ComponentProps<typeof Ionicons>['name'];

const tabIcons: Record<
  keyof MainTabParamList,
  { active: IconName; inactive: IconName }
> = {
  Home: { active: 'home', inactive: 'home-outline' },
  Browse: { active: 'storefront', inactive: 'storefront-outline' },
  Orders: { active: 'receipt', inactive: 'receipt-outline' },
  Messages: { active: 'chatbubble-ellipses', inactive: 'chatbubble-ellipses-outline' },
  Profile: { active: 'person', inactive: 'person-outline' },
};

export function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: theme.colors.primary,
        tabBarInactiveTintColor: theme.colors.textSubtle,
        tabBarLabelStyle: {
          fontSize: 12,
          fontWeight: '800',
        },
        tabBarStyle: {
          backgroundColor: theme.colors.surface,
          borderTopColor: theme.colors.border,
          height: 76,
          paddingBottom: 12,
          paddingTop: 9,
        },
        tabBarIcon: ({ color, focused, size }) => {
          const icon = tabIcons[route.name];
          return (
            <View style={focused ? styles.activeIcon : styles.icon}>
              <Ionicons
                color={color}
                name={focused ? icon.active : icon.inactive}
                size={size}
              />
            </View>
          );
        },
      })}
    >
      <Tab.Screen name="Home" component={HomeStack} />
      <Tab.Screen name="Browse" component={BrowseStack} />
      <Tab.Screen name="Orders" component={OrdersStack} />
      <Tab.Screen name="Messages" component={MessagesStack} />
      <Tab.Screen name="Profile" component={ProfileStack} />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  icon: {
    alignItems: 'center',
    borderRadius: theme.radius.pill,
    height: 30,
    justifyContent: 'center',
    width: 42,
  },
  activeIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 30,
    justifyContent: 'center',
    width: 42,
  },
});
