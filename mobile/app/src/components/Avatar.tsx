import React, { useMemo } from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';

type AvatarProps = {
  name: string;
  imageUri?: string;
  size?: number;
};

export function Avatar({ name, imageUri, size = 48 }: AvatarProps) {
  const initials = useMemo(() => {
    const parts = name.trim().split(/\s+/);
    return parts
      .slice(0, 2)
      .map((part) => part.charAt(0).toUpperCase())
      .join('');
  }, [name]);

  if (imageUri) {
    return (
      <Image
        source={{ uri: imageUri }}
        style={[styles.avatar, { height: size, width: size }]}
      />
    );
  }

  return (
    <View style={[styles.avatar, styles.fallback, { height: size, width: size }]}>
      <Text style={[styles.initials, { fontSize: Math.max(13, size * 0.34) }]}>
        {initials}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  avatar: {
    borderRadius: theme.radius.md,
  },
  fallback: {
    alignItems: 'center',
    backgroundColor: theme.colors.primary,
    justifyContent: 'center',
  },
  initials: {
    color: theme.colors.white,
    fontWeight: '900',
  },
});
