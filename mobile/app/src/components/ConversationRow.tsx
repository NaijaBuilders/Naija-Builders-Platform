import { Ionicons } from '@expo/vector-icons';
import React, { useCallback } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { Conversation } from '../types';
import { Avatar } from './Avatar';
import { Badge } from './Badge';
import { Card } from './Card';

type IconName = React.ComponentProps<typeof Ionicons>['name'];

type ConversationRowProps = {
  conversation: Conversation;
  animationIndex?: number;
  onPress?: (conversation: Conversation) => void;
  onLongPress?: (conversation: Conversation) => void;
  pinned?: boolean;
  tagLabel?: string;
  tagTone?: 'primary' | 'success' | 'warning' | 'neutral';
  tagIcon?: IconName;
};

export const ConversationRow = React.memo(function ConversationRow({
  animationIndex,
  conversation,
  onPress,
  onLongPress,
  pinned,
  tagLabel,
  tagTone = 'neutral',
}: ConversationRowProps) {
  const handlePress = useCallback(() => {
    onPress?.(conversation);
  }, [conversation, onPress]);
  const handleLongPress = useCallback(() => {
    onLongPress?.(conversation);
  }, [conversation, onLongPress]);
  const unread = conversation.unreadCount > 0;

  return (
    <Card animationIndex={animationIndex} style={styles.card}>
      <Pressable
        disabled={!onPress}
        onPress={handlePress}
        onLongPress={handleLongPress}
        delayLongPress={250}
        style={styles.pressable}
      >
        <Avatar name={conversation.participantName} size={46} />
        <View style={styles.copy}>
          <View style={styles.top}>
            <Text numberOfLines={1} style={styles.name}>
              {conversation.participantName}
            </Text>
            {pinned ? (
              <Ionicons
                color={theme.colors.textSubtle}
                name="pin"
                size={13}
                style={styles.pin}
              />
            ) : null}
            <Text style={[styles.time, unread ? styles.timeUnread : null]}>
              {conversation.lastMessageAt}
            </Text>
          </View>
          <Text numberOfLines={1} style={styles.company}>
            {conversation.company}
          </Text>
          <View style={styles.bottom}>
            <Text
              numberOfLines={1}
              style={[styles.message, unread ? styles.messageUnread : null]}
            >
              {conversation.lastMessage}
            </Text>
            {unread ? (
              <View style={styles.unread}>
                <Text style={styles.unreadText}>{conversation.unreadCount}</Text>
              </View>
            ) : null}
          </View>
          {tagLabel ? (
            <View style={styles.tagRow}>
              <Badge label={tagLabel} tone={tagTone} />
            </View>
          ) : null}
        </View>
      </Pressable>
    </Card>
  );
});

const styles = StyleSheet.create({
  card: {
    marginBottom: theme.spacing.md,
    padding: 0,
  },
  pressable: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    minHeight: 92,
    padding: theme.spacing.md,
  },
  copy: {
    flex: 1,
  },
  top: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  name: {
    color: theme.colors.text,
    flexShrink: 1,
    fontSize: 16,
    fontWeight: '900',
  },
  pin: {
    marginLeft: 5,
  },
  time: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '800',
    marginLeft: 'auto',
  },
  timeUnread: {
    color: theme.colors.secondary,
  },
  company: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '800',
    marginTop: 2,
  },
  bottom: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginTop: theme.spacing.xs,
  },
  message: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13,
    lineHeight: 19,
  },
  messageUnread: {
    color: theme.colors.text,
    fontWeight: '700',
  },
  tagRow: {
    flexDirection: 'row',
    marginTop: theme.spacing.sm,
  },
  unread: {
    alignItems: 'center',
    backgroundColor: theme.colors.secondary,
    borderRadius: theme.radius.pill,
    height: 22,
    justifyContent: 'center',
    minWidth: 22,
    paddingHorizontal: 7,
  },
  unreadText: {
    color: theme.colors.white,
    fontSize: 11,
    fontWeight: '900',
  },
});
