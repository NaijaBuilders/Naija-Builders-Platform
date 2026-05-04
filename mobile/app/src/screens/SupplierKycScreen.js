import React, { useContext, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Badge, Card, ErrorBanner, LoadingState, ScreenHeader, TextField } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';
import { humanize } from '../utils/format';

export default function SupplierKycScreen() {
  const { user } = useContext(AuthContext);
  const [profile, setProfile] = useState(null);
  const [form, setForm] = useState({
    company: '',
    business_category: '',
    location: '',
    business_address: '',
    business_description: '',
    bank_name: '',
    account_number: '',
  });
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadProfile = async () => {
    setIsLoading(true);
    setError('');
    try {
      const response = await api.getProfile();
      const nextUser = response.data.user || {};
      setProfile(response.data);
      setForm({
        company: nextUser.company || '',
        business_category: nextUser.business_category || '',
        location: nextUser.location || '',
        business_address: nextUser.business_address || '',
        business_description: nextUser.business_description || '',
        bank_name: nextUser.bank_name || '',
        account_number: nextUser.account_number || '',
      });
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load supplier KYC details.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadProfile();
  }, []);

  const updateField = (key, value) => {
    setForm((current) => ({ ...current, [key]: value }));
  };

  const handleSave = async () => {
    setError('');
    setSuccess('');
    setIsSaving(true);

    try {
      const formData = new FormData();
      const nameParts = String(profile?.full_name || user?.name || 'Supplier').split(' ');
      formData.append('first_name', nameParts[0] || 'Supplier');
      formData.append('last_name', nameParts.slice(1).join(' '));
      Object.entries(profile?.user || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          formData.append(key, String(value));
        }
      });
      Object.entries(form).forEach(([key, value]) => {
        formData.append(key, value);
      });

      await api.updateProfile(formData);
      setSuccess('Supplier verification details saved.');
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to save supplier KYC details.');
    } finally {
      setIsSaving(false);
    }
  };

  const status = String(user?.kyc_status || 'pending');
  const tone = status === 'approved' ? 'success' : status === 'submitted' ? 'warning' : 'danger';

  return (
    <ScreenWrapper>
      <ScreenHeader title="Supplier KYC" subtitle="Mobile business verification details for your supplier profile." />
      <Card style={styles.statusCard}>
        <View style={styles.statusRow}>
          <View style={styles.statusText}>
            <Text style={styles.statusTitle}>Current Status</Text>
            <Text style={styles.statusCopy}>Listings stay limited until approval is complete.</Text>
          </View>
          <Badge label={humanize(status)} tone={tone} />
        </View>
      </Card>

      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading KYC details..." /> : null}
      {!isLoading ? (
        <Card>
          <TextField label="Business Name" value={form.company} onChangeText={(value) => updateField('company', value)} />
          <TextField label="Business Category" value={form.business_category} onChangeText={(value) => updateField('business_category', value)} />
          <TextField label="Location" value={form.location} onChangeText={(value) => updateField('location', value)} />
          <TextField label="Business Address" value={form.business_address} onChangeText={(value) => updateField('business_address', value)} />
          <TextField label="Business Description" multiline value={form.business_description} onChangeText={(value) => updateField('business_description', value)} />
          <TextField label="Bank Name" value={form.bank_name} onChangeText={(value) => updateField('bank_name', value)} />
          <TextField label="Account Number" keyboardType="number-pad" value={form.account_number} onChangeText={(value) => updateField('account_number', value)} />
          {success ? <Text style={styles.success}>{success}</Text> : null}
          <AppButton label={isSaving ? 'Saving...' : 'Save KYC Details'} onPress={handleSave} disabled={isSaving} />
        </Card>
      ) : null}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  statusCard: {
    marginBottom: spacing.md,
  },
  statusRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: spacing.sm,
    justifyContent: 'space-between',
  },
  statusText: {
    flex: 1,
  },
  statusTitle: {
    color: colors.ink,
    fontWeight: '800',
  },
  statusCopy: {
    color: colors.muted,
    marginTop: 4,
  },
  success: {
    color: colors.success,
    fontWeight: '700',
    marginVertical: spacing.sm,
  },
});

