import React, { useEffect, useState } from 'react';
import { Image, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Badge, Card, EmptyState, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, fonts, radius, spacing } from '../styles/theme';
import { formatMoney, humanize, imageUrl } from '../utils/format';

// Maps to GET /api/mobile/materials/{materialId} (Mobile/MaterialsController@show).
export default function MaterialDetailScreen({ route, navigation }) {
  const { materialId } = route.params;
  const [material, setMaterial] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [quantity, setQuantity] = useState(1);
  const [rating, setRating] = useState(0);
  const [reviewText, setReviewText] = useState('');
  const [notice, setNotice] = useState('');
  const [error, setError] = useState('');

  const loadMaterial = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getMaterial(materialId);
      setMaterial(response.data);
      setRating(response.data.current_user_product_rating || 0);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load material details.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadMaterial();
  }, [materialId]);

  const handleAddToCart = async () => {
    setError('');
    setNotice('');
    try {
      await api.addCartItem({ material_id: materialId, quantity });
      setNotice('Added to cart.');
    } catch (err) {
      setError(err?.response?.data?.message || 'Sign in to add this product to cart.');
    }
  };

  const handleSave = async () => {
    setError('');
    setNotice('');
    try {
      await api.saveProduct({ material_id: materialId });
      setMaterial((current) => ({ ...current, is_saved: true }));
      setNotice('Saved to your products.');
    } catch (err) {
      setError(err?.response?.data?.message || 'Sign in to save this product.');
    }
  };

  const handleRate = async () => {
    if (!rating) {
      setError('Choose a rating before saving.');
      return;
    }

    setError('');
    setNotice('');
    try {
      await api.rateMaterial(materialId, { rating, review_text: reviewText });
      setNotice('Rating saved.');
      await loadMaterial(true);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to save rating.');
    }
  };

  if (isLoading && !material) {
    return (
      <ScreenWrapper>
        <ScreenHeader title="Material Detail" subtitle="Loading product information." />
        <LoadingState />
      </ScreenWrapper>
    );
  }

  if (!material) {
    return (
      <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadMaterial(true)}>
        <ScreenHeader title="Material Detail" />
        <ErrorBanner message={error} />
        <EmptyState title="Material unavailable" body="This listing may no longer be active." actionLabel="Back to Materials" onAction={() => navigation.navigate('Materials')} />
      </ScreenWrapper>
    );
  }

  const data = material.material;
  const images = material.images || [];
  const mainImage = imageUrl(material.main_image_path || images[0]?.image_path);
  const stockQty = Number(material.stock_quantity || data.stock_qty || 0);

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadMaterial(true)}>
      {mainImage ? (
        <Image source={{ uri: mainImage }} style={styles.heroImage} />
      ) : (
        <View style={styles.heroFallback}><Text style={styles.heroFallbackText}>NaijaBuilders</Text></View>
      )}

      <Card style={styles.cardGap}>
        <View style={styles.badgeRow}>
          <Badge label={data.category || 'General'} tone="soft" />
          {Number(data.is_verified_badge || 0) === 1 ? <Badge label="Verified Supplier" tone="success" /> : null}
          {Number(data.is_negotiable || 0) === 1 ? <Badge label="Negotiable" tone="warning" /> : null}
        </View>
        <Text style={styles.title}>{data.name}</Text>
        <Text style={styles.price}>{formatMoney(data.price)} / {humanize(data.price_unit || 'item')}</Text>
        <Text style={styles.meta}>Stock: {material.stock_label}</Text>
        <Text style={styles.meta}>Supplier: {material.supplier_name}</Text>
        <Text style={styles.body}>{data.description || 'No description has been added for this material yet.'}</Text>
      </Card>

      {images.length > 1 ? (
        <View style={styles.thumbRow}>
          {images.map((image, index) => {
            const uri = imageUrl(image.image_path);
            return uri ? <Image key={`${image.image_path}-${index}`} source={{ uri }} style={styles.thumbImage} /> : null;
          })}
        </View>
      ) : null}

      <Card style={styles.cardGap}>
        <Text style={styles.sectionTitle}>Buyer Actions</Text>
        <View style={styles.qtyRow}>
          <Pressable style={styles.qtyButton} onPress={() => setQuantity((value) => Math.max(1, value - 1))}>
            <Text style={styles.qtyText}>-</Text>
          </Pressable>
          <Text style={styles.qtyValue}>{quantity}</Text>
          <Pressable style={styles.qtyButton} onPress={() => setQuantity((value) => Math.min(stockQty || 999, value + 1))}>
            <Text style={styles.qtyText}>+</Text>
          </Pressable>
        </View>
        <View style={styles.actionGrid}>
          <AppButton label="Add to Cart" onPress={handleAddToCart} />
          <AppButton label={material.is_saved ? 'Saved' : 'Save Product'} variant="soft" onPress={handleSave} />
        </View>
        <AppButton label="Message Supplier" variant="outline" onPress={() => navigation.navigate('Messages', { receiver_id: data.supplier_id })} />
      </Card>

      <Card style={styles.cardGap}>
        <Text style={styles.sectionTitle}>Ratings</Text>
        <Text style={styles.meta}>Product rating: {material.product_rating_avg || '0.0'} ({material.product_rating_count || 0})</Text>
        <Text style={styles.meta}>Supplier rating: {material.supplier_rating_avg || '0.0'} ({material.supplier_rating_count || 0})</Text>
        <View style={styles.ratingRow}>
          {[1, 2, 3, 4, 5].map((value) => (
            <Pressable key={value} style={[styles.ratingButton, rating === value ? styles.ratingButtonActive : null]} onPress={() => setRating(value)}>
              <Text style={[styles.ratingText, rating === value ? styles.ratingTextActive : null]}>{value}</Text>
            </Pressable>
          ))}
        </View>
        <TextInput
          style={styles.reviewInput}
          multiline
          placeholder="Optional review"
          placeholderTextColor={colors.mutedSoft}
          value={reviewText}
          onChangeText={setReviewText}
        />
        <AppButton label="Save Rating" variant="soft" onPress={handleRate} />
      </Card>

      {notice ? <Text style={styles.notice}>{notice}</Text> : null}
      <ErrorBanner message={error} />
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  heroImage: {
    aspectRatio: 1.2,
    backgroundColor: '#EEE6DC',
    borderRadius: radius.lg,
    marginBottom: spacing.md,
    width: '100%',
  },
  heroFallback: {
    alignItems: 'center',
    aspectRatio: 1.2,
    backgroundColor: colors.accentSoft,
    borderRadius: radius.lg,
    justifyContent: 'center',
    marginBottom: spacing.md,
    width: '100%',
  },
  heroFallbackText: {
    color: colors.accent,
    fontFamily: fonts.heading,
    fontSize: 22,
  },
  cardGap: {
    marginBottom: spacing.md,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginBottom: 10,
  },
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
    lineHeight: 30,
  },
  meta: {
    marginTop: 4,
    color: colors.muted,
  },
  price: {
    marginTop: 8,
    color: colors.accent,
    fontSize: 18,
    fontWeight: '800',
  },
  body: {
    marginTop: 10,
    color: colors.ink,
    lineHeight: 20,
  },
  sectionTitle: {
    fontFamily: fonts.heading,
    fontSize: 18,
    marginBottom: 8,
  },
  thumbRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: spacing.md,
  },
  thumbImage: {
    backgroundColor: '#EEE6DC',
    borderRadius: radius.sm,
    height: 74,
    width: 74,
  },
  qtyRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: 10,
    marginBottom: spacing.sm,
  },
  qtyButton: {
    alignItems: 'center',
    backgroundColor: colors.accentSoft,
    borderRadius: radius.sm,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  qtyText: {
    color: colors.accent,
    fontSize: 18,
    fontWeight: '800',
  },
  qtyValue: {
    color: colors.ink,
    fontSize: 17,
    fontWeight: '800',
    minWidth: 28,
    textAlign: 'center',
  },
  actionGrid: {
    gap: 8,
    marginBottom: 8,
  },
  ratingRow: {
    flexDirection: 'row',
    gap: 8,
    marginVertical: spacing.sm,
  },
  ratingButton: {
    alignItems: 'center',
    backgroundColor: '#F5F0EA',
    borderRadius: radius.sm,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  ratingButtonActive: {
    backgroundColor: colors.accent,
  },
  ratingText: {
    color: colors.ink,
    fontWeight: '800',
  },
  ratingTextActive: {
    color: '#FFFFFF',
  },
  reviewInput: {
    backgroundColor: '#FFFDF9',
    borderColor: colors.border,
    borderRadius: radius.sm,
    borderWidth: 1,
    color: colors.ink,
    marginBottom: spacing.sm,
    minHeight: 84,
    padding: 12,
    textAlignVertical: 'top',
  },
  notice: {
    color: colors.success,
    fontWeight: '700',
    marginBottom: spacing.sm,
  },
});
