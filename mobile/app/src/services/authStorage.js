import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = 'mobile_token';

export const getToken = async () => AsyncStorage.getItem(TOKEN_KEY);

export const setToken = async (token) => {
  if (!token) {
    return AsyncStorage.removeItem(TOKEN_KEY);
  }
  return AsyncStorage.setItem(TOKEN_KEY, token);
};

export const clearToken = async () => AsyncStorage.removeItem(TOKEN_KEY);
