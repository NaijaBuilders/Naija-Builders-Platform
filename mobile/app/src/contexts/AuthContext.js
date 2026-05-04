import { createContext } from 'react';

export const AuthContext = createContext({
  signIn: async () => {},
  signOut: async () => {},
  refreshUser: async () => null,
  isLoading: true,
  token: null,
  user: null,
});
