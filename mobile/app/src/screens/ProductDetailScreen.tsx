import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import {
  Avatar,
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import { useProduct } from '../hooks/useMarketplaceData';
import { theme } from '../theme';
import { formatCurrency } from '../utils/format';

type ProductDetailScreenProps = {
  route: {
    params: {
      productId: string;
    };
  };
};

export function ProductDetailScreen({ route }: ProductDetailScreenProps) {
  const { data: product, error, loading } = useProduct(route.params.productId);

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

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton tint="auto" />}
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
        <Text style={styles.description}>{product.description}</Text>
        <View style={styles.actions}>
          <Button
            title="Request quote"
            onPress={() => undefined}
            style={styles.actionButton}
          />
          <Button
            title="Buy now"
            onPress={() => undefined}
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
  description: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.body,
    lineHeight: 23,
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
});
