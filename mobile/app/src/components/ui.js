import React from 'react';
import {
  ActivityIndicator,
  Image,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { colors, fonts, radius, spacing } from '../styles/theme';
import { formatMoney, humanize, imageUrl, supplierName } from '../utils/format';

export function ScreenHeader({ title, subtitle, actionLabel, onAction }) {
  return (
    <View style={styles.header}>
      <View style={styles.headerText}>
        <Text style={styles.title}>{title}</Text>
        {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
      </View>
      {actionLabel && onAction ? (
        <AppButton label={actionLabel} onPress={onAction} size="sm" />
      ) : null}
    </View>
  );
}

export function Card({ children, style }) {
  return <View style={[styles.card, style]}>{children}</View>;
}

export function AppButton({ label, onPress, variant = 'primary', size = 'md', disabled = false }) {
  return (
    <Pressable
      onPress={onPress}
      disabled={disabled}
      style={({ pressed }) => [
        styles.button,
        styles[`button_${variant}`],
        styles[`button_${size}`],
        pressed && !disabled ? styles.buttonPressed : null,
        disabled ? styles.buttonDisabled : null,
      ]}
    >
      <Text style={[styles.buttonText, styles[`buttonText_${variant}`]]}>{label}</Text>
    </Pressable>
  );
}

export function TextField({ label, value, onChangeText, multiline = false, style, ...props }) {
  return (
    <View style={styles.fieldWrap}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        value={value}
        onChangeText={onChangeText}
        multiline={multiline}
        style={[styles.input, multiline ? styles.textArea : null, style]}
        placeholderTextColor={colors.mutedSoft}
        {...props}
      />
    </View>
  );
}

export function Badge({ label, tone = 'neutral' }) {
  return (
    <View style={[styles.badge, styles[`badge_${tone}`]]}>
      <Text style={[styles.badgeText, styles[`badgeText_${tone}`]]}>{label}</Text>
    </View>
  );
}

export function MetricCard({ label, value, tone = 'default' }) {
  return (
    <View style={[styles.metricCard, styles[`metric_${tone}`]]}>
      <Text style={styles.metricLabel}>{label}</Text>
      <Text style={styles.metricValue}>{value}</Text>
    </View>
  );
}

export function LoadingState({ label = 'Loading...' }) {
  return (
    <Card style={styles.stateCard}>
      <ActivityIndicator color={colors.accent} />
      <Text style={styles.stateText}>{label}</Text>
    </Card>
  );
}

export function EmptyState({ title, body, actionLabel, onAction }) {
  return (
    <Card style={styles.stateCard}>
      <Text style={styles.emptyTitle}>{title}</Text>
      {body ? <Text style={styles.stateText}>{body}</Text> : null}
      {actionLabel && onAction ? (
        <AppButton label={actionLabel} onPress={onAction} variant="soft" size="sm" />
      ) : null}
    </Card>
  );
}

export function ErrorBanner({ message }) {
  if (!message) {
    return null;
  }

  return (
    <View style={styles.errorBanner}>
      <Text style={styles.errorText}>{message}</Text>
    </View>
  );
}

export function ProductCard({ item, onPress, onSave, onCart, isSaved = false }) {
  const source = imageUrl(item?.image_path);
  const supplier = supplierName(item);
  const rating = item?.product_rating_avg ? `${item.product_rating_avg}/5` : 'New';

  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.productCard, pressed ? styles.cardPressed : null]}>
      {source ? (
        <Image source={{ uri: source }} style={styles.productImage} />
      ) : (
        <View style={styles.productImageFallback}>
          <Text style={styles.productImageFallbackText}>NB</Text>
        </View>
      )}
      <View style={styles.productBody}>
        <View style={styles.productTopRow}>
          <Badge label={item?.category || 'General'} tone="soft" />
          {Number(item?.is_verified_badge || 0) === 1 ? <Badge label="Verified" tone="success" /> : null}
        </View>
        <Text style={styles.productTitle} numberOfLines={2}>{item?.name}</Text>
        <Text style={styles.productSupplier} numberOfLines={1}>{supplier}</Text>
        <View style={styles.productFooter}>
          <View>
            <Text style={styles.productPrice}>{formatMoney(item?.price)}</Text>
            <Text style={styles.productMeta}>{humanize(item?.price_unit || 'item')} · {rating}</Text>
          </View>
          <View style={styles.productActions}>
            {onSave ? (
              <Pressable onPress={onSave} style={[styles.iconButton, isSaved ? styles.iconButtonActive : null]}>
                <Text style={[styles.iconButtonText, isSaved ? styles.iconButtonTextActive : null]}>
                  {isSaved ? 'Saved' : 'Save'}
                </Text>
              </Pressable>
            ) : null}
            {onCart ? (
              <Pressable onPress={onCart} style={styles.iconButton}>
                <Text style={styles.iconButtonText}>Cart</Text>
              </Pressable>
            ) : null}
          </View>
        </View>
      </View>
    </Pressable>
  );
}

export function QuickAction({ label, detail, onPress }) {
  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.quickAction, pressed ? styles.cardPressed : null]}>
      <Text style={styles.quickActionTitle}>{label}</Text>
      {detail ? <Text style={styles.quickActionDetail}>{detail}</Text> : null}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.md,
    marginBottom: spacing.md,
  },
  headerText: {
    flex: 1,
  },
  title: {
    color: colors.ink,
    fontFamily: fonts.heading,
    fontSize: 27,
    lineHeight: 33,
  },
  subtitle: {
    color: colors.muted,
    fontSize: 14,
    lineHeight: 20,
    marginTop: 5,
  },
  card: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderRadius: radius.md,
    borderWidth: 1,
    padding: spacing.md,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.06,
    shadowRadius: 14,
    elevation: 2,
  },
  cardPressed: {
    opacity: 0.86,
    transform: [{ scale: 0.995 }],
  },
  button: {
    alignItems: 'center',
    borderRadius: radius.sm,
    justifyContent: 'center',
  },
  button_md: {
    paddingHorizontal: spacing.md,
    paddingVertical: 12,
  },
  button_sm: {
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  button_primary: {
    backgroundColor: colors.accent,
  },
  button_soft: {
    backgroundColor: colors.accentSoft,
  },
  button_outline: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderWidth: 1,
  },
  button_danger: {
    backgroundColor: '#F8DEDE',
  },
  buttonDisabled: {
    opacity: 0.55,
  },
  buttonPressed: {
    opacity: 0.78,
  },
  buttonText: {
    fontWeight: '700',
  },
  buttonText_primary: {
    color: '#FFFFFF',
  },
  buttonText_soft: {
    color: colors.accent,
  },
  buttonText_outline: {
    color: colors.ink,
  },
  buttonText_danger: {
    color: colors.danger,
  },
  fieldWrap: {
    marginTop: spacing.sm,
  },
  label: {
    color: colors.muted,
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  input: {
    backgroundColor: '#FFFDF9',
    borderColor: colors.border,
    borderRadius: radius.sm,
    borderWidth: 1,
    color: colors.ink,
    padding: 12,
  },
  textArea: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  badge: {
    borderRadius: 999,
    paddingHorizontal: 9,
    paddingVertical: 4,
  },
  badge_neutral: {
    backgroundColor: '#F3F0EB',
  },
  badge_soft: {
    backgroundColor: colors.highlight,
  },
  badge_success: {
    backgroundColor: '#DDF4E8',
  },
  badge_warning: {
    backgroundColor: '#FEF2D4',
  },
  badge_danger: {
    backgroundColor: '#F8DEDE',
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '700',
  },
  badgeText_neutral: {
    color: colors.muted,
  },
  badgeText_soft: {
    color: colors.ink,
  },
  badgeText_success: {
    color: colors.success,
  },
  badgeText_warning: {
    color: colors.warning,
  },
  badgeText_danger: {
    color: colors.danger,
  },
  metricCard: {
    borderRadius: radius.md,
    flexBasis: '48%',
    flexGrow: 1,
    padding: spacing.md,
  },
  metric_default: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderWidth: 1,
  },
  metric_accent: {
    backgroundColor: colors.accentSoft,
  },
  metric_warning: {
    backgroundColor: '#FEF2D4',
  },
  metric_success: {
    backgroundColor: '#DDF4E8',
  },
  metricLabel: {
    color: colors.muted,
    fontSize: 12,
    marginBottom: 6,
  },
  metricValue: {
    color: colors.ink,
    fontSize: 20,
    fontWeight: '800',
  },
  stateCard: {
    alignItems: 'center',
    gap: 8,
    marginVertical: spacing.sm,
  },
  stateText: {
    color: colors.muted,
    lineHeight: 20,
    textAlign: 'center',
  },
  emptyTitle: {
    color: colors.ink,
    fontFamily: fonts.heading,
    fontSize: 18,
    textAlign: 'center',
  },
  errorBanner: {
    backgroundColor: '#F8DEDE',
    borderColor: '#F0B4B4',
    borderRadius: radius.sm,
    borderWidth: 1,
    marginBottom: spacing.sm,
    padding: 11,
  },
  errorText: {
    color: colors.danger,
  },
  productCard: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderRadius: radius.md,
    borderWidth: 1,
    marginBottom: spacing.md,
    overflow: 'hidden',
  },
  productImage: {
    aspectRatio: 1.65,
    backgroundColor: '#EEE6DC',
    width: '100%',
  },
  productImageFallback: {
    alignItems: 'center',
    aspectRatio: 1.65,
    backgroundColor: colors.accentSoft,
    justifyContent: 'center',
    width: '100%',
  },
  productImageFallbackText: {
    color: colors.accent,
    fontFamily: fonts.heading,
    fontSize: 28,
  },
  productBody: {
    padding: spacing.md,
  },
  productTopRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginBottom: 9,
  },
  productTitle: {
    color: colors.ink,
    fontFamily: fonts.heading,
    fontSize: 18,
    lineHeight: 23,
  },
  productSupplier: {
    color: colors.muted,
    marginTop: 4,
  },
  productFooter: {
    alignItems: 'flex-end',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: spacing.sm,
    gap: spacing.sm,
  },
  productPrice: {
    color: colors.accent,
    fontSize: 17,
    fontWeight: '800',
  },
  productMeta: {
    color: colors.muted,
    fontSize: 12,
    marginTop: 3,
  },
  productActions: {
    flexDirection: 'row',
    gap: 6,
  },
  iconButton: {
    backgroundColor: '#F5F0EA',
    borderRadius: radius.xs,
    paddingHorizontal: 9,
    paddingVertical: 7,
  },
  iconButtonActive: {
    backgroundColor: colors.accentSoft,
  },
  iconButtonText: {
    color: colors.ink,
    fontSize: 12,
    fontWeight: '700',
  },
  iconButtonTextActive: {
    color: colors.accent,
  },
  quickAction: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderRadius: radius.md,
    borderWidth: 1,
    flexBasis: '48%',
    flexGrow: 1,
    padding: spacing.md,
  },
  quickActionTitle: {
    color: colors.ink,
    fontWeight: '800',
  },
  quickActionDetail: {
    color: colors.muted,
    fontSize: 12,
    lineHeight: 18,
    marginTop: 5,
  },
});

