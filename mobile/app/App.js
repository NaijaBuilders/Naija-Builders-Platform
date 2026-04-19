import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, View } from 'react-native';
import AppNavigator from './src/navigation/AppNavigator';
import { AuthContext } from './src/contexts/AuthContext';
import { getToken, setToken, clearToken } from './src/services/authStorage';
import { setAuthToken } from './src/services/api';

export default function App() {
  const [token, setTokenState] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const bootstrap = async () => {
      const storedToken = await getToken();
      if (storedToken) {
        setAuthToken(storedToken);
        setTokenState(storedToken);
      }
      setIsLoading(false);
    };

    bootstrap();
  }, []);

  const authContext = useMemo(
    () => ({
      isLoading,
      token,
      signIn: async (newToken) => {
        await setToken(newToken);
        setAuthToken(newToken);
        setTokenState(newToken);
      },
      signOut: async () => {
        await clearToken();
        setAuthToken(null);
        setTokenState(null);
      },
    }),
    [isLoading, token]
  );

  if (isLoading) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#F8F4EF' }}>
        <ActivityIndicator size="large" color="#0E7C86" />
      </View>
    );
  }

  return (
    <AuthContext.Provider value={authContext}>
      <AppNavigator isAuthenticated={Boolean(token)} />
    </AuthContext.Provider>
  );
}
