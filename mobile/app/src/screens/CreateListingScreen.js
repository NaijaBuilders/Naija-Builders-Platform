import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

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
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const updateField = (key, value) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const handleSubmit = async () => {
    setError('');
    setSuccess('');

    try {
      const formData = new FormData();
      Object.entries(form).forEach(([key, value]) => {
        formData.append(key, value);
      });

      // Image picker integration should append at least 3 images to `images[]`.
      await api.createListing(formData);
      setSuccess('Listing created successfully.');
    } catch (err) {
      const message = err?.response?.data?.message || 'Unable to create listing.';
      setError(message);
    }
  };

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Create Listing</Text>
      <View style={styles.card}>
        <Text style={styles.label}>Name</Text>
        <TextInput style={styles.input} value={form.name} onChangeText={(value) => updateField('name', value)} />

        <Text style={styles.label}>Category</Text>
        <TextInput style={styles.input} value={form.category} onChangeText={(value) => updateField('category', value)} />

        <Text style={styles.label}>Description</Text>
        <TextInput
          style={[styles.input, styles.textArea]}
          multiline
          value={form.description}
          onChangeText={(value) => updateField('description', value)}
        />

        <Text style={styles.label}>Price</Text>
        <TextInput
          style={styles.input}
          keyboardType="numeric"
          value={form.price}
          onChangeText={(value) => updateField('price', value)}
        />

        <Text style={styles.label}>Price Unit</Text>
        <TextInput style={styles.input} value={form.price_unit} onChangeText={(value) => updateField('price_unit', value)} />

        <Text style={styles.label}>Stock Quantity</Text>
        <TextInput
          style={styles.input}
          keyboardType="numeric"
          value={form.stock_qty}
          onChangeText={(value) => updateField('stock_qty', value)}
        />

        <Text style={styles.label}>Status</Text>
        <TextInput style={styles.input} value={form.status} onChangeText={(value) => updateField('status', value)} />

        <Text style={styles.helperText}>Add at least 3 product images via an image picker.</Text>

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {success ? <Text style={styles.success}>{success}</Text> : null}

        <Pressable style={styles.primaryButton} onPress={handleSubmit}>
          <Text style={styles.primaryButtonText}>Publish Listing</Text>
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
  textArea: {
    minHeight: 90,
  },
  helperText: {
    marginTop: 10,
    color: colors.muted,
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
