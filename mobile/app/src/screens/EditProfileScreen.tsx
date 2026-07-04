import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as ImagePicker from 'expo-image-picker';
import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Avatar,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Loader,
  Screen,
} from '../components';
import { useAppState } from '../context/AppContext';
import { userService } from '../services';
import { theme } from '../theme';
import type { ProfileStackParamList } from '../navigation/types';

type EditProfileNavigation = NativeStackNavigationProp<
  ProfileStackParamList,
  'EditProfile'
>;

export function EditProfileScreen() {
  const navigation = useNavigation<EditProfileNavigation>();
  const { currentRole, setUser, user } = useAppState();
  const showBusiness = currentRole === 'supplier';

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [photoUri, setPhotoUri] = useState('');
  const [currentImage, setCurrentImage] = useState<string | undefined>(
    user?.profileImage
  );

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [location, setLocation] = useState('');
  const [company, setCompany] = useState('');
  const [businessCategory, setBusinessCategory] = useState('');
  const [businessAddress, setBusinessAddress] = useState('');
  const [businessDescription, setBusinessDescription] = useState('');

  useEffect(() => {
    let mounted = true;

    userService
      .getEditableProfile()
      .then((profile) => {
        if (!mounted) {
          return;
        }
        setFirstName(profile.firstName);
        setLastName(profile.lastName);
        setUsername(profile.username);
        setEmail(profile.email);
        setPhone(profile.phone);
        setLocation(profile.location);
        setCompany(profile.company);
        setBusinessCategory(profile.businessCategory);
        setBusinessAddress(profile.businessAddress);
        setBusinessDescription(profile.businessDescription);
        setCurrentImage(profile.profileImage ?? user?.profileImage);
      })
      .catch((reason) => {
        if (mounted) {
          setError(
            reason instanceof Error ? reason.message : 'Could not load profile.'
          );
        }
      })
      .finally(() => {
        if (mounted) {
          setLoading(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, [user?.profileImage]);

  const pickPhoto = async () => {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      setError('Photo access is required to change your picture.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: true,
      aspect: [1, 1],
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.85,
    });

    if (!result.canceled && result.assets[0]?.uri) {
      setPhotoUri(result.assets[0].uri);
    }
  };

  const handleSave = async () => {
    setError('');

    if (!firstName.trim() || !email.trim() || !username.trim()) {
      setError('Name, username and email are required.');
      return;
    }

    setSaving(true);
    try {
      const updated = await userService.updateProfile({
        firstName: firstName.trim(),
        lastName: lastName.trim(),
        username: username.trim(),
        email: email.trim(),
        phone: phone.trim(),
        location: location.trim(),
        photoUri: photoUri || undefined,
        ...(showBusiness
          ? {
              company: company.trim(),
              businessCategory: businessCategory.trim(),
              businessAddress: businessAddress.trim(),
              businessDescription: businessDescription.trim(),
            }
          : {}),
      });

      setUser(updated);
      navigation.goBack();
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not save profile.'
      );
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading profile" />
      </Screen>
    );
  }

  const previewImage = photoUri || currentImage;

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Account"
        title="Edit profile"
        subtitle="Update your details and profile photo."
      />

      <View style={styles.photoWrap}>
        <Pressable
          accessibilityRole="button"
          onPress={pickPhoto}
          style={styles.photoPressable}
        >
          <Avatar name={`${firstName} ${lastName}`} imageUri={previewImage} size={104} />
          <View style={styles.cameraBadge}>
            <Ionicons color={theme.colors.white} name="camera" size={16} />
          </View>
        </Pressable>
        <Text style={styles.photoHint}>Tap to change photo</Text>
      </View>

      <Card style={styles.card}>
        <Text style={styles.sectionLabel}>Personal</Text>
        <View style={styles.row}>
          <Input
            containerStyle={styles.rowItem}
            label="First name"
            onChangeText={setFirstName}
            value={firstName}
          />
          <Input
            containerStyle={styles.rowItem}
            label="Last name"
            onChangeText={setLastName}
            value={lastName}
          />
        </View>
        <Input
          autoCapitalize="none"
          autoCorrect={false}
          label="Username"
          onChangeText={setUsername}
          value={username}
        />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setEmail}
          value={email}
        />
        <Input
          keyboardType="phone-pad"
          label="Phone"
          onChangeText={setPhone}
          value={phone}
        />
        <Input label="Location" onChangeText={setLocation} value={location} />
      </Card>

      {showBusiness ? (
        <Card style={styles.card}>
          <Text style={styles.sectionLabel}>Business</Text>
          <Input
            label="Business name"
            onChangeText={setCompany}
            value={company}
          />
          <Input
            label="Business category"
            onChangeText={setBusinessCategory}
            value={businessCategory}
          />
          <Input
            label="Business address"
            onChangeText={setBusinessAddress}
            value={businessAddress}
          />
          <Input
            label="About your business"
            multiline
            onChangeText={setBusinessDescription}
            style={styles.multiline}
            textAlignVertical="top"
            value={businessDescription}
          />
        </Card>
      ) : null}

      {error ? (
        <View style={styles.errorBanner}>
          <Ionicons color={theme.colors.danger} name="alert-circle" size={18} />
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : null}

      <Button
        title="Save changes"
        size="lg"
        onPress={handleSave}
        loading={saving}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  photoWrap: {
    alignItems: 'center',
    marginBottom: theme.spacing.lg,
  },
  photoPressable: {
    position: 'relative',
  },
  cameraBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.background,
    borderRadius: theme.radius.pill,
    borderWidth: 3,
    bottom: -2,
    height: 34,
    justifyContent: 'center',
    position: 'absolute',
    right: -2,
    width: 34,
  },
  photoHint: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '800',
    marginTop: theme.spacing.sm,
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  sectionLabel: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  row: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  rowItem: {
    flex: 1,
  },
  multiline: {
    minHeight: 96,
    paddingTop: theme.spacing.md,
  },
  errorBanner: {
    alignItems: 'center',
    backgroundColor: theme.colors.dangerSoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.md,
    padding: theme.spacing.sm,
  },
  errorText: {
    color: theme.colors.danger,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
  },
});
