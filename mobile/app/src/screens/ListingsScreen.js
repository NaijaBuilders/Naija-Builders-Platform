import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/listings (Mobile/ListingController@index).
export default function ListingsScreen({ navigation }) {
  const [listings, setListings] = useState([]);
  const [error, setError] = useState('');

  const loadListings = async () => {
    try {
      const response = await api.getListings();
      setListings(response.data.data || []);
    } catch (err) {
      setError('Unable to load listings.');
    }
  };

  useEffect(() => {
    loadListings();
  }, []);

  return (
    <ScreenWrapper>
      <View style={styles.headerRow}>
        <Text style={styles.title}>Your Listings</Text>
        <Pressable style={styles.createButton} onPress={() => navigation.navigate('CreateListing')}>
          <Text style={styles.createButtonText}>New</Text>
        </Pressable>
      </View>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {listings.map((listing) => (
        <View key={listing.id} style={styles.card}>
          <Text style={styles.cardTitle}>{listing.name}</Text>
          <Text style={styles.meta}>{listing.category}</Text>
          <Text style={styles.meta}>Price: NGN {listing.price}</Text>
          <Text style={styles.meta}>Stock: {listing.stock_qty}</Text>
        </View>
      ))}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  createButton: {
    backgroundColor: colors.accent,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
  },
  createButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
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
