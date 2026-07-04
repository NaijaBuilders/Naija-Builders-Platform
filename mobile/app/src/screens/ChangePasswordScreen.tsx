import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React, { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import type { ProfileStackParamList } from '../navigation/types';
import { authService } from '../services';
import { theme } from '../theme';
import { haptics } from '../utils/haptics';

type ChangePasswordNavigation = NativeStackNavigationProp<
  ProfileStackParamList,
  'ChangePassword'
>;

export function ChangePasswordScreen() {
  const navigation = useNavigation<ChangePasswordNavigation>();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [done, setDone] = useState(false);

  const canSubmit =
    currentPassword.length > 0 &&
    newPassword.length >= 8 &&
    confirmPassword.length >= 8;

  const submit = async () => {
    if (newPassword !== confirmPassword) {
      setError('The new passwords do not match.');
      return;
    }

    if (newPassword === currentPassword) {
      setError('Choose a password different from your current one.');
      return;
    }

    setLoading(true);
    setError('');
    try {
      await authService.changePassword(currentPassword, newPassword);
      haptics.success();
      setDone(true);
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not change password.'
      );
    } finally {
      setLoading(false);
    }
  };

  if (done) {
    return (
      <Screen
        contentContainerStyle={styles.contentWithFloatingBack}
        floating={<FloatingBackButton />}
      >
        <Card style={styles.doneCard}>
          <View style={styles.doneIcon}>
            <Ionicons
              color={theme.colors.secondary}
              name="checkmark-circle"
              size={44}
            />
          </View>
          <Text style={styles.doneTitle}>Password changed</Text>
          <Text style={styles.doneText}>
            Your other signed-in devices have been logged out. This device
            stays signed in.
          </Text>
          <Button onPress={() => navigation.goBack()} size="lg" title="Done" />
        </Card>
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Security"
        title="Change password"
        subtitle="Use at least 8 characters. Other devices are signed out after the change."
      />

      <Card style={styles.card}>
        <Input
          label="Current password"
          onChangeText={setCurrentPassword}
          secureTextEntry
          value={currentPassword}
        />
        <Input
          label="New password"
          onChangeText={setNewPassword}
          secureTextEntry
          value={newPassword}
        />
        <Input
          label="Confirm new password"
          onChangeText={setConfirmPassword}
          secureTextEntry
          value={confirmPassword}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <Button
          disabled={!canSubmit || loading}
          loading={loading}
          onPress={submit}
          size="lg"
          title="Change password"
        />
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 86,
  },
  card: {
    gap: theme.spacing.md,
  },
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    textAlign: 'center',
  },
  doneCard: {
    alignItems: 'center',
    gap: theme.spacing.md,
    marginTop: theme.spacing.xl,
    paddingVertical: theme.spacing.xl,
  },
  doneIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.secondarySoft,
    borderRadius: theme.radius.pill,
    height: 76,
    justifyContent: 'center',
    width: 76,
  },
  doneTitle: {
    color: theme.colors.text,
    fontSize: 19,
    fontWeight: '900',
  },
  doneText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    textAlign: 'center',
  },
});
