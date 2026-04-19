import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/terms (Mobile/StaticPageController@terms).
export default function TermsScreen() {
  const [content, setContent] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    const loadTerms = async () => {
      try {
        const response = await api.getTerms();
        setContent(response.data.content || '');
      } catch (err) {
        setError('Unable to load terms.');
      }
    };

    loadTerms();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Terms & Conditions</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <View style={styles.card}>
        <Text style={styles.content}>{content || 'Terms content is unavailable.'}</Text>
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
  content: {
    color: colors.muted,
    lineHeight: 20,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
