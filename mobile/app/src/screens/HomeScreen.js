import React from 'react';
import { ImageBackground, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, QuickAction, ScreenHeader } from '../components/ui';
import { colors, fonts, radius, spacing } from '../styles/theme';
import { imageUrl } from '../utils/format';

// Mirrors GET /index.php (HomeController@index).
export default function HomeScreen({ navigation }) {
  return (
    <ScreenWrapper>
      <ImageBackground
        source={{ uri: imageUrl('assets/images/home.jpg') }}
        resizeMode="cover"
        style={styles.hero}
        imageStyle={styles.heroImage}
      >
        <View style={styles.heroOverlay}>
          <Text style={styles.eyebrow}>NaijaBuilders</Text>
          <Text style={styles.heroTitle}>Build Better. Faster. Together.</Text>
          <Text style={styles.heroCopy}>
            Trusted construction materials, supplier conversations, saved products, and cart workflows in one mobile app.
          </Text>
          <View style={styles.buttonRow}>
            <AppButton label="Browse Materials" onPress={() => navigation.navigate('Materials')} />
            <AppButton label="Create Account" variant="soft" onPress={() => navigation.navigate('Register')} />
          </View>
        </View>
      </ImageBackground>

      <ScreenHeader title="What You Can Do" subtitle="The app follows the same marketplace flows as the website, tuned for quick mobile decisions." />
      <View style={styles.quickGrid}>
        <QuickAction label="Find Materials" detail="Search active cement, steel, gravel, wood, and finishing listings." onPress={() => navigation.navigate('Materials')} />
        <QuickAction label="Save Products" detail="Keep shortlisted materials organised for later." onPress={() => navigation.navigate('Register')} />
        <QuickAction label="Message Suppliers" detail="Ask availability, delivery, quantity, and price questions." onPress={() => navigation.navigate('Register')} />
        <QuickAction label="Supplier Tools" detail="Register as a supplier to manage listings from mobile." onPress={() => navigation.navigate('Register')} />
      </View>

      <Card style={styles.trustCard}>
        <Text style={styles.sectionTitle}>Verified Supplier Focus</Text>
        <Text style={styles.sectionBody}>NaijaBuilders is being shaped around supplier onboarding, transparent listings, buyer protection, and clear project communication.</Text>
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  hero: {
    minHeight: 370,
    marginBottom: 18,
    overflow: 'hidden',
    borderRadius: radius.lg,
  },
  heroImage: {
    borderRadius: radius.lg,
  },
  heroOverlay: {
    flex: 1,
    justifyContent: 'flex-end',
    padding: spacing.lg,
    backgroundColor: 'rgba(14, 28, 51, 0.55)',
  },
  eyebrow: {
    textTransform: 'uppercase',
    fontSize: 12,
    color: '#D6EEE9',
    fontWeight: '800',
  },
  heroTitle: {
    color: '#FFFFFF',
    fontFamily: fonts.heading,
    fontSize: 34,
    lineHeight: 40,
    marginTop: 8,
  },
  heroCopy: {
    color: '#F8F4EF',
    lineHeight: 21,
    marginTop: 10,
  },
  buttonRow: {
    marginTop: 18,
    gap: 10,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  trustCard: {},
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
