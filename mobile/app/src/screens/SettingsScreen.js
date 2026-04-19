import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET/POST /api/mobile/settings (Mobile/ProfileController@settings/saveSettings).
export default function SettingsScreen() {
  const [settings, setSettings] = useState(null);
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
    }
  };

  if (!settings) {
    return (
      <ScreenWrapper>
        <Text style={styles.title}>Settings</Text>
        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScreenWrapper>
    );
  }

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Settings</Text>
      <View style={styles.card}>
        {['notifications_email', 'notifications_push', 'material_alerts', 'message_sound'].map((key) => (
          <View key={key} style={styles.row}>
            <Text style={styles.label}>{key.replace('_', ' ')}</Text>
            <Switch value={Boolean(settings[key])} onValueChange={() => toggle(key)} />
          </View>
        ))}

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <Pressable style={styles.primaryButton} onPress={handleSave}>
          <Text style={styles.primaryButtonText}>Save Settings</Text>
        </Pressable>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
    marginBottom: 12,
  },
  card: {
    padding: 16,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
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
  error: {
    marginTop: 8,
    color: '#B23A3A',
  },
  success: {
    marginTop: 8,
    color: '#2D7A4B',
  },
});
