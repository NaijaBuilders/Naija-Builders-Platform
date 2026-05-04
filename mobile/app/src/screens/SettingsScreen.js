import React, { useEffect, useState } from 'react';
import { StyleSheet, Switch, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';

// Maps to GET/POST /api/mobile/settings (Mobile/ProfileController@settings/saveSettings).
export default function SettingsScreen() {
  const [settings, setSettings] = useState(null);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    const loadSettings = async () => {
      try {
        const response = await api.getSettings();
        setSettings(response.data.settings || {});
      } catch (err) {
        setError('Unable to load settings.');
      }
    };

    loadSettings();
  }, []);

  const toggle = (key) => {
    setSettings((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const handleSave = async () => {
    setError('');
    setSuccess('');

    try {
      setIsSaving(true);
      const booleanKeys = [
        'notifications_email',
        'notifications_push',
        'material_alerts',
        'message_sound',
        'notifications_sms',
        'auto_save_drafts',
        'compact_dashboard',
        'two_factor_login',
        'api_access',
        'developer_mode',
        'beta_features',
        'activity_logs',
        'session_timeout_short',
      ];

      const payload = { ...settings };
      booleanKeys.forEach((key) => {
        if (key in payload) {
          payload[key] = payload[key] ? '1' : '0';
        }
      });
      const response = await api.saveSettings(payload);
      setSettings(response.data.settings || settings);
      setSuccess('Settings saved.');
    } catch (err) {
      setError('Unable to save settings.');
    } finally {
      setIsSaving(false);
    }
  };

  if (!settings) {
    return (
      <ScreenWrapper>
        <ScreenHeader title="Settings" />
        {error ? <ErrorBanner message={error} /> : <LoadingState label="Loading settings..." />}
      </ScreenWrapper>
    );
  }

  return (
    <ScreenWrapper>
      <ScreenHeader title="Settings" subtitle="Tune notifications, regional preferences, and mobile experience." />
      <Card style={styles.cardGap}>
        <Text style={styles.sectionTitle}>Notifications</Text>
        {['notifications_email', 'notifications_sms', 'notifications_push', 'material_alerts', 'message_sound'].map((key) => (
          <SettingToggle key={key} label={key} value={Boolean(settings[key])} onChange={() => toggle(key)} />
        ))}
      </Card>

      <Card style={styles.cardGap}>
        <Text style={styles.sectionTitle}>Experience</Text>
        {['auto_save_drafts', 'compact_dashboard', 'activity_logs', 'session_timeout_short'].map((key) => (
          <SettingToggle key={key} label={key} value={Boolean(settings[key])} onChange={() => toggle(key)} />
        ))}
      </Card>

      <Card style={styles.cardGap}>
        <Text style={styles.sectionTitle}>Advanced</Text>
        {['two_factor_login', 'api_access', 'developer_mode', 'beta_features'].map((key) => (
          <SettingToggle key={key} label={key} value={Boolean(settings[key])} onChange={() => toggle(key)} />
        ))}

        <ErrorBanner message={error} />
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <AppButton label={isSaving ? 'Saving...' : 'Save Settings'} onPress={handleSave} disabled={isSaving} />
      </Card>
    </ScreenWrapper>
  );
}

function SettingToggle({ label, value, onChange }) {
  return (
    <View style={styles.row}>
      <Text style={styles.label}>{label.replace(/_/g, ' ')}</Text>
      <Switch value={value} onValueChange={onChange} />
    </View>
  );
}

const styles = StyleSheet.create({
  cardGap: {
    marginBottom: spacing.md,
  },
  sectionTitle: {
    color: colors.ink,
    fontSize: 18,
    fontWeight: '800',
    marginBottom: spacing.sm,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  label: {
    color: colors.muted,
    textTransform: 'capitalize',
  },
  primaryButton: {
    marginTop: 16,
    backgroundColor: colors.accent,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  success: {
    marginTop: 8,
    color: '#2D7A4B',
  },
});
