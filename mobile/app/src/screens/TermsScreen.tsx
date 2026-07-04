import React, { useEffect, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import {
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import { apiClient } from '../services';
import { theme } from '../theme';

export function TermsScreen() {
  const [content, setContent] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let mounted = true;

    apiClient
      .get<{ content?: string }>('/terms')
      .then((response) => {
        if (mounted) {
          setContent(String(response.data.content ?? ''));
        }
      })
      .catch(() => {
        if (mounted) {
          setError('Terms could not be loaded right now. Please try again later.');
        }
      })
      .finally(() => {
        if (mounted) {
          setLoading(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading terms" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Legal"
        title="Terms & privacy"
        subtitle="How NaijaBuilders works and how your data is protected."
      />
      <Card>
        <Text style={styles.body}>{error || content}</Text>
      </Card>
    </Screen>
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
  body: {
    color: theme.colors.textMuted,
    fontSize: 13.5,
    lineHeight: 21,
  },
});
