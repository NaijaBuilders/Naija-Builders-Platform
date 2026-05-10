import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import React, { useEffect, useMemo, useState } from 'react';
import {
  Pressable,
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
  Loader,
  Screen,
} from '../components';
import { useAppState } from '../context/AppContext';
import { supplierOnboardingService } from '../services';
import { theme } from '../theme';
import type {
  StatusTone,
  SupplierBankDetailsPayload,
  SupplierBusinessDetailsPayload,
  SupplierIdentityVerificationPayload,
  SupplierOnboardingApplication,
  SupplierOnboardingStatus,
} from '../types';

const GENERIC_REJECTION_MESSAGE =
  'We could not verify your supplier application at this time.';

const documentTypes = ['national_id', 'voter_card', 'drivers_license', 'passport'];

const initialBusinessForm: SupplierBusinessDetailsPayload = {
  cac_number: '',
  business_name: '',
  business_type: '',
  business_address: '',
  state: '',
  contact_name: '',
  contact_email: '',
  contact_phone: '',
};

const initialIdentityForm: SupplierIdentityVerificationPayload = {
  bvn: '',
  id_document_type: 'national_id',
  idDocumentUri: '',
  nin: '',
  selfieUri: '',
};

const initialBankForm: SupplierBankDetailsPayload = {
  account_name: '',
  account_number: '',
  bank_code: '',
  bank_name: '',
};

const statusCopy: Record<
  SupplierOnboardingStatus,
  { label: string; tone: StatusTone; body: string }
> = {
  APPROVED: {
    body: 'Your supplier verification is approved.',
    label: 'Approved',
    tone: 'success',
  },
  DRAFT: {
    body: 'Complete each section and submit your application.',
    label: 'Draft',
    tone: 'neutral',
  },
  MANUAL_REVIEW: {
    body: 'Your application is being reviewed by the NaijaBuilders team.',
    label: 'Manual review',
    tone: 'warning',
  },
  MORE_INFO_REQUIRED: {
    body: 'Please update the requested information and submit again.',
    label: 'More info required',
    tone: 'warning',
  },
  REJECTED: {
    body: GENERIC_REJECTION_MESSAGE,
    label: 'Not verified',
    tone: 'danger',
  },
  SUBMITTED: {
    body: 'Your supplier application has been submitted.',
    label: 'Submitted',
    tone: 'primary',
  },
  SUSPENDED: {
    body: 'Supplier verification is currently suspended.',
    label: 'Suspended',
    tone: 'danger',
  },
  VERIFYING: {
    body: 'Verification checks are in progress.',
    label: 'Verifying',
    tone: 'primary',
  },
};

export function SupplierOnboardingScreen() {
  const { user } = useAppState();
  const [application, setApplication] =
    useState<SupplierOnboardingApplication | null>(null);
  const [businessForm, setBusinessForm] =
    useState<SupplierBusinessDetailsPayload>(initialBusinessForm);
  const [identityForm, setIdentityForm] =
    useState<SupplierIdentityVerificationPayload>(initialIdentityForm);
  const [bankForm, setBankForm] =
    useState<SupplierBankDetailsPayload>(initialBankForm);
  const [loading, setLoading] = useState(true);
  const [savingSection, setSavingSection] = useState<string | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    supplierOnboardingService
      .getStatus()
      .then((nextApplication) => {
        if (!active) {
          return;
        }

        setApplication(nextApplication);
        hydrateForms(nextApplication);
      })
      .catch((reason: unknown) => {
        if (active) {
          setError(reason instanceof Error ? reason.message : 'Unable to load verification.');
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const status = application?.status ?? 'DRAFT';
  const statusDetails = statusCopy[status];
  const canSubmit = useMemo(
    () =>
      Boolean(
        businessForm.cac_number.trim() &&
          businessForm.business_name.trim() &&
          businessForm.business_type.trim() &&
          businessForm.business_address.trim() &&
          businessForm.state.trim() &&
          (identityForm.bvn?.trim() ||
            identityForm.nin?.trim() ||
            application?.identity.bvn ||
            application?.identity.nin) &&
          bankForm.bank_name.trim() &&
          bankForm.bank_code.trim() &&
          (bankForm.account_number.trim().length >= 10 ||
            application?.bank.account_number)
      ),
    [application, bankForm, businessForm, identityForm]
  );

  function hydrateForms(nextApplication: SupplierOnboardingApplication) {
    setBusinessForm((current) => ({
      ...current,
      business_name: nextApplication.business.business_name || user?.company || '',
      business_type: nextApplication.business.business_type || current.business_type,
      cac_number: nextApplication.business.cac_number || current.cac_number,
      state: nextApplication.business.state || user?.location || '',
    }));
    setIdentityForm((current) => ({
      ...current,
      bvn: '',
      id_document_type:
        nextApplication.identity.id_document_type || current.id_document_type,
      nin: '',
    }));
    setBankForm((current) => ({
      ...current,
      account_name: nextApplication.bank.account_name || current.account_name,
      account_number: '',
      bank_code: nextApplication.bank.bank_code || current.bank_code,
      bank_name: nextApplication.bank.bank_name || current.bank_name,
    }));
  }

  async function runSection(
    section: string,
    action: () => Promise<SupplierOnboardingApplication>
  ) {
    setSavingSection(section);
    setError('');

    try {
      const nextApplication = await action();
      setApplication(nextApplication);
      hydrateForms(nextApplication);
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Request failed.');
    } finally {
      setSavingSection(null);
    }
  }

  async function pickImage(target: 'selfieUri' | 'idDocumentUri') {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      setError('Photo access is required to select this document.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: false,
      mediaTypes: ['images'],
      quality: 0.86,
      selectionLimit: 1,
    });

    if (!result.canceled && result.assets[0]?.uri) {
      setIdentityForm((current) => ({
        ...current,
        [target]: result.assets[0].uri,
      }));
    }
  }

  if (loading) {
    return (
      <Screen
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
        scroll={false}
      >
        <Loader label="Loading verification" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Supplier account"
        title="Verification"
        subtitle="Complete your business, identity, and bank checks."
      />

      <Card style={styles.statusCard}>
        <View style={styles.statusHeader}>
          <View>
            <Text style={styles.cardTitle}>Application status</Text>
            <Text style={styles.statusText}>
              {status === 'REJECTED'
                ? GENERIC_REJECTION_MESSAGE
                : application?.more_info_message || statusDetails.body}
            </Text>
          </View>
          <Badge label={statusDetails.label} tone={statusDetails.tone} />
        </View>
      </Card>

      <Card style={styles.formCard}>
        <View style={styles.sectionHeader}>
          <Text style={styles.cardTitle}>Business details</Text>
          <Badge label="Stage 2" tone="primary" />
        </View>
        <Input
          autoCapitalize="characters"
          label="CAC number"
          onChangeText={(value) =>
            setBusinessForm((current) => ({ ...current, cac_number: value }))
          }
          value={businessForm.cac_number}
        />
        <Input
          label="Business name"
          onChangeText={(value) =>
            setBusinessForm((current) => ({ ...current, business_name: value }))
          }
          value={businessForm.business_name}
        />
        <Input
          label="Business type"
          onChangeText={(value) =>
            setBusinessForm((current) => ({ ...current, business_type: value }))
          }
          value={businessForm.business_type}
        />
        <Input
          label="Business address"
          multiline
          onChangeText={(value) =>
            setBusinessForm((current) => ({
              ...current,
              business_address: value,
            }))
          }
          style={styles.multiline}
          textAlignVertical="top"
          value={businessForm.business_address}
        />
        <Input
          label="State/location"
          onChangeText={(value) =>
            setBusinessForm((current) => ({ ...current, state: value }))
          }
          value={businessForm.state}
        />
        <Button
          disabled={
            savingSection !== null ||
            !businessForm.cac_number.trim() ||
            !businessForm.business_name.trim() ||
            !businessForm.business_type.trim() ||
            !businessForm.business_address.trim() ||
            !businessForm.state.trim()
          }
          loading={savingSection === 'business'}
          onPress={() =>
            runSection('business', () =>
              supplierOnboardingService.submitBusinessDetails(businessForm)
            )
          }
          title="Save business details"
        />
      </Card>

      <Card style={styles.formCard}>
        <View style={styles.sectionHeader}>
          <Text style={styles.cardTitle}>Identity verification</Text>
          <Badge label="Stage 3" tone="primary" />
        </View>
        <Input
          keyboardType="number-pad"
          label="BVN"
          maxLength={11}
          onChangeText={(value) =>
            setIdentityForm((current) => ({ ...current, bvn: value }))
          }
          secureTextEntry
          value={identityForm.bvn}
        />
        <Input
          keyboardType="number-pad"
          label="NIN"
          maxLength={11}
          onChangeText={(value) =>
            setIdentityForm((current) => ({ ...current, nin: value }))
          }
          secureTextEntry
          value={identityForm.nin}
        />
        <View>
          <Text style={styles.inputLabel}>ID document type</Text>
          <View style={styles.optionRow}>
            {documentTypes.map((type) => (
              <Pressable
                accessibilityRole="button"
                key={type}
                onPress={() =>
                  setIdentityForm((current) => ({
                    ...current,
                    id_document_type: type,
                  }))
                }
                style={styles.optionButton}
              >
                <Badge
                  label={type.replace(/_/g, ' ')}
                  tone={
                    identityForm.id_document_type === type
                      ? 'primary'
                      : 'neutral'
                  }
                />
              </Pressable>
            ))}
          </View>
        </View>
        <View style={styles.uploadRow}>
          <UploadButton
            label={identityForm.selfieUri ? 'Selfie selected' : 'Select selfie'}
            onPress={() => pickImage('selfieUri')}
          />
          <UploadButton
            label={
              identityForm.idDocumentUri ? 'ID selected' : 'Select ID document'
            }
            onPress={() => pickImage('idDocumentUri')}
          />
        </View>
        <Button
          disabled={
            savingSection !== null ||
            !(identityForm.bvn?.trim() || identityForm.nin?.trim())
          }
          loading={savingSection === 'identity'}
          onPress={() =>
            runSection('identity', () =>
              supplierOnboardingService.submitIdentityVerification(identityForm)
            )
          }
          title="Save identity details"
        />
      </Card>

      <Card style={styles.formCard}>
        <View style={styles.sectionHeader}>
          <Text style={styles.cardTitle}>Bank verification</Text>
          <Badge label="Stage 4" tone="primary" />
        </View>
        <Input
          label="Bank name"
          onChangeText={(value) =>
            setBankForm((current) => ({ ...current, bank_name: value }))
          }
          value={bankForm.bank_name}
        />
        <Input
          autoCapitalize="characters"
          label="Bank code"
          onChangeText={(value) =>
            setBankForm((current) => ({ ...current, bank_code: value }))
          }
          value={bankForm.bank_code}
        />
        <Input
          keyboardType="number-pad"
          label="Account number"
          maxLength={12}
          onChangeText={(value) =>
            setBankForm((current) => ({ ...current, account_number: value }))
          }
          secureTextEntry
          value={bankForm.account_number}
        />
        <Input
          label="Account name"
          onChangeText={(value) =>
            setBankForm((current) => ({ ...current, account_name: value }))
          }
          value={bankForm.account_name}
        />
        <Button
          disabled={
            savingSection !== null ||
            !bankForm.bank_name.trim() ||
            !bankForm.bank_code.trim() ||
            bankForm.account_number.trim().length < 10
          }
          loading={savingSection === 'bank'}
          onPress={() =>
            runSection('bank', () =>
              supplierOnboardingService.submitBankDetails(bankForm)
            )
          }
          title="Save bank details"
        />
      </Card>

      {error ? <Text style={styles.error}>{error}</Text> : null}

      <Button
        disabled={savingSection !== null || !canSubmit}
        loading={savingSection === 'submit'}
        onPress={() =>
          runSection('submit', () => supplierOnboardingService.submitForReview())
        }
        title="Submit application"
      />
    </Screen>
  );
}

function UploadButton({
  label,
  onPress,
}: {
  label: string;
  onPress: () => void;
}) {
  return (
    <Pressable accessibilityRole="button" onPress={onPress} style={styles.uploadButton}>
      <Ionicons color={theme.colors.primary} name="cloud-upload-outline" size={18} />
      <Text style={styles.uploadText}>{label}</Text>
    </Pressable>
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
  statusCard: {
    marginBottom: theme.spacing.md,
  },
  statusHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  formCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  sectionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cardTitle: {
    color: theme.colors.text,
    flex: 1,
    fontSize: 16,
    fontWeight: '900',
  },
  statusText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 4,
  },
  multiline: {
    minHeight: 86,
    paddingTop: theme.spacing.md,
  },
  inputLabel: {
    color: theme.colors.text,
    fontSize: 13,
    fontWeight: '800',
    marginBottom: theme.spacing.xs,
  },
  optionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  optionButton: {
    minHeight: 34,
  },
  uploadRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  uploadButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    justifyContent: 'center',
    minHeight: 44,
    paddingHorizontal: theme.spacing.sm,
  },
  uploadText: {
    color: theme.colors.primaryDark,
    flexShrink: 1,
    fontSize: 12,
    fontWeight: '900',
    textAlign: 'center',
  },
  error: {
    color: theme.colors.danger,
    fontSize: 13,
    fontWeight: '800',
    lineHeight: 19,
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
});
