import React, { useEffect, useState } from 'react';
import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AppStateProvider } from './src/context/AppContext';
import { RootNavigator } from './src/navigation/RootNavigator';
import { OpeningScreen } from './src/screens/OpeningScreen';
import { theme } from './src/theme';

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
        <StatusBar
          barStyle="dark-content"
          backgroundColor={theme.colors.background}
        />
        {isOpening ? <OpeningScreen /> : <RootNavigator />}
      </AppStateProvider>
    </SafeAreaProvider>
  );
}
