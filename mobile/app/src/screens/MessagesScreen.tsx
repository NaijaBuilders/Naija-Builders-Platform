import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useMemo } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ConversationRow, Header, Loader } from '../components';
import { useConversations } from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import type { MessagesStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Conversation } from '../types';

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

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          data={conversations}
          keyExtractor={(item) => item.id}
          ListEmptyComponent={
            loading ? (
              <Loader label="Loading messages" />
            ) : (
              <Text style={styles.empty}>
                {error?.message || 'No conversations.'}
              </Text>
            )
          }
          ListHeaderComponent={
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
          }
          renderItem={({ item, index }: ListRenderItemInfo<Conversation>) => (
            <ConversationRow
              animationIndex={index}
              conversation={item}
              onPress={(conversation) =>
                navigation.navigate('Chat', { conversationId: conversation.id })
              }
            />
          )}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.content}
        />
      </Animated.View>
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
