import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { authScreens, publicScreens, protectedScreens } from '../config/features';

const Stack = createStackNavigator();

const screenOptions = {
  headerStyle: { backgroundColor: '#F8F4EF' },
  headerTitleStyle: { fontFamily: 'Georgia', color: '#1D2A32' },
  headerTintColor: '#0E7C86',
};

const renderScreens = (screens) =>
  screens.map((screen) => (
    <Stack.Screen
      key={screen.name}
      name={screen.name}
      component={screen.component}
      options={screen.options}
    />
  ));

const AuthStack = () => (
  <Stack.Navigator screenOptions={screenOptions}>
    {renderScreens(authScreens)}
    {renderScreens(publicScreens)}
  </Stack.Navigator>
);

const MainStack = () => (
  <Stack.Navigator screenOptions={screenOptions}>
    {renderScreens(publicScreens)}
    {renderScreens(protectedScreens)}
  </Stack.Navigator>
);

export default function AppNavigator({ isAuthenticated }) {
  return (
    <NavigationContainer>
      {isAuthenticated ? <MainStack /> : <AuthStack />}
    </NavigationContainer>
  );
}
