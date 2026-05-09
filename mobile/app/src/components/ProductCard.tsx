import React, { useCallback } from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { Product } from '../types';
import { formatCurrency } from '../utils/format';
import { Badge } from './Badge';
import { Button } from './Button';
import { Card } from './Card';

type ProductCardProps = {
  product: Product;
  animationIndex?: number;
  onPress?: (product: Product) => void;
};

export const ProductCard = React.memo(function ProductCard({
  animationIndex,
  onPress,
  product,
}: ProductCardProps) {
  const supplierMeta = [product.supplierName, product.location]
    .filter(Boolean)
    .join(' - ');
  const handlePress = useCallback(() => {
    onPress?.(product);
  }, [onPress, product]);

  return (
    <Card animationIndex={animationIndex} style={styles.card}>
      <Pressable
        disabled={!onPress}
        onPress={handlePress}
        style={styles.pressable}
      >
        <Image
          fadeDuration={120}
          progressiveRenderingEnabled
          resizeMethod="resize"
          resizeMode="cover"
          source={product.image}
          style={styles.image}
        />
        <View style={styles.body}>
          <View style={styles.row}>
            <Badge label={product.category} tone="primary" />
            <Text style={styles.rating}>
              {product.rating > 0 ? `${product.rating.toFixed(1)} rating` : 'New'}
            </Text>
          </View>
          <Text numberOfLines={2} style={styles.name}>
            {product.name}
          </Text>
          <Text numberOfLines={1} style={styles.meta}>{supplierMeta}</Text>
          <Text numberOfLines={2} style={styles.description}>
            {product.description}
          </Text>
          <View style={styles.footer}>
            <View style={styles.priceWrap}>
              <Text style={styles.price}>{formatCurrency(product.price)}</Text>
              <Text style={styles.unit}>per {product.unit}</Text>
            </View>
            <Button
              title={product.inStock ? 'View' : 'Notify'}
              variant={product.inStock ? 'primary' : 'outline'}
              style={styles.button}
              onPress={handlePress}
            />
          </View>
        </View>
      </Pressable>
    </Card>
  );
});

const styles = StyleSheet.create({
  card: {
    marginBottom: theme.spacing.md,
    overflow: 'hidden',
    padding: 0,
  },
  pressable: {
    flexDirection: 'row',
  },
  image: {
    backgroundColor: theme.colors.surfaceMuted,
    minHeight: 154,
    width: 118,
  },
  body: {
    flex: 1,
    padding: theme.spacing.md,
  },
  row: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  category: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  rating: {
    color: theme.colors.warning,
    fontSize: 12,
    fontWeight: '900',
  },
  name: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
    lineHeight: 21,
    marginTop: theme.spacing.xs,
  },
  meta: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 4,
  },
  description: {
    color: theme.colors.textMuted,
    fontSize: 12,
    lineHeight: 17,
    marginTop: theme.spacing.xs,
  },
  footer: {
    alignItems: 'flex-end',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    justifyContent: 'space-between',
    marginTop: theme.spacing.sm,
  },
  priceWrap: {
    flex: 1,
  },
  price: {
    color: theme.colors.primaryDark,
    fontSize: 16,
    fontWeight: '900',
  },
  unit: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    marginTop: 2,
  },
  button: {
    minHeight: 38,
    minWidth: 76,
  },
});
