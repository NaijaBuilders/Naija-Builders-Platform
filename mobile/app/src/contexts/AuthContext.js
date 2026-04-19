import { createContext } from 'react';

export const AuthContext = createContext({
  signIn: async () => {},
  signOut: async () => {},
  isLoading: true,
  token: null,
});
