import { Ionicons } from '@expo/vector-icons';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useEffect, useState } from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { Button, Card, Input, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import { API_BASE_URL, apiClient, getApiBaseUrl, setApiBaseUrl } from '../services';
import { theme } from '../theme';
import type { AuthStackParamList } from '../navigation/types';

const logo = require('../../assets/images/logo.png');

type LoginNavigation = NativeStackNavigationProp<AuthStackParamList, 'Login'>;

export function LoginScreen() {
  const navigation = useNavigation<LoginNavigation>();
  const { login } = useAppState();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [checkingBackend, setCheckingBackend] = useState(false);
  const [backendStatus, setBackendStatus] = useState('');
  const [apiUrl, setApiUrl] = useState('');
  const [showDevTools, setShowDevTools] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    getApiBaseUrl().then(setApiUrl).catch(() => undefined);
  }, []);

  const handleLogin = async () => {
    setLoading(true);
    setError('');

    try {
      if (apiUrl) {
        await setApiBaseUrl(apiUrl);
      }
      await login({ login: identifier.trim(), password });
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  const handleTestBackend = async () => {
    setCheckingBackend(true);
    setBackendStatus('');

    try {
      const savedUrl = await setApiBaseUrl(apiUrl);
      await apiClient.get('/health');
      setBackendStatus(`Connected: ${savedUrl}`);
    } catch (reason) {
      setBackendStatus(
        reason instanceof Error ? reason.message : `Could not reach ${apiUrl}`
      );
    } finally {
      setCheckingBackend(false);
    }
  };

  const handleUseStagingApi = async () => {
    const savedUrl = await setApiBaseUrl(API_BASE_URL);
    setApiUrl(savedUrl);
    setBackendStatus('');
    setError('');
  };

  return (
    <Screen contentContainerStyle={styles.screen}>
      <View style={styles.hero}>
        <View style={styles.brandRow}>
          <View style={styles.logoBadge}>
            <Image source={logo} style={styles.logo} resizeMode="contain" />
          </View>
          <Text style={styles.brand}>NaijaBuilders</Text>
        </View>
        <Text style={styles.heroTitle}>Build with trusted suppliers.</Text>
        <Text style={styles.heroSubtitle}>
          Source verified construction materials and hire professionals — all in
          one marketplace.
        </Text>
      </View>

      <Card style={styles.card}>
        <Text style={styles.cardTitle}>Welcome back</Text>
        <Text style={styles.cardSubtitle}>
          Sign in with your email or username.
        </Text>

        <Input
          autoCapitalize="none"
          autoCorrect={false}
          label="Email or username"
          placeholder="you@example.com"
          onChangeText={setIdentifier}
          value={identifier}
          containerStyle={styles.field}
        />

        <View style={styles.field}>
          <Input
            label="Password"
            placeholder="••••••••"
            onChangeText={setPassword}
            secureTextEntry={!showPassword}
            value={password}
          />
          <Pressable
            accessibilityRole="button"
            hitSlop={10}
            onPress={() => setShowPassword((current) => !current)}
            style={styles.eyeButton}
          >
            <Ionicons
              color={theme.colors.textMuted}
              name={showPassword ? 'eye-off-outline' : 'eye-outline'}
              size={20}
            />
          </Pressable>
        </View>

        <Pressable
          accessibilityRole="button"
          onPress={() => navigation.navigate('ForgotPassword')}
          style={styles.forgotRow}
        >
          <Text style={styles.forgotText}>Forgot password?</Text>
        </Pressable>

        {error ? (
          <View style={styles.errorBanner}>
            <Ionicons
              color={theme.colors.danger}
              name="alert-circle"
              size={18}
            />
            <Text style={styles.errorText}>{error}</Text>
          </View>
        ) : null}

        <Button
          title="Sign in"
          size="lg"
          onPress={handleLogin}
          loading={loading}
          disabled={!identifier.trim() || !password}
        />
      </Card>

      <View style={styles.signupRow}>
        <Text style={styles.signupText}>New to NaijaBuilders? </Text>
        <Pressable
          accessibilityRole="button"
          onPress={() => navigation.navigate('Signup')}
        >
          <Text style={styles.signupLink}>Create an account</Text>
        </Pressable>
      </View>

      {__DEV__ ? (
        <Card variant="flat" style={styles.devCard} animated={false}>
          <Pressable
            accessibilityRole="button"
            onPress={() => setShowDevTools((current) => !current)}
            style={styles.devToggle}
          >
            <Ionicons
              color={theme.colors.textMuted}
              name="construct-outline"
              size={16}
            />
            <Text style={styles.devToggleText}>Developer tools</Text>
            <Ionicons
              color={theme.colors.textMuted}
              name={showDevTools ? 'chevron-up' : 'chevron-down'}
              size={16}
            />
          </Pressable>
          {showDevTools ? (
            <View style={styles.devBody}>
              <Input
                autoCapitalize="none"
                autoCorrect={false}
                label="Backend API URL"
                onChangeText={setApiUrl}
                value={apiUrl}
              />
              <View style={styles.devActions}>
                <Button
                  title="Use staging"
                  onPress={handleUseStagingApi}
                  variant="outline"
                  style={styles.devButton}
                />
                <Button
                  title="Test backend"
                  onPress={handleTestBackend}
                  loading={checkingBackend}
                  variant="ghost"
                  style={styles.devButton}
                />
              </View>
              {backendStatus ? (
                <Text style={styles.status}>{backendStatus}</Text>
              ) : null}
            </View>
          ) : null}
        </Card>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: theme.spacing.lg,
    justifyContent: 'center',
  },
  hero: {
    paddingHorizontal: theme.spacing.xs,
    paddingTop: theme.spacing.md,
  },
  brandRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.lg,
  },
  logoBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderRadius: theme.radius.md,
    height: 44,
    justifyContent: 'center',
    width: 44,
    ...theme.shadows.soft,
  },
  logo: {
    height: 30,
    width: 30,
  },
  brand: {
    color: theme.colors.text,
    fontSize: 18,
    fontWeight: '900',
    letterSpacing: -0.3,
  },
  heroTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.display,
    fontWeight: '900',
    letterSpacing: -0.8,
    lineHeight: 36,
  },
  heroSubtitle: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.body,
    lineHeight: 22,
    marginTop: theme.spacing.sm,
  },
  card: {
    gap: theme.spacing.md,
  },
  cardTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
  },
  cardSubtitle: {
    color: theme.colors.textMuted,
    fontSize: 13,
    marginTop: -8,
  },
  field: {
    position: 'relative',
  },
  eyeButton: {
    position: 'absolute',
    right: theme.spacing.md,
    top: 38,
  },
  forgotRow: {
    alignSelf: 'flex-end',
    marginTop: -4,
  },
  forgotText: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '800',
  },
  errorBanner: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.sm,
  },
  errorText: {
    color: theme.colors.danger,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
  },
  signupRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
  },
  signupText: {
    color: theme.colors.textMuted,
    fontSize: 14,
  },
  signupLink: {
    color: theme.colors.primary,
    fontSize: 14,
    fontWeight: '900',
  },
  devCard: {
    gap: theme.spacing.sm,
  },
  devToggle: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  devToggleText: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13,
    fontWeight: '800',
  },
  devBody: {
    gap: theme.spacing.sm,
    marginTop: theme.spacing.sm,
  },
  devActions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  devButton: {
    flex: 1,
  },
  status: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.small,
    fontWeight: '700',
    lineHeight: 18,
    textAlign: 'center',
  },
});
