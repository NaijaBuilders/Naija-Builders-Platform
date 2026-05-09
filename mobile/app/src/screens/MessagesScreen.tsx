import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback, useMemo } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  Platform,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  ConversationRow,
  FloatingBackButton,
  Header,
  Loader,
} from '../components';
import { useConversations } from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import type { MainTabParamList, MessagesStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Conversation } from '../types';

const listPerformanceProps = {
  initialNumToRender: 8,
  maxToRenderPerBatch: 8,
  removeClippedSubviews: Platform.OS === 'android',
  updateCellsBatchingPeriod: 50,
  windowSize: 7,
};

export function MessagesScreen() {
  const navigation =
    useNavigation<NativeStackNavigationProp<MessagesStackParamList, 'MessagesMain'>>();
  const { data: conversations, error, loading } = useConversations();
  const animatedStyle = useScreenAnimation();
  const unreadTotal = useMemo(
    () =>
      conversations.reduce(
        (total, conversation) => total + conversation.unreadCount,
        0
      ),
    [conversations]
  );
  const keyExtractor = useCallback((item: Conversation) => item.id, []);
  const openConversation = useCallback(
    (conversation: Conversation) => {
      navigation.navigate('Chat', { conversationId: conversation.id });
    },
    [navigation]
  );
  const goHome = useCallback(() => {
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Home', { screen: 'HomeMain' });
  }, [navigation]);
  const renderConversation = useCallback(
    ({ item, index }: ListRenderItemInfo<Conversation>) => (
      <ConversationRow
        animationIndex={index}
        conversation={item}
        onPress={openConversation}
      />
    ),
    [openConversation]
  );
  const header = useMemo(
    () => (
      <>
        <Header
          eyebrow="Inbox"
          title="Messages"
          subtitle="Conversations from your account."
        />
        <View style={styles.inboxBar}>
          <View>
            <Text style={styles.inboxValue}>{conversations.length}</Text>
            <Text style={styles.inboxLabel}>Conversations</Text>
          </View>
          <View style={styles.inboxDivider} />
          <View>
            <Text style={styles.inboxValue}>{unreadTotal}</Text>
            <Text style={styles.inboxLabel}>Unread</Text>
          </View>
        </View>
        <Text style={styles.sectionTitle}>Recent conversations</Text>
      </>
    ),
    [conversations.length, unreadTotal]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={conversations}
          keyExtractor={keyExtractor}
          ListEmptyComponent={
            loading ? (
              <Loader label="Loading messages" />
            ) : (
              <Text style={styles.empty}>
                {error?.message || 'No conversations.'}
              </Text>
            )
          }
          ListHeaderComponent={header}
          renderItem={renderConversation}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.contentWithFloatingBack}
        />
      </Animated.View>
      <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  container: {
    flex: 1,
  },
  content: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
  },
  contentWithFloatingBack: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
    paddingTop: 86,
  },
  inboxBar: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.lg,
    marginBottom: theme.spacing.lg,
    padding: theme.spacing.md,
  },
  inboxDivider: {
    backgroundColor: theme.colors.border,
    height: 36,
    width: 1,
  },
  inboxValue: {
    color: theme.colors.primaryDark,
    fontSize: 18,
    fontWeight: '900',
  },
  inboxLabel: {
    color: theme.colors.textMuted,
    fontSize: 11,
    fontWeight: '800',
    marginTop: 3,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    textAlign: 'center',
  },
});
