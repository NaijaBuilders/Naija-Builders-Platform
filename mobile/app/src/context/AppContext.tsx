import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import {
  authService,
  getStoredAuthToken,
  notificationService,
  setAuthToken,
  userService,
} from '../services';
import type {
  LoginPayload,
  SignupPayload,
  UserPreferences,
  UserProfile,
  UserRole,
} from '../types';

const defaultPreferences: UserPreferences = {
  compact_cards: false,
  email_updates: true,
  push_notifications: true,
};

type AppStateContextValue = {
  currentRole: UserRole;
  user: UserProfile | null;
  isAuthenticated: boolean;
  isAuthLoading: boolean;
  preferences: UserPreferences;
  setUser: React.Dispatch<React.SetStateAction<UserProfile | null>>;
  setPreferences: React.Dispatch<React.SetStateAction<UserPreferences>>;
  login: (payload: LoginPayload) => Promise<void>;
  register: (payload: SignupPayload) => Promise<void>;
  signOut: () => Promise<void>;
};

const AppStateContext = createContext<AppStateContextValue | undefined>(undefined);

export function AppStateProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<UserProfile | null>(null);
  const [currentRole, setCurrentRoleValue] = useState<UserRole>('buyer');
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isAuthLoading, setIsAuthLoading] = useState(true);
  const [preferences, setPreferences] =
    useState<UserPreferences>(defaultPreferences);

  const syncPreferences = useCallback(async () => {
    try {
      const nextPreferences = await userService.getPreferences();
      setPreferences(nextPreferences);
    } catch {
      setPreferences(defaultPreferences);
    }
  }, []);

  useEffect(() => {
    let active = true;

    async function hydrateSession() {
      try {
        const token = await getStoredAuthToken();

        if (!token) {
          return;
        }

        const [currentUser, currentPreferences] = await Promise.all([
          userService.getCurrentUser(),
          userService.getPreferences().catch(() => defaultPreferences),
        ]);

        if (active) {
          setUser(currentUser);
          setCurrentRoleValue(currentUser.role);
          setPreferences(currentPreferences);
          setIsAuthenticated(true);
          notificationService.registerDeviceForPush();
        }
      } catch {
        await setAuthToken(null);
        if (active) {
          setUser(null);
          setCurrentRoleValue('buyer');
          setIsAuthenticated(false);
        }
      } finally {
        if (active) {
          setIsAuthLoading(false);
        }
      }
    }

    hydrateSession();

    return () => {
      active = false;
    };
  }, []);

  const login = useCallback(
    async (payload: LoginPayload) => {
      const session = await authService.login(payload);
      setUser(session.user);
      setCurrentRoleValue(session.user.role);
      setIsAuthenticated(true);
      notificationService.registerDeviceForPush();
      await syncPreferences();
    },
    [syncPreferences]
  );

  const register = useCallback(
    async (payload: SignupPayload) => {
      const session = await authService.signup(payload);
      setUser(session.user);
      setCurrentRoleValue(session.user.role);
      setIsAuthenticated(true);
      await syncPreferences();
    },
    [syncPreferences]
  );

  const signOut = useCallback(async () => {
    await authService.logout();
    setUser(null);
    setCurrentRoleValue('buyer');
    setIsAuthenticated(false);
    setPreferences(defaultPreferences);
  }, []);

  const value = useMemo(
    () => ({
      currentRole,
      isAuthenticated,
      isAuthLoading,
      login,
      preferences,
      register,
      setPreferences,
      setUser,
      signOut,
      user,
    }),
    [
      currentRole,
      isAuthenticated,
      isAuthLoading,
      login,
      preferences,
      register,
      signOut,
      user,
    ]
  );

  return (
    <AppStateContext.Provider value={value}>{children}</AppStateContext.Provider>
  );
}

export function useAppState() {
  const context = useContext(AppStateContext);

  if (!context) {
    throw new Error('useAppState must be used inside AppStateProvider');
  }

  return context;
}
