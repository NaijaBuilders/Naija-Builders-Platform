import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { colors, fonts } from '../styles/theme';

// Mirrors GET /index.php (HomeController@index).
export default function HomeScreen({ navigation }) {
  return (
    <ScreenWrapper>
      <View style={styles.heroCard}>
        <Text style={styles.eyebrow}>The Nigerian Construction Materials Marketplace</Text>
        <Text style={styles.title}>Build Better. Faster. Together.</Text>
        <Text style={styles.subtitle}>
          Connect with trusted local suppliers of cement, steel, wood, finishes and other construction materials across
          Nigeria.
        </Text>
        <View style={styles.buttonRow}>
          <Pressable style={styles.primaryButton} onPress={() => navigation.navigate('Materials')}>
            <Text style={styles.primaryButtonText}>Browse Materials</Text>
          </Pressable>
          <Pressable style={styles.secondaryButton} onPress={() => navigation.navigate('Register')}>
            <Text style={styles.secondaryButtonText}>Become a Supplier</Text>
          </Pressable>
        </View>
      </View>
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Why Choose NaijaBuilders?</Text>
        <Text style={styles.sectionBody}>
          From supplier verification to delivery and post-order support, NaijaBuilders helps projects move faster with less
          risk and better cost control.
        </Text>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  heroCard: {
    padding: 20,
    borderRadius: 20,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    marginBottom: 18,
  },
  eyebrow: {
    textTransform: 'uppercase',
    letterSpacing: 1,
    fontSize: 12,
    color: colors.muted,
  },
  title: {
    fontSize: 30,
    fontFamily: fonts.heading,
    color: colors.ink,
    marginTop: 10,
  },
  subtitle: {
    marginTop: 10,
    color: colors.muted,
    lineHeight: 20,
  },
  buttonRow: {
    marginTop: 18,
    gap: 10,
  },
  primaryButton: {
    backgroundColor: colors.accent,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  secondaryButton: {
    backgroundColor: colors.highlight,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  secondaryButtonText: {
    color: colors.ink,
    fontWeight: '600',
  },
  section: {
    padding: 16,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  sectionTitle: {
    fontFamily: fonts.heading,
    fontSize: 18,
    marginBottom: 8,
  },
  sectionBody: {
    color: colors.muted,
    lineHeight: 20,
  },
});
