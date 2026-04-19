import React from 'react';
import { SafeAreaView, ScrollView, StyleSheet, View } from 'react-native';
import FadeInView from './FadeInView';

export default function ScreenWrapper({ children }) {
  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.background}>
        <View style={styles.blobPrimary} />
        <View style={styles.blobSecondary} />
      </View>
      <ScrollView contentContainerStyle={styles.container}>
        <FadeInView>{children}</FadeInView>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F8F4EF',
  },
  container: {
    padding: 20,
    paddingBottom: 36,
  },
  background: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#F8F4EF',
  },
  blobPrimary: {
    position: 'absolute',
    top: -120,
    right: -60,
    width: 220,
    height: 220,
    borderRadius: 120,
    backgroundColor: '#E4F0EB',
  },
  blobSecondary: {
    position: 'absolute',
    bottom: -120,
    left: -80,
    width: 260,
    height: 260,
    borderRadius: 140,
    backgroundColor: '#F1E3D3',
  },
});
