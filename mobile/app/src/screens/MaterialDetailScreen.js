import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api, MEDIA_BASE_URL } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/materials/{materialId} (Mobile/MaterialsController@show).
export default function MaterialDetailScreen({ route, navigation }) {
  const { materialId } = route.params;
  const [material, setMaterial] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadMaterial = async () => {
      try {
        const response = await api.getMaterial(materialId);
        setMaterial(response.data);
      } catch (err) {
        setError('Unable to load material details.');
      }
    };

    loadMaterial();
  }, [materialId]);

  if (!material) {
    return (
      <ScreenWrapper>
        <Text style={styles.title}>Material Detail</Text>
        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScreenWrapper>
    );
  }

  const data = material.material;
  const images = material.images || [];

  return (
    <ScreenWrapper>
      <View style={styles.card}>
        <Text style={styles.title}>{data.name}</Text>
        <Text style={styles.meta}>{data.category}</Text>
        <Text style={styles.price}>NGN {data.price}</Text>
        <Text style={styles.meta}>Stock: {material.stock_label}</Text>
        <Text style={styles.meta}>Supplier: {material.supplier_name}</Text>
        <Text style={styles.body}>{data.description}</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Images</Text>
        {images.length === 0 ? <Text style={styles.meta}>No images available.</Text> : null}
        {images.map((image, index) => (
          <Text key={`${image.image_path}-${index}`} style={styles.meta}>
            {MEDIA_BASE_URL ? `${MEDIA_BASE_URL}/${image.image_path}` : image.image_path}
          </Text>
        ))}
      </View>

      <Pressable style={styles.primaryButton} onPress={() => navigation.navigate('Messages', { receiver_id: data.supplier_id })}>
        <Text style={styles.primaryButtonText}>Message Supplier</Text>
      </Pressable>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  card: {
    padding: 16,
    marginBottom: 12,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  title: {
    fontSize: 22,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  meta: {
    marginTop: 4,
    color: colors.muted,
  },
  price: {
    marginTop: 6,
    color: colors.accent,
    fontWeight: '600',
  },
  body: {
    marginTop: 10,
    color: colors.ink,
    lineHeight: 20,
  },
  sectionTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
  },
  primaryButton: {
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
    color: '#B23A3A',
  },
});
