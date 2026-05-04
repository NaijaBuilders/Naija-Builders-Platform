import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, View } from 'react-native';
import AppNavigator from './src/navigation/AppNavigator';
import { AuthContext } from './src/contexts/AuthContext';
import { getToken, setToken, clearToken } from './src/services/authStorage';
import { api, setAuthToken } from './src/services/api';

export default function App() {
  const [token, setTokenState] = useState(null);
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const bootstrap = async () => {
      const storedToken = await getToken();
      if (storedToken) {
        setAuthToken(storedToken);
        try {
          const response = await api.getUser();
          setUser(response.data.user || null);
          setTokenState(storedToken);
        } catch (error) {
          await clearToken();
          setAuthToken(null);
          setUser(null);
          setTokenState(null);
        }
      }
      setIsLoading(false);
    };

    bootstrap();
  }, []);

  const authContext = useMemo(
    () => ({
      isLoading,
      token,
      user,
      refreshUser: async () => {
        const response = await api.getUser();
        setUser(response.data.user || null);
        return response.data.user || null;
      },
      signIn: async (newToken, nextUser = null) => {
        await setToken(newToken);
        setAuthToken(newToken);
        if (nextUser) {
          setUser(nextUser);
        } else {
          try {
            const response = await api.getUser();
            setUser(response.data.user || null);
          } catch (error) {
            setUser(null);
          }
        }
        setTokenState(newToken);
      },
      signOut: async () => {
        try {
          await api.logout();
        } catch (error) {
          // The token is cleared locally even if the server logout call fails.
        }
        await clearToken();
        setAuthToken(null);
        setUser(null);
        setTokenState(null);
      },
    }),
    [isLoading, token, user]
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
      <AppNavigator isAuthenticated={Boolean(token)} user={user} />
    </AuthContext.Provider>
  );
}
