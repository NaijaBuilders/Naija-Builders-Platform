import { Ionicons } from '@expo/vector-icons';
import React, { useEffect, useMemo, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Button, Card, Header, Input, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import {
  fallbackBudgetRanges,
  fallbackServiceTypes,
  serviceRequestService,
} from '../services';
import { theme } from '../theme';
import type { ServiceOption } from '../types';

export function HireServiceScreen() {
  const { user } = useAppState();
  const [serviceTypes, setServiceTypes] =
    useState<ServiceOption[]>(fallbackServiceTypes);
  const [budgetRanges, setBudgetRanges] =
    useState<ServiceOption[]>(fallbackBudgetRanges);
  const [serviceType, setServiceType] = useState(fallbackServiceTypes[0].value);
  const [budgetRange, setBudgetRange] = useState('not_sure');
  const [projectTitle, setProjectTitle] = useState('');
  const [projectLocation, setProjectLocation] = useState(user?.location ?? '');
  const [projectDescription, setProjectDescription] = useState('');
  const [preferredStartDate, setPreferredStartDate] = useState('');
  const [contactName, setContactName] = useState(user?.name ?? '');
  const [contactPhone, setContactPhone] = useState(user?.phone ?? '');
  const [contactEmail, setContactEmail] = useState(user?.email ?? '');
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    let mounted = true;

    serviceRequestService.options().then((options) => {
      if (!mounted) {
        return;
      }

      setServiceTypes(options.serviceTypes);
      setBudgetRanges(options.budgetRanges);
      setServiceType((current) => current || options.serviceTypes[0]?.value || '');
      setBudgetRange((current) => current || options.budgetRanges[0]?.value || '');
    });

    return () => {
      mounted = false;
    };
  }, []);

  const selectedServiceLabel = useMemo(
    () =>
      serviceTypes.find((option) => option.value === serviceType)?.label ??
      'Service',
    [serviceType, serviceTypes]
  );

  const handleSubmit = async () => {
    setError('');
    setSuccessMessage('');

    if (
      !projectTitle.trim() ||
      !projectLocation.trim() ||
      projectDescription.trim().length < 15 ||
      !contactName.trim() ||
      !contactPhone.trim() ||
      !contactEmail.trim()
    ) {
      setError('Please complete the project and contact details.');
      return;
    }

    try {
      setLoading(true);
      await serviceRequestService.create({
        budgetRange,
        contactEmail: contactEmail.trim(),
        contactName: contactName.trim(),
        contactPhone: contactPhone.trim(),
        preferredStartDate: preferredStartDate.trim() || undefined,
        projectDescription: projectDescription.trim(),
        projectLocation: projectLocation.trim(),
        projectTitle: projectTitle.trim(),
        serviceType,
      });

      setSuccessMessage(
        `Your ${selectedServiceLabel.toLowerCase()} request has been sent.`
      );
      setProjectTitle('');
      setProjectDescription('');
      setPreferredStartDate('');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Request failed.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Screen>
      <Header
        eyebrow="Construction services"
        title="Hire a Service"
        subtitle="Tell us what your project needs and we will help route the request."
      />

      <Card style={styles.infoCard}>
        <View style={styles.infoRow}>
          <Ionicons
            color={theme.colors.primary}
            name="briefcase-outline"
            size={22}
          />
          <View style={styles.infoTextWrap}>
            <Text style={styles.infoTitle}>How it works</Text>
            <Text style={styles.infoText}>
              Choose a professional, share your site details, and we will follow
              up with the next step.
            </Text>
          </View>
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Service needed</Text>
        <View style={styles.optionWrap}>
          {serviceTypes.map((option) => (
            <Pressable
              accessibilityRole="button"
              key={option.value}
              onPress={() => setServiceType(option.value)}
              style={[
                styles.option,
                serviceType === option.value ? styles.optionActive : null,
              ]}
            >
              <Text
                style={[
                  styles.optionText,
                  serviceType === option.value ? styles.optionTextActive : null,
                ]}
              >
                {option.label}
              </Text>
            </Pressable>
          ))}
        </View>

        <Input
          label="Project title"
          onChangeText={setProjectTitle}
          placeholder="Duplex design and site supervision"
          value={projectTitle}
        />
        <Input
          label="Project location"
          onChangeText={setProjectLocation}
          placeholder="Lekki, Lagos"
          value={projectLocation}
        />
        <Input
          label="What do you need done?"
          multiline
          onChangeText={setProjectDescription}
          placeholder="Describe the project, site stage, and support you need."
          style={styles.multiline}
          textAlignVertical="top"
          value={projectDescription}
        />

        <Text style={styles.sectionTitle}>Budget range</Text>
        <View style={styles.optionWrap}>
          {budgetRanges.map((option) => (
            <Pressable
              accessibilityRole="button"
              key={option.value}
              onPress={() => setBudgetRange(option.value)}
              style={[
                styles.option,
                budgetRange === option.value ? styles.optionActive : null,
              ]}
            >
              <Text
                style={[
                  styles.optionText,
                  budgetRange === option.value ? styles.optionTextActive : null,
                ]}
              >
                {option.label}
              </Text>
            </Pressable>
          ))}
        </View>

        <Input
          label="Preferred start date"
          onChangeText={setPreferredStartDate}
          placeholder="YYYY-MM-DD"
          value={preferredStartDate}
        />
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Contact details</Text>
        <Input label="Name" onChangeText={setContactName} value={contactName} />
        <Input
          keyboardType="phone-pad"
          label="Phone"
          onChangeText={setContactPhone}
          value={contactPhone}
        />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setContactEmail}
          value={contactEmail}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {successMessage ? (
          <Text style={styles.success}>{successMessage}</Text>
        ) : null}
        <Button
          disabled={loading}
          loading={loading}
          onPress={handleSubmit}
          size="lg"
          title="Send request"
        />
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  infoCard: {
    marginTop: theme.spacing.sm,
  },
  infoRow: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  infoTextWrap: {
    flex: 1,
  },
  infoTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  infoText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    marginTop: 4,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  optionWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  option: {
    borderColor: theme.colors.border,
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: 9,
  },
  optionActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  optionText: {
    color: theme.colors.textMuted,
    fontSize: 12,
    fontWeight: '900',
  },
  optionTextActive: {
    color: theme.colors.primaryDark,
  },
  multiline: {
    minHeight: 110,
    paddingTop: theme.spacing.md,
  },
  error: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
  success: {
    color: theme.colors.success,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
});
