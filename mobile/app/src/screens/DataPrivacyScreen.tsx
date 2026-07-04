import { Ionicons } from '@expo/vector-icons';
import React, { useState } from 'react';
import { Alert, StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import { useAppState } from '../context/AppContext';
import { authService } from '../services';
import { theme } from '../theme';

export function DataPrivacyScreen() {
  const { signOut } = useAppState();
  const [confirming, setConfirming] = useState(false);
  const [password, setPassword] = useState('');
  const [deleting, setDeleting] = useState(false);
  const [error, setError] = useState('');

  const requestDeletion = () => {
    Alert.alert(
      'Delete your account?',
      'This permanently removes your profile, saved items and sign-in access. Order history stays visible to businesses you traded with, without your personal details.',
      [
        { style: 'cancel', text: 'Keep my account' },
        {
          style: 'destructive',
          text: 'Continue',
          onPress: () => setConfirming(true),
        },
      ]
    );
  };

  const deleteAccount = async () => {
    setDeleting(true);
    setError('');
    try {
      await authService.deleteAccount(password);
      await signOut();
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not delete account.'
      );
    } finally {
      setDeleting(false);
    }
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Privacy"
        title="Data & privacy"
        subtitle="Control your personal data on NaijaBuilders."
      />

      <Card style={styles.card}>
        <View style={styles.row}>
          <View style={styles.iconCircle}>
            <Ionicons
              color={theme.colors.primary}
              name="download-outline"
              size={18}
            />
          </View>
          <View style={styles.rowCopy}>
            <Text style={styles.rowLabel}>Export my data</Text>
            <Text style={styles.rowDescription}>
              Request a copy of your profile, orders and messages. Coming soon.
            </Text>
          </View>
        </View>
      </Card>

      <Card style={styles.dangerCard}>
        <View style={styles.row}>
          <View style={styles.dangerIcon}>
            <Ionicons color={theme.colors.danger} name="trash-outline" size={18} />
          </View>
          <View style={styles.rowCopy}>
            <Text style={styles.rowLabel}>Delete account</Text>
            <Text style={styles.rowDescription}>
              Permanently remove your account and personal data.
            </Text>
          </View>
        </View>

        {confirming ? (
          <View style={styles.confirmBox}>
            <Text style={styles.confirmText}>
              Enter your password to confirm deletion. This cannot be undone.
            </Text>
            <Input
              label="Password"
              onChangeText={setPassword}
              secureTextEntry
              value={password}
            />
            {error ? <Text style={styles.error}>{error}</Text> : null}
            <View style={styles.confirmActions}>
              <Button
                onPress={() => {
                  setConfirming(false);
                  setPassword('');
                  setError('');
                }}
                style={styles.confirmAction}
                title="Cancel"
                variant="outline"
              />
              <Button
                disabled={password.length === 0 || deleting}
                loading={deleting}
                onPress={deleteAccount}
                style={styles.confirmAction}
                title="Delete forever"
                variant="danger"
              />
            </View>
          </View>
        ) : (
          <Button onPress={requestDeletion} title="Delete my account" variant="danger" />
        )}
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  dangerCard: {
    borderColor: '#F6C8C0',
    borderWidth: 1,
    gap: theme.spacing.md,
  },
  row: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  iconCircle: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  dangerIcon: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  rowCopy: {
    flex: 1,
  },
  rowLabel: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  rowDescription: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    lineHeight: 18,
    marginTop: 2,
  },
  confirmBox: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  confirmText: {
    color: theme.colors.text,
    fontSize: 13.5,
    fontWeight: '700',
    lineHeight: 20,
  },
  confirmActions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  confirmAction: {
    flex: 1,
  },
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    textAlign: 'center',
  },
});
