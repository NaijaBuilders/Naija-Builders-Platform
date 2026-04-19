import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import FadeInView from '../components/FadeInView';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/materials (Mobile/MaterialsController@index).
export default function MaterialsScreen({ navigation }) {
  const [materials, setMaterials] = useState([]);
  const [meta, setMeta] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadMaterials = async () => {
      try {
        const response = await api.getMaterials();
        setMaterials(response.data.data || []);
        setMeta(response.data.meta || null);
      } catch (err) {
        setError('Unable to load materials.');
      }
    };

    loadMaterials();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Materials Marketplace</Text>
      <Text style={styles.subtitle}>Browse active listings from verified suppliers.</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {materials.map((item, index) => (
        <FadeInView key={item.id} delay={index * 70}>
          <Pressable
            style={styles.card}
            onPress={() => navigation.navigate('MaterialDetail', { materialId: item.id })}
          >
            <Text style={styles.cardTitle}>{item.name}</Text>
            <Text style={styles.cardMeta}>{item.category}</Text>
            <Text style={styles.cardPrice}>NGN {item.price}</Text>
            <Text style={styles.cardMeta}>Supplier: {item.company || item.full_name || 'Supplier'}</Text>
          </Pressable>
        </FadeInView>
      ))}
      {meta ? <Text style={styles.metaText}>Page {meta.current_page} of {meta.last_page}</Text> : null}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  subtitle: {
    marginBottom: 16,
    color: colors.muted,
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
    fontSize: 16,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  cardMeta: {
    marginTop: 4,
    color: colors.muted,
  },
  cardPrice: {
    marginTop: 6,
    fontWeight: '600',
    color: colors.accent,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
  metaText: {
    textAlign: 'center',
    color: colors.muted,
    marginTop: 12,
  },
});
