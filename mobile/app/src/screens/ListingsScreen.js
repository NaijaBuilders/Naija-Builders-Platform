import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Badge, EmptyState, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';
import { formatMoney, humanize } from '../utils/format';

// Maps to GET /api/mobile/listings (Mobile/ListingController@index).
export default function ListingsScreen({ navigation }) {
  const [listings, setListings] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadListings = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getListings();
      setListings(response.data.data || []);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load listings.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadListings();
  }, []);

  const deleteListing = async (listingId) => {
    try {
      await api.deleteListing(listingId);
      setListings((current) => current.filter((listing) => listing.id !== listingId));
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to delete listing.');
    }
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadListings(true)}>
      <ScreenHeader title="Your Listings" subtitle="Manage the materials you publish to the marketplace." actionLabel="New" onAction={() => navigation.navigate('CreateListing')} />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading listings..." /> : null}
      {!isLoading && listings.length === 0 ? (
        <EmptyState title="No listings yet" body="Create your first supplier listing after KYC approval." actionLabel="Create Listing" onAction={() => navigation.navigate('CreateListing')} />
      ) : null}
      {listings.map((listing) => (
        <View key={listing.id} style={styles.card}>
          <View style={styles.rowBetween}>
            <Text style={styles.cardTitle}>{listing.name}</Text>
            <Badge label={humanize(listing.status)} tone={listing.status === 'active' ? 'success' : 'neutral'} />
          </View>
          <Text style={styles.meta}>{listing.category || 'General'}</Text>
          <Text style={styles.meta}>Price: {formatMoney(listing.price)} / {humanize(listing.price_unit || 'item')}</Text>
          <Text style={styles.meta}>Stock: {listing.stock_qty}</Text>
          <View style={styles.actions}>
            <AppButton label="Delete" variant="danger" size="sm" onPress={() => deleteListing(listing.id)} />
          </View>
        </View>
      ))}
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
  cardTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: colors.ink,
    flex: 1,
    marginRight: spacing.sm,
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  rowBetween: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actions: {
    alignItems: 'flex-start',
    marginTop: spacing.sm,
  },
});
