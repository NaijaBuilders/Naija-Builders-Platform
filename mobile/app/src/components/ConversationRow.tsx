import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { Conversation } from '../types';
import { Avatar } from './Avatar';
import { Card } from './Card';

type ConversationRowProps = {
  conversation: Conversation;
  animationIndex?: number;
  onPress?: (conversation: Conversation) => void;
};

export const ConversationRow = React.memo(function ConversationRow({
  animationIndex,
  conversation,
  onPress,
}: ConversationRowProps) {
  return (
    <Card animationIndex={animationIndex} style={styles.card}>
      <Pressable
        disabled={!onPress}
        onPress={() => onPress?.(conversation)}
        style={styles.pressable}
      >
        <Avatar name={conversation.participantName} size={46} />
        <View style={styles.copy}>
          <View style={styles.top}>
            <Text numberOfLines={1} style={styles.name}>
              {conversation.participantName}
            </Text>
            <Text style={styles.time}>{conversation.lastMessageAt}</Text>
          </View>
          <Text numberOfLines={1} style={styles.company}>
            {conversation.company}
          </Text>
          <Text numberOfLines={2} style={styles.message}>
            {conversation.lastMessage}
          </Text>
        </View>
        {conversation.unreadCount > 0 ? (
          <View style={styles.unread}>
            <Text style={styles.unreadText}>{conversation.unreadCount}</Text>
          </View>
        ) : null}
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
    flex: 1,
    fontSize: 16,
    fontWeight: '900',
  },
  time: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '800',
  },
  company: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '800',
    marginTop: 2,
  },
  message: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: theme.spacing.xs,
  },
  unread: {
    alignItems: 'center',
    backgroundColor: theme.colors.secondary,
    borderRadius: theme.radius.pill,
    height: 26,
    justifyContent: 'center',
    minWidth: 26,
    paddingHorizontal: 8,
  },
  unreadText: {
    color: theme.colors.white,
    fontSize: 12,
    fontWeight: '900',
  },
});
