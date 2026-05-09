import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useMemo, useState } from 'react';
import {
  Image,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import type { BrowseStackParamList } from '../navigation/types';
import { listingService } from '../services';
import { theme } from '../theme';
import type {
  ListingStatus,
  SupplierListingCreatePayload,
} from '../types';

const priceUnits = ['item', 'bag', 'ton', 'truckload', 'piece', 'meter'];

const statusOptions: Array<{ label: string; value: ListingStatus }> = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Out of stock', value: 'out_of_stock' },
];

const initialForm: SupplierListingCreatePayload = {
  name: '',
  category: '',
  description: '',
  price: '',
  price_unit: 'item',
  stock_qty: '',
  status: 'active',
  is_negotiable: false,
  imageUris: [],
};

type FieldErrors = Partial<
  Record<keyof SupplierListingCreatePayload, string>
>;

type CreateListingNavigation = NativeStackNavigationProp<
  BrowseStackParamList,
  'CreateListing'
>;

export function CreateListingScreen() {
  const navigation = useNavigation<CreateListingNavigation>();
  const [form, setForm] =
    useState<SupplierListingCreatePayload>(initialForm);
  const [errors, setErrors] = useState<FieldErrors>({});
  const [submitError, setSubmitError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  function updateField<K extends keyof SupplierListingCreatePayload>(
    key: K,
    value: SupplierListingCreatePayload[K]
  ) {
    setForm((current) => ({ ...current, [key]: value }));
    setErrors((current) => ({ ...current, [key]: undefined }));
    setSubmitError('');
  }

  const remainingImages = useMemo(
    () => Math.max(0, 10 - form.imageUris.length),
    [form.imageUris.length]
  );

  const pickImages = async () => {
    if (remainingImages === 0) {
      return;
    }

    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      setSubmitError('Photo access is required to add product images.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsMultipleSelection: true,
      mediaTypes: ['images'],
      orderedSelection: true,
      quality: 0.82,
      selectionLimit: remainingImages,
    });

    if (!result.canceled) {
      updateField(
        'imageUris',
        [
          ...form.imageUris,
          ...result.assets.map((asset) => asset.uri),
        ].slice(0, 10)
      );
    }
  };

  const removeImage = (uri: string) => {
    updateField(
      'imageUris',
      form.imageUris.filter((item) => item !== uri)
    );
  };

  const validate = () => {
    const nextErrors: FieldErrors = {};
    const price = Number(form.price);
    const stockQty = Number(form.stock_qty);

    if (form.name.trim().length < 3) {
      nextErrors.name = 'Enter a product name.';
    }

    if (!form.category.trim()) {
      nextErrors.category = 'Enter a category.';
    }

    if (!Number.isFinite(price) || price <= 0) {
      nextErrors.price = 'Enter a valid price.';
    }

    if (!form.stock_qty.trim() || !Number.isInteger(stockQty) || stockQty < 0) {
      nextErrors.stock_qty = 'Enter a valid stock quantity.';
    }

    if (form.imageUris.length < 3) {
      nextErrors.imageUris = 'Add at least 3 product images.';
    }

    setErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const submit = async () => {
    if (!validate()) {
      return;
    }

    setSubmitting(true);
    setSubmitError('');

    try {
      await listingService.createSupplierListing(form);
      setForm(initialForm);
      navigation.navigate('BrowseMain');
    } catch (error) {
      setSubmitError(
        error instanceof Error ? error.message : 'Failed to create listing.'
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Supplier listings"
        title="Create listing"
        subtitle="Add material details, stock, pricing, and product images."
      />

      <Card style={styles.formCard}>
        <Input
          autoCapitalize="words"
          error={errors.name}
          label="Material name"
          onChangeText={(value) => updateField('name', value)}
          placeholder="Premium cement"
          value={form.name}
        />
        <Input
          autoCapitalize="words"
          error={errors.category}
          label="Category"
          onChangeText={(value) => updateField('category', value)}
          placeholder="Cement"
          value={form.category}
        />
        <Input
          label="Description"
          multiline
          onChangeText={(value) => updateField('description', value)}
          placeholder="Product grade, pack size, and delivery notes"
          style={styles.descriptionInput}
          textAlignVertical="top"
          value={form.description}
        />

        <View style={styles.inlineFields}>
          <Input
            containerStyle={styles.inlineField}
            error={errors.price}
            keyboardType="decimal-pad"
            label="Price"
            onChangeText={(value) => updateField('price', value)}
            placeholder="0"
            value={form.price}
          />
          <Input
            containerStyle={styles.inlineField}
            error={errors.stock_qty}
            keyboardType="number-pad"
            label="Stock"
            onChangeText={(value) => updateField('stock_qty', value)}
            placeholder="0"
            value={form.stock_qty}
          />
        </View>

        <View>
          <Text style={styles.label}>Price unit</Text>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.optionRail}
          >
            {priceUnits.map((unit) => (
              <Pressable
                accessibilityRole="button"
                key={unit}
                onPress={() => updateField('price_unit', unit)}
                style={styles.optionButton}
              >
                <Badge
                  label={unit}
                  tone={form.price_unit === unit ? 'primary' : 'neutral'}
                />
              </Pressable>
            ))}
          </ScrollView>
        </View>

        <View>
          <Text style={styles.label}>Listing status</Text>
          <View style={styles.segmented}>
            {statusOptions.map((option) => {
              const active = form.status === option.value;

              return (
                <Pressable
                  accessibilityRole="button"
                  key={option.value}
                  onPress={() => updateField('status', option.value)}
                  style={[
                    styles.segment,
                    active ? styles.segmentActive : null,
                  ]}
                >
                  <Text
                    style={[
                      styles.segmentText,
                      active ? styles.segmentTextActive : null,
                    ]}
                  >
                    {option.label}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        </View>

        <Pressable
          accessibilityRole="checkbox"
          accessibilityState={{ checked: form.is_negotiable }}
          onPress={() =>
            updateField('is_negotiable', !form.is_negotiable)
          }
          style={styles.toggleRow}
        >
          <Ionicons
            color={
              form.is_negotiable
                ? theme.colors.primary
                : theme.colors.textSubtle
            }
            name={
              form.is_negotiable
                ? 'checkmark-circle'
                : 'ellipse-outline'
            }
            size={22}
          />
          <Text style={styles.toggleText}>Price is negotiable</Text>
        </Pressable>
      </Card>

      <Card style={styles.imageCard}>
        <View style={styles.imageHeader}>
          <View>
            <Text style={styles.cardTitle}>Product images</Text>
            <Text style={styles.imageMeta}>
              {form.imageUris.length}/10 selected
            </Text>
          </View>
          <Pressable
            accessibilityRole="button"
            disabled={remainingImages === 0}
            onPress={pickImages}
            style={[
              styles.imagePickerButton,
              remainingImages === 0 ? styles.disabledButton : null,
            ]}
          >
            <Ionicons
              color={theme.colors.white}
              name="images-outline"
              size={18}
            />
            <Text style={styles.imagePickerText}>Choose</Text>
          </Pressable>
        </View>

        {errors.imageUris ? (
          <Text style={styles.errorText}>{errors.imageUris}</Text>
        ) : null}

        {form.imageUris.length > 0 ? (
          <View style={styles.imageGrid}>
            {form.imageUris.map((uri, index) => (
              <View key={`${uri}-${index}`} style={styles.imageTile}>
                <Image source={{ uri }} style={styles.imagePreview} />
                <Pressable
                  accessibilityRole="button"
                  onPress={() => removeImage(uri)}
                  style={styles.removeImage}
                >
                  <Ionicons
                    color={theme.colors.white}
                    name="close"
                    size={15}
                  />
                </Pressable>
              </View>
            ))}
          </View>
        ) : (
          <View style={styles.emptyImages}>
            <Ionicons
              color={theme.colors.textSubtle}
              name="image-outline"
              size={28}
            />
          </View>
        )}
      </Card>

      {submitError ? <Text style={styles.submitError}>{submitError}</Text> : null}

      <Button
        disabled={submitting}
        loading={submitting}
        onPress={submit}
        title="Publish listing"
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  formCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  descriptionInput: {
    minHeight: 104,
    paddingTop: theme.spacing.md,
  },
  inlineFields: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  inlineField: {
    flex: 1,
  },
  label: {
    color: theme.colors.text,
    fontSize: 13,
    fontWeight: '800',
    marginBottom: theme.spacing.xs,
  },
  optionRail: {
    gap: theme.spacing.sm,
  },
  optionButton: {
    justifyContent: 'center',
    minHeight: 36,
  },
  segmented: {
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    padding: 4,
  },
  segment: {
    alignItems: 'center',
    borderRadius: theme.radius.sm,
    flex: 1,
    justifyContent: 'center',
    minHeight: 40,
    paddingHorizontal: theme.spacing.xs,
  },
  segmentActive: {
    backgroundColor: theme.colors.primary,
  },
  segmentText: {
    color: theme.colors.textMuted,
    fontSize: 12,
    fontWeight: '900',
    textAlign: 'center',
  },
  segmentTextActive: {
    color: theme.colors.white,
  },
  toggleRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    minHeight: 38,
  },
  toggleText: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '800',
  },
  imageCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  imageHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cardTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  imageMeta: {
    color: theme.colors.textMuted,
    fontSize: 12,
    fontWeight: '800',
    marginTop: 3,
  },
  imagePickerButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.primary,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    minHeight: 40,
    paddingHorizontal: theme.spacing.md,
  },
  disabledButton: {
    opacity: 0.55,
  },
  imagePickerText: {
    color: theme.colors.white,
    fontSize: 13,
    fontWeight: '900',
  },
  imageGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  imageTile: {
    borderRadius: theme.radius.md,
    height: 86,
    overflow: 'hidden',
    width: '30.8%',
  },
  imagePreview: {
    backgroundColor: theme.colors.surfaceMuted,
    height: '100%',
    width: '100%',
  },
  removeImage: {
    alignItems: 'center',
    backgroundColor: 'rgba(17, 24, 39, 0.72)',
    borderRadius: theme.radius.pill,
    height: 24,
    justifyContent: 'center',
    position: 'absolute',
    right: 6,
    top: 6,
    width: 24,
  },
  emptyImages: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderStyle: 'dashed',
    borderWidth: 1,
    justifyContent: 'center',
    minHeight: 112,
  },
  errorText: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
  },
  submitError: {
    color: theme.colors.danger,
    fontSize: 13,
    lineHeight: 19,
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
});
