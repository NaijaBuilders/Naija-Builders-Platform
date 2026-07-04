import React, { useEffect, useState } from 'react';
import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AppStateProvider } from './src/context/AppContext';
import { CartProvider } from './src/context/CartContext';
import { RootNavigator } from './src/navigation/RootNavigator';
import { OpeningScreen } from './src/screens/OpeningScreen';
import { isDarkTheme, theme } from './src/theme';

export default function App() {
  const [isOpening, setIsOpening] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsOpening(false);
    }, 1500);

    return () => clearTimeout(timer);
  }, []);

  return (
    <SafeAreaProvider>
      <AppStateProvider>
        <CartProvider>
          <StatusBar
            barStyle={isDarkTheme ? 'light-content' : 'dark-content'}
            backgroundColor={theme.colors.background}
          />
          {isOpening ? <OpeningScreen /> : <RootNavigator />}
        </CartProvider>
      </AppStateProvider>
    </SafeAreaProvider>
  );
}
