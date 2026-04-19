import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Mirrors GET /forgot-password.php and GET /api/mobile/forgot-password.
export default function ForgotPasswordScreen() {
  const [message, setMessage] = useState('');

  useEffect(() => {
    const loadMessage = async () => {
      try {
        const response = await api.getForgotPassword();
        setMessage(response.data.message || 'Password reset is not available yet.');
      } catch (err) {
        setMessage('Password reset is not available yet.');
      }
    };

    loadMessage();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Reset Password</Text>
      <View style={styles.card}>
        <Text style={styles.message}>{message}</Text>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
    marginBottom: 12,
  },
  card: {
    padding: 16,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  message: {
    color: colors.muted,
  },
});
