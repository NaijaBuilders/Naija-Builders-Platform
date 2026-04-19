import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to /api/mobile/saved-products (Mobile/SavedMaterialController).
export default function SavedProductsScreen() {
  const [saved, setSaved] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadSaved = async () => {
      try {
        const response = await api.getSavedProducts();
        setSaved(response.data.saved_materials || []);
      } catch (err) {
        setError('Unable to load saved products.');
      }
    };

    loadSaved();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Saved Products</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {saved.map((item) => (
        <View key={item.material_id} style={styles.card}>
          <Text style={styles.cardTitle}>{item.name}</Text>
          <Text style={styles.meta}>{item.category}</Text>
          <Text style={styles.meta}>Supplier: {item.company || item.full_name}</Text>
        </View>
      ))}
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
    marginBottom: 12,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  cardTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
