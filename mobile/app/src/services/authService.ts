import type { AuthSession, LoginPayload, SignupPayload } from '../types';
import {
  apiClient,
  handleServiceError,
  setAuthToken,
} from './apiClient';
import { mapLaravelUser, toLaravelAccountType } from './adapters';

type LaravelAuthResponse = {
  token: string;
  user: unknown;
};

type LaravelMessageResponse = {
  message?: string;
};

function buildSession(response: LaravelAuthResponse): AuthSession {
  return {
    token: response.token,
    user: mapLaravelUser(response.user as Parameters<typeof mapLaravelUser>[0]),
  };
}

export const authService = {
  async login(payload: LoginPayload): Promise<AuthSession> {
    try {
      const response = await apiClient.post<LaravelAuthResponse>('/login', {
        login: payload.login,
        // `email` is sent for backward compatibility with backends that have
        // not yet deployed username login (they only read the email field).
        email: payload.login,
        password: payload.password,
      });
      const session = buildSession(response.data);

      await setAuthToken(session.token);
      return session;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async signup(payload: SignupPayload): Promise<AuthSession> {
    try {
      const response = await apiClient.post<LaravelAuthResponse>('/register', {
        name: payload.name,
        email: payload.email,
        username: payload.username,
        phone: payload.phone,
        location: payload.location,
        password: payload.password,
        confirm_password: payload.confirmPassword,
        account_type: toLaravelAccountType(payload.role),
        company: payload.company,
        terms_accepted: payload.termsAccepted,
      });
      const session = buildSession(response.data);

      await setAuthToken(session.token);
      return session;
    } catch (error) {
      handleServiceError(error);
    }
  },

  async requestPasswordReset(email: string): Promise<{
    email: string;
    message: string;
    debugCode?: string;
  }> {
    try {
      const response = await apiClient.post<
        LaravelMessageResponse & { debug_code?: string | null }
      >('/forgot-password', { email });

      return {
        email,
        message:
          response.data.message ??
          'If an account exists for this email, a reset code has been sent.',
        debugCode: response.data.debug_code ?? undefined,
      };
    } catch (error) {
      handleServiceError(error);
    }
  },

  async resetPassword(
    email: string,
    code: string,
    password: string
  ): Promise<string> {
    try {
      const response = await apiClient.post<LaravelMessageResponse>(
        '/reset-password',
        { email, code, password }
      );

      return response.data.message ?? 'Your password has been reset.';
    } catch (error) {
      handleServiceError(error);
    }
  },

  async changePassword(
    currentPassword: string,
    newPassword: string
  ): Promise<string> {
    try {
      const response = await apiClient.post<LaravelMessageResponse>(
        '/account/password',
        {
          current_password: currentPassword,
          new_password: newPassword,
        }
      );

      return response.data.message ?? 'Your password has been changed.';
    } catch (error) {
      handleServiceError(error);
    }
  },

  async deleteAccount(password: string): Promise<void> {
    try {
      await apiClient.delete('/account', { data: { password } });
      await setAuthToken(null);
    } catch (error) {
      handleServiceError(error);
    }
  },

  async logout(): Promise<void> {
    try {
      await apiClient.post('/logout');
    } catch {
      // Local token removal should still happen if the token is expired or offline.
    } finally {
      await setAuthToken(null);
    }
  },
};
