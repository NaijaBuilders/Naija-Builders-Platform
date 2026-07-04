import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React, { useEffect, useState } from 'react';
import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import {
  Avatar,
  Badge,
  Button,
  Card,
  FloatingBackButton,
  FloatingIconButton,
  Header,
  Input,
  Loader,
  Screen,
  StarRating,
} from '../components';
import { useCart } from '../context/CartContext';
import { useProduct } from '../hooks/useMarketplaceData';
import type { HomeStackParamList } from '../navigation/types';
import { orderService, savedService } from '../services';
import { theme } from '../theme';
import { formatCurrency } from '../utils/format';

type ProductDetailScreenProps = {
  route: {
    params: {
      productId: string;
    };
  };
};

type ProductDetailNavigation = NativeStackNavigationProp<
  HomeStackParamList,
  'ProductDetail'
>;

export function ProductDetailScreen({ route }: ProductDetailScreenProps) {
  const navigation = useNavigation<ProductDetailNavigation>();
  const { data: product, error, loading, refresh } = useProduct(
    route.params.productId
  );
  const { addToCart } = useCart();

  const [saved, setSaved] = useState(false);
  const [cartFeedback, setCartFeedback] = useState('');
  const [cartBusy, setCartBusy] = useState('');
  const [myRating, setMyRating] = useState(0);
  const [reviewText, setReviewText] = useState('');
  const [reviewBusy, setReviewBusy] = useState(false);
  const [reviewNotice, setReviewNotice] = useState('');

  useEffect(() => {
    if (product?.detail) {
      setSaved(product.detail.isSaved);
      setMyRating(product.detail.currentUserProductRating ?? 0);
    }
  }, [product]);

  const toggleSaved = async () => {
    if (!product) {
      return;
    }

    const next = !saved;
    setSaved(next);
    try {
      if (next) {
        await savedService.save(product.id);
      } else {
        await savedService.unsave(product.id);
      }
    } catch {
      setSaved(!next);
    }
  };

  const handleAddToCart = async (goToCart: boolean) => {
    if (!product) {
      return;
    }

    setCartBusy(goToCart ? 'buy' : 'add');
    setCartFeedback('');
    try {
      await addToCart(product.id, 1);
      if (goToCart) {
        navigation.navigate('Cart');
      } else {
        setCartFeedback('Added to cart.');
      }
    } catch (reason) {
      setCartFeedback(
        reason instanceof Error ? reason.message : 'Could not add to cart.'
      );
    } finally {
      setCartBusy('');
    }
  };

  const submitReview = async () => {
    if (!product || myRating < 1) {
      return;
    }

    setReviewBusy(true);
    setReviewNotice('');
    try {
      await orderService.rateProduct(product.id, myRating, reviewText.trim());
      setReviewNotice('Thanks — your rating has been saved.');
      setReviewText('');
      refresh();
    } catch (reason) {
      setReviewNotice(
        reason instanceof Error ? reason.message : 'Could not save your rating.'
      );
    } finally {
      setReviewBusy(false);
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton tint="auto" />}
      >
        <Loader label="Loading product" />
      </Screen>
    );
  }

  if (!product) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton tint="auto" />}
      >
        <Text style={styles.empty}>
          {error?.message || 'Product not found.'}
        </Text>
      </Screen>
    );
  }

  const detail = product.detail;

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={
        <>
          <FloatingBackButton tint="auto" />
          <FloatingIconButton
            accessibilityLabel={saved ? 'Remove from saved' : 'Save material'}
            icon={saved ? 'heart' : 'heart-outline'}
            iconColor={saved ? theme.colors.danger : theme.colors.text}
            onPress={toggleSaved}
          />
        </>
      }
    >
      <Header
        eyebrow={product.category}
        title={product.name}
        subtitle={`${product.location} - ${product.supplierName}`}
      />

      <Image
        fadeDuration={120}
        progressiveRenderingEnabled
        resizeMethod="resize"
        resizeMode="cover"
        source={product.images[0]}
        style={styles.heroImage}
      />
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.imageRail}
      >
        {product.images.map((image, index) => (
          <Image
            fadeDuration={80}
            key={`${product.id}-${index}`}
            resizeMethod="resize"
            source={image}
            style={styles.image}
          />
        ))}
      </ScrollView>

      <Card style={styles.card}>
        <View style={styles.priceRow}>
          <View>
            <Text style={styles.price}>{formatCurrency(product.price)}</Text>
            <Text style={styles.unit}>per {product.unit}</Text>
          </View>
          <Badge
            label={product.inStock ? `${product.stock_count} in stock` : 'Out of stock'}
            tone={product.inStock ? 'success' : 'warning'}
          />
        </View>

        {detail && detail.productRatingCount > 0 ? (
          <View style={styles.ratingRow}>
            <StarRating rating={detail.productRatingAvg ?? 0} />
            <Text style={styles.ratingText}>
              {detail.productRatingAvg?.toFixed(1)} ·{' '}
              {detail.productRatingCount} review
              {detail.productRatingCount === 1 ? '' : 's'}
            </Text>
          </View>
        ) : null}

        <Text style={styles.description}>{product.description}</Text>

        {cartFeedback ? (
          <Text style={styles.cartFeedback}>{cartFeedback}</Text>
        ) : null}
        <View style={styles.actions}>
          <Button
            disabled={!product.inStock || cartBusy !== ''}
            loading={cartBusy === 'add'}
            title="Add to cart"
            onPress={() => handleAddToCart(false)}
            style={styles.actionButton}
          />
          <Button
            disabled={!product.inStock || cartBusy !== ''}
            loading={cartBusy === 'buy'}
            title="Buy now"
            onPress={() => handleAddToCart(true)}
            variant="secondary"
            style={styles.actionButton}
          />
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Supplier</Text>
        <View style={styles.supplierRow}>
          <Avatar name={product.supplier.company} size={52} />
          <View style={styles.supplierCopy}>
            <Text style={styles.supplierName}>{product.supplier.company}</Text>
            <Text style={styles.supplierMeta}>{product.supplier.name}</Text>
          </View>
          {product.supplier.verified ? (
            <Badge label="Verified" tone="success" />
          ) : null}
        </View>
        <View style={styles.supplierStats}>
          <View style={styles.supplierStat}>
            <Ionicons color={theme.colors.warning} name="star" size={15} />
            <Text style={styles.supplierStatText}>
              {product.supplier.rating > 0
                ? product.supplier.rating.toFixed(1)
                : 'New'}
            </Text>
          </View>
          <View style={styles.supplierStat}>
            <Ionicons
              color={theme.colors.textMuted}
              name="location-outline"
              size={15}
            />
            <Text style={styles.supplierStatText}>
              {product.supplier.location}
            </Text>
          </View>
          <View style={styles.supplierStat}>
            <Ionicons
              color={theme.colors.textMuted}
              name="time-outline"
              size={15}
            />
            <Text style={styles.supplierStatText}>
              {product.supplier.response_time}
            </Text>
          </View>
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Reviews</Text>

        {detail && detail.reviews.length > 0 ? (
          detail.reviews.map((review, index) => (
            <View
              key={`review-${index}`}
              style={[
                styles.reviewItem,
                index < detail.reviews.length - 1 ? styles.reviewBorder : null,
              ]}
            >
              <View style={styles.reviewTop}>
                <Text style={styles.reviewerName}>{review.reviewerName}</Text>
                <StarRating rating={review.rating} size={13} />
              </View>
              {review.reviewText ? (
                <Text style={styles.reviewText}>{review.reviewText}</Text>
              ) : null}
            </View>
          ))
        ) : (
          <Text style={styles.noReviews}>
            No reviews yet. Be the first to rate this material.
          </Text>
        )}

        <View style={styles.rateBox}>
          <Text style={styles.rateTitle}>
            {detail?.currentUserProductRating
              ? 'Update your rating'
              : 'Rate this material'}
          </Text>
          <StarRating onRate={setMyRating} rating={myRating} size={26} />
          <Input
            label="Add a comment (optional)"
            multiline
            onChangeText={setReviewText}
            placeholder="Quality, delivery time, packaging…"
            style={styles.reviewInput}
            textAlignVertical="top"
            value={reviewText}
          />
          {reviewNotice ? (
            <Text style={styles.reviewNotice}>{reviewNotice}</Text>
          ) : null}
          <Button
            disabled={myRating < 1 || reviewBusy}
            loading={reviewBusy}
            onPress={submitReview}
            title="Submit rating"
            variant="outline"
          />
        </View>
      </Card>
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
  imageRail: {
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.md,
  },
  heroImage: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    height: 250,
    marginBottom: theme.spacing.sm,
    width: '100%',
  },
  image: {
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.sm,
    borderWidth: 1,
    height: 68,
    width: 88,
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  priceRow: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  price: {
    color: theme.colors.primaryDark,
    fontSize: 24,
    fontWeight: '900',
  },
  unit: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    marginTop: 2,
  },
  ratingRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  ratingText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '800',
  },
  description: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.body,
    lineHeight: 23,
  },
  cartFeedback: {
    color: theme.colors.success,
    fontWeight: '800',
    textAlign: 'center',
  },
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  actionButton: {
    flex: 1,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    textAlign: 'center',
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
  },
  supplierRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  supplierCopy: {
    flex: 1,
  },
  supplierName: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
  supplierMeta: {
    color: theme.colors.textMuted,
    lineHeight: 21,
  },
  supplierStats: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
    padding: theme.spacing.md,
  },
  supplierStat: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: 5,
  },
  supplierStatText: {
    color: theme.colors.text,
    fontSize: 13,
    fontWeight: '800',
  },
  reviewItem: {
    gap: theme.spacing.xs,
    paddingBottom: theme.spacing.md,
  },
  reviewBorder: {
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
  },
  reviewTop: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  reviewerName: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '900',
  },
  reviewText: {
    color: theme.colors.textMuted,
    fontSize: 13.5,
    lineHeight: 20,
  },
  noReviews: {
    color: theme.colors.textMuted,
    lineHeight: 21,
  },
  rateBox: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  rateTitle: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '900',
  },
  reviewInput: {
    minHeight: 70,
    paddingTop: theme.spacing.sm,
  },
  reviewNotice: {
    color: theme.colors.success,
    fontWeight: '800',
  },
});
