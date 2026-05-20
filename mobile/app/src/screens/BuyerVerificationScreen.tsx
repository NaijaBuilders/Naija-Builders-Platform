import * as ImagePicker from 'expo-image-picker';
import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import { kycService } from '../services';
import { theme } from '../theme';
import type { BuyerIdSubmissionPayload, OtpChannel } from '../types';

const documentOptions: Array<{
  label: string;
  value: BuyerIdSubmissionPayload['documentType'];
}> = [
  { label: 'NIN slip', value: 'nin_slip' },
  { label: 'Passport', value: 'international_passport' },
  { label: 'Driver licence', value: 'drivers_licence' },
];

export function BuyerVerificationScreen() {
  const [emailOtp, setEmailOtp] = useState('');
  const [phoneOtp, setPhoneOtp] = useState('');
  const [documentType, setDocumentType] =
    useState<BuyerIdSubmissionPayload['documentType']>('nin_slip');
  const [idName, setIdName] = useState('');
  const [idDocumentUri, setIdDocumentUri] = useState('');
  const [selfieUri, setSelfieUri] = useState('');
  const [loading, setLoading] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const sendCode = async (channel: OtpChannel) => {
    setLoading(`send-${channel}`);
    setMessage('');
    setError('');
    try {
      const response = await kycService.sendOtp(channel);
      setMessage(
        response.debug_code
          ? `Code sent. Local test code: ${response.debug_code}`
          : 'Code sent.'
      );
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Could not send code.');
    } finally {
      setLoading('');
    }
  };

  const confirmCode = async (channel: OtpChannel, otp: string) => {
    setLoading(`confirm-${channel}`);
    setMessage('');
    setError('');
    try {
      await kycService.confirmOtp(channel, otp.trim());
      setMessage(channel === 'email' ? 'Email confirmed.' : 'Phone confirmed.');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Could not confirm code.');
    } finally {
      setLoading('');
    }
  };

  const pickImage = async (kind: 'id' | 'selfie') => {
    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: true,
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.85,
    });

    if (!result.canceled && result.assets[0]?.uri) {
      if (kind === 'id') {
        setIdDocumentUri(result.assets[0].uri);
      } else {
        setSelfieUri(result.assets[0].uri);
      }
    }
  };

  const submitId = async () => {
    setLoading('id');
    setMessage('');
    setError('');
    try {
      const result = await kycService.submitBuyerId({
        documentType,
        idDocumentUri,
        selfieUri,
        verifiedIdName: idName.trim() || undefined,
      });
      setMessage(result.message || 'Your ID has been submitted.');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Could not submit ID.');
    } finally {
      setLoading('');
    }
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Account"
        title="Verification"
        subtitle="Complete this once now, or wait until an order needs it."
      />

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>How it works</Text>
        <Text style={styles.helper}>
          You can browse and save materials without ID. Larger orders may ask
          for a photo of your ID and a selfie. Once approved, we can reuse this
          verification for future eligible orders.
        </Text>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Confirm email</Text>
        <Input
          keyboardType="number-pad"
          label="Email code"
          onChangeText={setEmailOtp}
          value={emailOtp}
        />
        <View style={styles.actionRow}>
          <Button
            title="Send code"
            onPress={() => sendCode('email')}
            loading={loading === 'send-email'}
            variant="outline"
            style={styles.action}
          />
          <Button
            title="Confirm"
            onPress={() => confirmCode('email', emailOtp)}
            loading={loading === 'confirm-email'}
            disabled={emailOtp.trim().length !== 6}
            style={styles.action}
          />
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Confirm phone</Text>
        <Input
          keyboardType="number-pad"
          label="Phone code"
          onChangeText={setPhoneOtp}
          value={phoneOtp}
        />
        <View style={styles.actionRow}>
          <Button
            title="Send code"
            onPress={() => sendCode('phone')}
            loading={loading === 'send-phone'}
            variant="outline"
            style={styles.action}
          />
          <Button
            title="Confirm"
            onPress={() => confirmCode('phone', phoneOtp)}
            loading={loading === 'confirm-phone'}
            disabled={phoneOtp.trim().length !== 6}
            style={styles.action}
          />
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Upload ID</Text>
        <Text style={styles.helper}>Upload a photo of your ID, then take a selfie.</Text>
        <View style={styles.optionRow}>
          {documentOptions.map((option) => (
            <Pressable
              key={option.value}
              onPress={() => setDocumentType(option.value)}
              style={[
                styles.option,
                documentType === option.value ? styles.optionActive : null,
              ]}
            >
              <Badge
                label={option.label}
                tone={documentType === option.value ? 'primary' : 'neutral'}
              />
            </Pressable>
          ))}
        </View>
        <Input
          label="Name on ID"
          onChangeText={setIdName}
          value={idName}
        />
        <Button
          title={idDocumentUri ? 'ID photo selected' : 'Choose ID photo'}
          onPress={() => pickImage('id')}
          variant="outline"
        />
        <Button
          title={selfieUri ? 'Selfie selected' : 'Choose selfie'}
          onPress={() => pickImage('selfie')}
          variant="outline"
        />
        <Button
          title="Submit ID"
          onPress={submitId}
          loading={loading === 'id'}
          disabled={!idDocumentUri || !selfieUri}
        />
      </Card>

      {message ? <Text style={styles.message}>{message}</Text> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 86,
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  helper: {
    color: theme.colors.textMuted,
    lineHeight: 21,
  },
  actionRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  action: {
    flex: 1,
  },
  optionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  option: {
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    padding: theme.spacing.xs,
  },
  optionActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  message: {
    color: theme.colors.success,
    fontWeight: '800',
    lineHeight: 21,
    textAlign: 'center',
  },
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    lineHeight: 21,
    textAlign: 'center',
  },
});
