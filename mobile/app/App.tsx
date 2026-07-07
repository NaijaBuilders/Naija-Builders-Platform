import React, { useEffect, useState } from 'react';
import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AppStateProvider } from './src/context/AppContext';
import { CartProvider } from './src/context/CartContext';
import { ThemeProvider, useThemeMode } from './src/context/ThemeContext';
import { RootNavigator } from './src/navigation/RootNavigator';
import { OpeningScreen } from './src/screens/OpeningScreen';
import { theme } from './src/theme';

function ThemedApp() {
  const { scheme, themeVersion } = useThemeMode();
  const [isOpening, setIsOpening] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsOpening(false);
    }, 1500);

    return () => clearTimeout(timer);
  }, []);

  return (
    <>
      <StatusBar
        barStyle={scheme === 'dark' ? 'light-content' : 'dark-content'}
        backgroundColor={theme.colors.background}
      />
      {/* Remounting on themeVersion forces every StyleSheet.create to re-run
          and read the freshly-swapped palette, so the theme flips live. */}
      <React.Fragment key={themeVersion}>
        {isOpening ? <OpeningScreen /> : <RootNavigator />}
      </React.Fragment>
    </>
  );
}

export default function App() {
  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <AppStateProvider>
          <CartProvider>
            <ThemedApp />
          </CartProvider>
        </AppStateProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
