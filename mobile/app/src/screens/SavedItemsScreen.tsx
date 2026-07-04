import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import type { MainTabParamList } from '../navigation/types';
import { savedService } from '../services';
import { theme } from '../theme';
import type { SavedProduct } from '../types';
import { formatCurrency } from '../utils/format';

type SavedNavigation = BottomTabNavigationProp<MainTabParamList>;

export function SavedItemsScreen() {
  const navigation = useNavigation<SavedNavigation>();
  const [items, setItems] = useState<SavedProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    try {
      setItems(await savedService.list());
      setError('');
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not load saved items.'
      );
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const removeItem = async (materialId: string) => {
    setItems((current) => current.filter((item) => item.id !== materialId));
    try {
      await savedService.unsave(materialId);
    } catch {
      load();
    }
  };

  const openProduct = (materialId: string) => {
    navigation.navigate('Browse', {
      screen: 'ProductDetail',
      params: { productId: materialId },
    });
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading saved materials" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Shortlist"
        title="Saved materials"
        subtitle="Compare prices and order when you are ready."
      />

      {error ? <Text style={styles.error}>{error}</Text> : null}

      {items.length === 0 && !error ? (
        <Card style={styles.emptyCard}>
          <View style={styles.emptyIcon}>
            <Ionicons color={theme.colors.danger} name="heart-outline" size={30} />
          </View>
          <Text style={styles.emptyTitle}>Nothing saved yet</Text>
          <Text style={styles.emptyText}>
            Tap the heart on any material to keep it here for easy comparison.
          </Text>
        </Card>
      ) : (
        items.map((item) => (
          <Card key={item.id} style={styles.itemCard}>
            <Pressable
              accessibilityRole="button"
              onPress={() => openProduct(item.id)}
              style={styles.itemRow}
            >
              <View style={styles.itemBadge}>
                <Ionicons
                  color={theme.colors.primary}
                  name="cube-outline"
                  size={22}
                />
              </View>
              <View style={styles.itemCopy}>
                <Text numberOfLines={1} style={styles.itemName}>
                  {item.name}
                </Text>
                <Text numberOfLines={1} style={styles.itemCompany}>
                  {item.company} · {item.category}
                </Text>
                <Text style={styles.itemPrice}>
                  {formatCurrency(item.price)}
                  <Text style={styles.itemUnit}> / {item.priceUnit}</Text>
                </Text>
              </View>
              <Pressable
                accessibilityLabel="Remove from saved"
                accessibilityRole="button"
                hitSlop={8}
                onPress={() => removeItem(item.id)}
                style={styles.removeButton}
              >
                <Ionicons color={theme.colors.danger} name="heart-dislike-outline" size={18} />
              </Pressable>
            </Pressable>
          </Card>
        ))
      )}
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
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
  emptyCard: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xl,
  },
  emptyIcon: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.pill,
    height: 64,
    justifyContent: 'center',
    marginBottom: theme.spacing.xs,
    width: 64,
  },
  emptyTitle: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
  emptyText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    textAlign: 'center',
  },
  itemCard: {
    marginBottom: theme.spacing.md,
    padding: 0,
  },
  itemRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  itemBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 48,
    justifyContent: 'center',
    width: 48,
  },
  itemCopy: {
    flex: 1,
  },
  itemName: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  itemCompany: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '800',
    marginTop: 2,
  },
  itemPrice: {
    color: theme.colors.primaryDark,
    fontSize: 14,
    fontWeight: '900',
    marginTop: 4,
  },
  itemUnit: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '700',
  },
  removeButton: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.md,
    height: 34,
    justifyContent: 'center',
    width: 34,
  },
});
