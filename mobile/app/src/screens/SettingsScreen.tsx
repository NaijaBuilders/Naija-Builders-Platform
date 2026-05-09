import React from 'react';
import { StyleSheet, Switch, Text, View } from 'react-native';
import { Card, FloatingBackButton, Header, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import { userService } from '../services';
import { theme } from '../theme';
import type { UserPreferences } from '../types';

type PreferenceKey = keyof UserPreferences;

const preferenceLabels: Array<{
  key: PreferenceKey;
  label: string;
  description: string;
}> = [
  {
    key: 'push_notifications',
    label: 'Push notifications',
    description: 'Receive order and message alerts.',
  },
  {
    key: 'email_updates',
    label: 'Email updates',
    description: 'Get marketplace and account updates by email.',
  },
  {
    key: 'compact_cards',
    label: 'Compact cards',
    description: 'Use denser lists for repeat workflows.',
  },
];

export function SettingsScreen() {
  const { preferences, setPreferences } = useAppState();

  const togglePreference = async (key: PreferenceKey) => {
    const next = {
      ...preferences,
      [key]: !preferences[key],
    };

    setPreferences(next);

    try {
      const savedPreferences = await userService.updatePreferences(next);
      setPreferences(savedPreferences);
    } catch {
      setPreferences(preferences);
    }
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Account"
        title="Settings"
        subtitle="Manage your account preferences."
      />

      <Text style={styles.sectionTitle}>Preferences</Text>
      {preferenceLabels.map((item) => (
        <Card key={item.key} style={styles.card}>
          <View style={styles.copy}>
            <Text style={styles.label}>{item.label}</Text>
            <Text style={styles.description}>{item.description}</Text>
          </View>
          <Switch
            onValueChange={() => togglePreference(item.key)}
            thumbColor={theme.colors.white}
            trackColor={{
              false: theme.colors.borderStrong,
              true: theme.colors.primary,
            }}
            value={preferences[item.key]}
          />
        </Card>
      ))}
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
  },
  card: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  copy: {
    flex: 1,
  },
  label: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  description: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 3,
  },
});
