import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { theme } from '../theme';

type StarRatingProps = {
  rating: number;
  size?: number;
  /** When provided the stars become tappable. */
  onRate?: (rating: number) => void;
};

export function StarRating({ rating, size = 16, onRate }: StarRatingProps) {
  return (
    <View style={styles.row}>
      {[1, 2, 3, 4, 5].map((value) => {
        const icon =
          rating >= value - 0.25
            ? 'star'
            : rating >= value - 0.75
              ? 'star-half'
              : 'star-outline';

        const star = (
          <Ionicons
            color={theme.colors.warning}
            key={`star-${value}`}
            name={icon}
            size={size}
          />
        );

        if (!onRate) {
          return star;
        }

        return (
          <Pressable
            accessibilityLabel={`Rate ${value} star${value === 1 ? '' : 's'}`}
            accessibilityRole="button"
            hitSlop={6}
            key={`star-${value}`}
            onPress={() => onRate(value)}
          >
            {star}
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: 3,
  },
});
