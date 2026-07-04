import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import {
  Image,
  ImageSourcePropType,
  StyleProp,
  StyleSheet,
  Text,
  View,
  ViewStyle,
} from 'react-native';
import { fallbackProductImage } from '../services/adapters';
import { theme } from '../theme';

type MaterialImageProps = {
  source: ImageSourcePropType;
  style?: StyleProp<ViewStyle>;
  /** Icon size for the placeholder variant. */
  placeholderIconSize?: number;
  /** Show the wordmark under the icon (hide on small thumbnails). */
  showWordmark?: boolean;
};

/**
 * Product image that renders a branded placeholder when the material has no
 * real photo, instead of the misleading generic stock image.
 */
export function MaterialImage({
  source,
  style,
  placeholderIconSize = 30,
  showWordmark = true,
}: MaterialImageProps) {
  if (source === fallbackProductImage) {
    return (
      <View style={[styles.placeholder, style]}>
        <Ionicons
          color={theme.colors.borderStrong}
          name="cube-outline"
          size={placeholderIconSize}
        />
        {showWordmark ? (
          <Text style={styles.wordmark}>Photo coming soon</Text>
        ) : null}
      </View>
    );
  }

  return (
    <Image
      fadeDuration={120}
      progressiveRenderingEnabled
      resizeMethod="resize"
      resizeMode="cover"
      source={source}
      style={style as StyleProp<import('react-native').ImageStyle>}
    />
  );
}

const styles = StyleSheet.create({
  placeholder: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderWidth: 1,
    gap: 6,
    justifyContent: 'center',
  },
  wordmark: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.4,
    textTransform: 'uppercase',
  },
});
