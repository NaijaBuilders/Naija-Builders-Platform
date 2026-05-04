import React, { useContext, useEffect, useState } from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Badge, Card, ErrorBanner, LoadingState, ScreenHeader, TextField } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, radius, spacing } from '../styles/theme';
import { humanize, imageUrl } from '../utils/format';

// Maps to GET/POST /api/mobile/profile (Mobile/ProfileController).
export default function ProfileScreen() {
  const { user: authUser, refreshUser, signOut } = useContext(AuthContext);
  const [profile, setProfile] = useState(null);
  const [nameFields, setNameFields] = useState({ firstName: '', lastName: '' });
  const [profileImage, setProfileImage] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadProfile = async () => {
    setIsLoading(true);
    setError('');
    try {
      const response = await api.getProfile();
      setProfile(response.data);
      setNameFields({
        firstName: response.data.first_name || '',
        lastName: response.data.last_name || '',
      });
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load profile.');
    } finally {
      setIsLoading(false);
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
    setIsSaving(true);

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

      if (profileImage) {
        const uriParts = String(profileImage.uri || '').split('.');
        const extension = (uriParts.pop() || 'jpg').toLowerCase();
        formData.append('profile_picture', {
          uri: profileImage.uri,
          name: `profile.${extension}`,
          type: profileImage.mimeType || `image/${extension === 'jpg' ? 'jpeg' : extension}`,
        });
      }

      const response = await api.updateProfile(formData);
      setProfile(response.data);
      setProfileImage(null);
      await refreshUser();
      setSuccess('Profile updated.');
    } catch (err) {
      const message = err?.response?.data?.message || 'Unable to update profile.';
      setError(message);
    } finally {
      setIsSaving(false);
    }
  };

  const pickProfileImage = async () => {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      setError('Photo library permission is required to update your profile picture.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: true,
      aspect: [1, 1],
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.8,
    });

    if (!result.canceled) {
      setProfileImage(result.assets?.[0] || null);
    }
  };

  if (isLoading && !profile) {
    return (
      <ScreenWrapper>
        <ScreenHeader title="Profile" />
        <LoadingState label="Loading profile..." />
      </ScreenWrapper>
    );
  }

  if (!profile) {
    return (
      <ScreenWrapper refreshing={false} onRefresh={loadProfile}>
        <ScreenHeader title="Profile" />
        <ErrorBanner message={error} />
      </ScreenWrapper>
    );
  }

  const user = profile.user || {};

  return (
    <ScreenWrapper>
      <ScreenHeader title="Profile" subtitle="Keep account details aligned with the website profile." />
      <Card style={styles.summaryCard}>
        <View style={styles.summaryRow}>
          {profileImage?.uri || user.profile_image_path ? (
            <Image source={{ uri: profileImage?.uri || imageUrl(user.profile_image_path) }} style={styles.avatar} />
          ) : (
            <View style={styles.avatarFallback}><Text style={styles.avatarText}>NB</Text></View>
          )}
          <View style={styles.summaryText}>
            <Text style={styles.name}>{profile.full_name || authUser?.name || 'User'}</Text>
            <Text style={styles.meta}>{user.email}</Text>
            <View style={styles.badgeRow}>
              <Badge label={humanize(user.role || authUser?.role || 'buyer')} tone="soft" />
              {user.is_verified_badge ? <Badge label="Verified Badge" tone="success" /> : null}
            </View>
          </View>
        </View>
        <View style={styles.profileActions}>
          <AppButton label="Change Photo" variant="soft" size="sm" onPress={pickProfileImage} />
          <AppButton label="Log Out" variant="danger" size="sm" onPress={signOut} />
        </View>
      </Card>

      <Card>
        <TextField
          label="First Name"
          value={nameFields.firstName}
          onChangeText={(value) => updateNameField('firstName', value)}
        />

        <TextField
          label="Last Name"
          value={nameFields.lastName}
          onChangeText={(value) => updateNameField('lastName', value)}
        />

        <TextField
          label="Email"
          autoCapitalize="none"
          keyboardType="email-address"
          value={user.email || ''}
          onChangeText={(value) => updateField('email', value)}
        />

        <TextField
          label="Phone"
          keyboardType="phone-pad"
          value={user.phone || ''}
          onChangeText={(value) => updateField('phone', value)}
        />

        <TextField
          label="Company"
          value={user.company || ''}
          onChangeText={(value) => updateField('company', value)}
        />

        <TextField
          label="Location"
          value={user.location || ''}
          onChangeText={(value) => updateField('location', value)}
        />

        <ErrorBanner message={error} />
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <AppButton label={isSaving ? 'Saving...' : 'Save Changes'} onPress={handleSave} disabled={isSaving} />
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  summaryCard: {
    marginBottom: spacing.md,
  },
  summaryRow: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  avatar: {
    borderRadius: radius.md,
    height: 78,
    width: 78,
  },
  avatarFallback: {
    alignItems: 'center',
    backgroundColor: colors.accentSoft,
    borderRadius: radius.md,
    height: 78,
    justifyContent: 'center',
    width: 78,
  },
  avatarText: {
    color: colors.accent,
    fontWeight: '800',
  },
  summaryText: {
    flex: 1,
  },
  name: {
    color: colors.ink,
    fontSize: 18,
    fontWeight: '800',
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginTop: spacing.sm,
  },
  profileActions: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.md,
  },
  success: {
    marginTop: 8,
    color: '#2D7A4B',
  },
});
