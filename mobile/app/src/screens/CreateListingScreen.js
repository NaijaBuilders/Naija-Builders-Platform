import React, { useState } from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, ErrorBanner, ScreenHeader, TextField } from '../components/ui';
import { api } from '../services/api';
import { colors, radius, spacing } from '../styles/theme';

// Maps to POST /api/mobile/listings (Mobile/ListingController@store).
export default function CreateListingScreen() {
  const [form, setForm] = useState({
    name: '',
    category: '',
    description: '',
    price: '',
    price_unit: 'item',
    stock_qty: '',
    status: 'active',
  });
  const [images, setImages] = useState([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const updateField = (key, value) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const pickImages = async () => {
    setError('');
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      setError('Photo library permission is required to upload listing images.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsMultipleSelection: true,
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.85,
      selectionLimit: 10,
    });

    if (result.canceled) {
      return;
    }

    setImages((current) => {
      const nextImages = [...current, ...(result.assets || [])];
      return nextImages.slice(0, 10);
    });
  };

  const removeImage = (uri) => {
    setImages((current) => current.filter((image) => image.uri !== uri));
  };

  const handleSubmit = async () => {
    setError('');
    setSuccess('');
    if (images.length < 3) {
      setError('Please add at least 3 product images.');
      return;
    }

    setIsSubmitting(true);
    try {
      const formData = new FormData();
      Object.entries(form).forEach(([key, value]) => {
        formData.append(key, value);
      });

      images.forEach((image, index) => {
        const uriParts = String(image.uri || '').split('.');
        const extension = (uriParts.pop() || 'jpg').toLowerCase();
        formData.append('images[]', {
          uri: image.uri,
          name: `listing-${index + 1}.${extension}`,
          type: image.mimeType || `image/${extension === 'jpg' ? 'jpeg' : extension}`,
        });
      });

      await api.createListing(formData);
      setSuccess('Listing created successfully.');
      setForm({
        name: '',
        category: '',
        description: '',
        price: '',
        price_unit: 'item',
        stock_qty: '',
        status: 'active',
      });
      setImages([]);
    } catch (err) {
      const message = err?.response?.data?.message || 'Unable to create listing.';
      setError(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <ScreenWrapper>
      <ScreenHeader title="Create Listing" subtitle="Publish materials with clear photos, pricing, stock, and status." />
      <Card>
        <TextField label="Name" value={form.name} onChangeText={(value) => updateField('name', value)} />

        <TextField label="Category" value={form.category} onChangeText={(value) => updateField('category', value)} />

        <TextField
          label="Description"
          multiline
          value={form.description}
          onChangeText={(value) => updateField('description', value)}
        />

        <TextField
          label="Price"
          keyboardType="numeric"
          value={form.price}
          onChangeText={(value) => updateField('price', value)}
        />

        <TextField label="Price Unit" value={form.price_unit} onChangeText={(value) => updateField('price_unit', value)} />

        <TextField
          label="Stock Quantity"
          keyboardType="numeric"
          value={form.stock_qty}
          onChangeText={(value) => updateField('stock_qty', value)}
        />

        <TextField label="Status" value={form.status} onChangeText={(value) => updateField('status', value)} />

        <View style={styles.imageHeader}>
          <Text style={styles.helperText}>Product Images ({images.length}/10)</Text>
          <AppButton label="Add Images" variant="soft" size="sm" onPress={pickImages} />
        </View>
        <View style={styles.imageGrid}>
          {images.map((image) => (
            <Pressable key={image.uri} onPress={() => removeImage(image.uri)}>
              <Image source={{ uri: image.uri }} style={styles.previewImage} />
              <Text style={styles.removeImageText}>Remove</Text>
            </Pressable>
          ))}
        </View>

        <ErrorBanner message={error} />
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <AppButton label={isSubmitting ? 'Publishing...' : 'Publish Listing'} onPress={handleSubmit} disabled={isSubmitting} />
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  helperText: {
    color: colors.ink,
    fontWeight: '800',
  },
  imageHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: spacing.md,
  },
  imageGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginTop: spacing.sm,
  },
  previewImage: {
    backgroundColor: '#EEE6DC',
    borderRadius: radius.sm,
    height: 84,
    width: 84,
  },
  removeImageText: {
    color: colors.muted,
    fontSize: 11,
    marginTop: 3,
    textAlign: 'center',
  },
  success: {
    marginTop: 8,
    color: '#2D7A4B',
  },
});
