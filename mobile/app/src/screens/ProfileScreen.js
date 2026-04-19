import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET/POST /api/mobile/profile (Mobile/ProfileController).
export default function ProfileScreen() {
  const [profile, setProfile] = useState(null);
  const [nameFields, setNameFields] = useState({ firstName: '', lastName: '' });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadProfile = async () => {
    try {
      const response = await api.getProfile();
      setProfile(response.data);
      setNameFields({
        firstName: response.data.first_name || '',
        lastName: response.data.last_name || '',
      });
    } catch (err) {
      setError('Unable to load profile.');
    }
  };

  useEffect(() => {
    loadProfile();
  }, []);

  const updateField = (key, value) => {
    setProfile((prev) => ({ ...prev, user: { ...prev.user, [key]: value } }));
  };

  const updateNameField = (key, value) => {
    setNameFields((prev) => ({ ...prev, [key]: value }));
  };

  const handleSave = async () => {
    setError('');
    setSuccess('');

    try {
      const formData = new FormData();
      formData.append('first_name', nameFields.firstName);
      formData.append('last_name', nameFields.lastName);
      Object.entries(profile.user || {}).forEach(([key, value]) => {
        if (value === null || value === undefined) {
          return;
        }
        formData.append(key, String(value));
      });

      const response = await api.updateProfile(formData);
      setProfile(response.data);
      setSuccess('Profile updated.');
    } catch (err) {
      const message = err?.response?.data?.message || 'Unable to update profile.';
      setError(message);
    }
  };

  if (!profile) {
    return (
      <ScreenWrapper>
        <Text style={styles.title}>Profile</Text>
        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScreenWrapper>
    );
  }

  const user = profile.user || {};

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Profile</Text>
      <View style={styles.card}>
        <Text style={styles.label}>First Name</Text>
        <TextInput
          style={styles.input}
          value={nameFields.firstName}
          onChangeText={(value) => updateNameField('firstName', value)}
        />

        <Text style={styles.label}>Last Name</Text>
        <TextInput
          style={styles.input}
          value={nameFields.lastName}
          onChangeText={(value) => updateNameField('lastName', value)}
        />

        <Text style={styles.label}>Email</Text>
        <TextInput
          style={styles.input}
          value={user.email || ''}
          onChangeText={(value) => updateField('email', value)}
        />

        <Text style={styles.label}>Phone</Text>
        <TextInput
          style={styles.input}
          value={user.phone || ''}
          onChangeText={(value) => updateField('phone', value)}
        />

        <Text style={styles.label}>Company</Text>
        <TextInput
          style={styles.input}
          value={user.company || ''}
          onChangeText={(value) => updateField('company', value)}
        />

        <Text style={styles.label}>Location</Text>
        <TextInput
          style={styles.input}
          value={user.location || ''}
          onChangeText={(value) => updateField('location', value)}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <Pressable style={styles.primaryButton} onPress={handleSave}>
          <Text style={styles.primaryButtonText}>Save Changes</Text>
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
  label: {
    marginTop: 12,
    marginBottom: 6,
    fontSize: 12,
    textTransform: 'uppercase',
    color: colors.muted,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 12,
    backgroundColor: '#FFFDF9',
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
