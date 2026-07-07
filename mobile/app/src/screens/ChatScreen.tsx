import { Ionicons } from '@expo/vector-icons';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { Avatar, EmptyState, Loader } from '../components';
import { useConversation } from '../hooks/useMarketplaceData';
import { messageService } from '../services';
import { theme } from '../theme';
import type { ConversationMessage } from '../types';
import { haptics } from '../utils/haptics';

type ChatScreenProps = {
  route: {
    params: {
      conversationId: string;
    };
  };
  navigation: {
    goBack: () => void;
  };
};

function formatTime(iso: string): string {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function dayLabel(iso: string): string {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  const sameDay = (a: Date, b: Date) =>
    a.getDate() === b.getDate() &&
    a.getMonth() === b.getMonth() &&
    a.getFullYear() === b.getFullYear();

  if (sameDay(date, today)) {
    return 'Today';
  }
  if (sameDay(date, yesterday)) {
    return 'Yesterday';
  }
  return date.toLocaleDateString([], {
    day: 'numeric',
    month: 'short',
    year: date.getFullYear() === today.getFullYear() ? undefined : 'numeric',
  });
}

export function ChatScreen({ route, navigation }: ChatScreenProps) {
  const {
    data: conversation,
    error,
    loading,
    refresh,
  } = useConversation(route.params.conversationId);
  const [draft, setDraft] = useState('');
  const [sendError, setSendError] = useState('');
  const [sending, setSending] = useState(false);
  const scrollRef = useRef<ScrollView>(null);

  const messageCount = conversation?.messages.length ?? 0;

  // Auto-scroll to the newest message when the thread loads or grows.
  useEffect(() => {
    if (messageCount > 0) {
      requestAnimationFrame(() => scrollRef.current?.scrollToEnd({ animated: true }));
    }
  }, [messageCount]);

  // Light polling so incoming replies appear without leaving the screen.
  useEffect(() => {
    const interval = setInterval(() => refresh(), 12000);
    return () => clearInterval(interval);
  }, [refresh]);

  const handleSend = useCallback(async () => {
    const body = draft.trim();
    if (!body) {
      return;
    }

    setSending(true);
    setSendError('');
    haptics.tap();

    try {
      await messageService.sendMessage(route.params.conversationId, body);
      setDraft('');
      refresh();
    } catch (reason) {
      setSendError(reason instanceof Error ? reason.message : 'Message was not sent');
    } finally {
      setSending(false);
    }
  }, [draft, refresh, route.params.conversationId]);

  if (loading) {
    return (
      <View style={styles.centerScreen}>
        <Loader label="Loading chat" />
      </View>
    );
  }

  if (!conversation) {
    return (
      <View style={styles.centerScreen}>
        <View style={styles.notFoundWrap}>
          <EmptyState
            icon="chatbubble-ellipses-outline"
            title="Conversation not found"
            message={error?.message || 'This chat could not be loaded. Go back and try again.'}
          />
        </View>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      style={styles.flex}
    >
      <View style={styles.header}>
        <Pressable
          accessibilityLabel="Go back"
          accessibilityRole="button"
          hitSlop={10}
          onPress={navigation.goBack}
          style={styles.backButton}
        >
          <Ionicons color={theme.colors.text} name="chevron-back" size={24} />
        </Pressable>
        <Avatar
          imageUri={conversation.profileImage}
          name={conversation.participantName}
          size={42}
        />
        <View style={styles.headerCopy}>
          <Text numberOfLines={1} style={styles.headerName}>
            {conversation.participantName}
          </Text>
          <Text numberOfLines={1} style={styles.headerCompany}>
            {conversation.company}
          </Text>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={styles.thread}
        ref={scrollRef}
        showsVerticalScrollIndicator={false}
      >
        {conversation.messages.length === 0 ? (
          <View style={styles.emptyThread}>
            <View style={styles.emptyIcon}>
              <Ionicons
                color={theme.colors.primary}
                name="chatbubbles-outline"
                size={30}
              />
            </View>
            <Text style={styles.emptyTitle}>Start the conversation</Text>
            <Text style={styles.emptyText}>
              Send a message to {conversation.participantName.split(' ')[0]} about
              your order, quote or delivery.
            </Text>
          </View>
        ) : (
          conversation.messages.map((message, index) => (
            <MessageBubble
              key={message.id}
              message={message}
              previous={conversation.messages[index - 1]}
            />
          ))
        )}
      </ScrollView>

      {sendError ? <Text style={styles.error}>{sendError}</Text> : null}

      <View style={styles.composer}>
        <TextInput
          multiline
          onChangeText={setDraft}
          placeholder="Write a message…"
          placeholderTextColor={theme.colors.textSubtle}
          style={styles.composerInput}
          value={draft}
        />
        <Pressable
          accessibilityLabel="Send message"
          accessibilityRole="button"
          disabled={!draft.trim() || sending}
          onPress={handleSend}
          style={[
            styles.sendButton,
            !draft.trim() || sending ? styles.sendButtonDisabled : null,
          ]}
        >
          <Ionicons color={theme.colors.white} name="send" size={18} />
        </Pressable>
      </View>
    </KeyboardAvoidingView>
  );
}

function MessageBubble({
  message,
  previous,
}: {
  message: ConversationMessage;
  previous?: ConversationMessage;
}) {
  const mine = message.sender === 'me';
  const showDay =
    !previous ||
    dayLabel(previous.created_at) !== dayLabel(message.created_at);

  return (
    <>
      {showDay ? (
        <View style={styles.dayDivider}>
          <Text style={styles.dayText}>{dayLabel(message.created_at)}</Text>
        </View>
      ) : null}
      <View style={[styles.bubbleRow, mine ? styles.bubbleRowMine : null]}>
        <View
          style={[styles.bubble, mine ? styles.bubbleMine : styles.bubbleTheirs]}
        >
          <Text style={[styles.bubbleText, mine ? styles.bubbleTextMine : null]}>
            {message.body}
          </Text>
          <Text style={[styles.bubbleTime, mine ? styles.bubbleTimeMine : null]}>
            {formatTime(message.created_at)}
          </Text>
        </View>
      </View>
    </>
  );
}

const styles = StyleSheet.create({
  flex: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  centerScreen: {
    alignItems: 'center',
    backgroundColor: theme.colors.background,
    flex: 1,
    justifyContent: 'center',
  },
  notFoundWrap: {
    paddingHorizontal: theme.spacing.lg,
    width: '100%',
  },
  header: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    paddingBottom: theme.spacing.md,
    paddingHorizontal: theme.spacing.md,
    paddingTop: 54,
  },
  backButton: {
    alignItems: 'center',
    height: 36,
    justifyContent: 'center',
    width: 32,
  },
  headerCopy: {
    flex: 1,
  },
  headerName: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  headerCompany: {
    color: theme.colors.primary,
    fontSize: 12.5,
    fontWeight: '700',
    marginTop: 1,
  },
  thread: {
    gap: 3,
    padding: theme.spacing.md,
    paddingBottom: theme.spacing.lg,
  },
  emptyThread: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingTop: theme.spacing.xxl,
  },
  emptyIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 64,
    justifyContent: 'center',
    marginBottom: theme.spacing.xs,
    width: 64,
  },
  emptyTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  emptyText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    maxWidth: '80%',
    textAlign: 'center',
  },
  dayDivider: {
    alignItems: 'center',
    marginVertical: theme.spacing.sm,
  },
  dayText: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.pill,
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '800',
    overflow: 'hidden',
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 4,
  },
  bubbleRow: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    marginVertical: 1.5,
  },
  bubbleRowMine: {
    justifyContent: 'flex-end',
  },
  bubble: {
    borderRadius: theme.radius.lg,
    maxWidth: '80%',
    paddingBottom: 6,
    paddingHorizontal: theme.spacing.md,
    paddingTop: theme.spacing.sm,
  },
  bubbleTheirs: {
    backgroundColor: theme.colors.surface,
    borderBottomLeftRadius: theme.radius.sm,
    borderColor: theme.colors.border,
    borderWidth: 1,
  },
  bubbleMine: {
    backgroundColor: theme.colors.primary,
    borderBottomRightRadius: theme.radius.sm,
  },
  bubbleText: {
    color: theme.colors.text,
    fontSize: 14.5,
    lineHeight: 20,
  },
  bubbleTextMine: {
    color: theme.colors.white,
  },
  bubbleTime: {
    color: theme.colors.textSubtle,
    fontSize: 10.5,
    fontWeight: '700',
    marginTop: 3,
    textAlign: 'right',
  },
  bubbleTimeMine: {
    color: theme.colors.onPrimaryMuted,
  },
  error: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
    fontWeight: '800',
    paddingBottom: theme.spacing.xs,
    textAlign: 'center',
  },
  composer: {
    alignItems: 'flex-end',
    backgroundColor: theme.colors.surface,
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    paddingBottom: theme.spacing.lg,
    paddingHorizontal: theme.spacing.md,
    paddingTop: theme.spacing.sm,
  },
  composerInput: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.lg,
    color: theme.colors.text,
    flex: 1,
    fontSize: 14.5,
    maxHeight: 110,
    minHeight: 44,
    paddingHorizontal: theme.spacing.md,
    paddingTop: 12,
    paddingBottom: 12,
  },
  sendButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.primary,
    borderRadius: theme.radius.pill,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  sendButtonDisabled: {
    backgroundColor: theme.colors.borderStrong,
  },
});
