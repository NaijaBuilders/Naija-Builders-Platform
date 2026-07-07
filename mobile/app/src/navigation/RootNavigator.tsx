import {
  DefaultTheme,
  NavigationContainer,
  type LinkingOptions,
  type Theme as NavigationTheme,
} from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import React from 'react';
import { View } from 'react-native';
import { Loader } from '../components';
import { AuthStack } from './AuthStack';
import { MainTabs } from './MainTabs';
import { defaultStackScreenOptions } from './transitionOptions';
import type { RootStackParamList } from './types';
import { useAppState } from '../context/AppContext';
import { theme } from '../theme';

const Stack = createNativeStackNavigator<RootStackParamList>();

const linking: LinkingOptions<RootStackParamList> = {
  prefixes: ['naijabuilders://', 'https://naijabuilders.com'],
  config: {
    screens: {
      Main: {
        screens: {
          Home: {
            screens: {
              ProductDetail: 'product/:productId',
              Notifications: 'notifications',
              Cart: 'cart',
            },
          },
          Orders: {
            screens: {
              OrderDetail: 'orders/:orderId',
            },
          },
          Messages: {
            screens: {
              Chat: 'chat/:conversationId',
            },
          },
        },
      },
    },
  },
};

export function RootNavigator() {
  const { isAuthenticated, isAuthLoading } = useAppState();

  // Built per render so it tracks the live palette when the theme flips.
  const navigationTheme: NavigationTheme = {
    ...DefaultTheme,
    colors: {
      ...DefaultTheme.colors,
      background: theme.colors.background,
      border: theme.colors.border,
      card: theme.colors.surface,
      primary: theme.colors.primary,
      text: theme.colors.text,
    },
  };

  if (isAuthLoading) {
    return (
      <View
        style={{
          alignItems: 'center',
          backgroundColor: theme.colors.background,
          flex: 1,
          justifyContent: 'center',
        }}
      >
        <Loader label="Checking session" />
      </View>
    );
  }

  return (
    <NavigationContainer linking={linking} theme={navigationTheme}>
      <Stack.Navigator screenOptions={defaultStackScreenOptions}>
        {isAuthenticated ? (
          <Stack.Screen name="Main" component={MainTabs} />
        ) : (
          <Stack.Screen name="Auth" component={AuthStack} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
