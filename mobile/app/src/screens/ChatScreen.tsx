import React, { useCallback, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Loader,
  Screen,
} from '../components';
import { useConversation } from '../hooks/useMarketplaceData';
import { messageService } from '../services';
import { theme } from '../theme';

type ChatScreenProps = {
  route: {
    params: {
      conversationId: string;
    };
  };
};

export function ChatScreen({ route }: ChatScreenProps) {
  const {
    data: conversation,
    error,
    loading,
    refresh,
  } = useConversation(route.params.conversationId);
  const [draft, setDraft] = useState('');
  const [sendError, setSendError] = useState('');
  const [sending, setSending] = useState(false);

  const handleSend = useCallback(async () => {
    const body = draft.trim();

    if (!body) {
      return;
    }

    setSending(true);
    setSendError('');

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
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading chat" />
      </Screen>
    );
  }

  if (!conversation) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Text style={styles.empty}>
          {error?.message || 'Conversation not found.'}
        </Text>
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow={conversation.company}
        title={conversation.participantName}
        subtitle={`Last activity ${conversation.lastMessageAt}`}
      />

      {conversation.messages.length > 0 ? (
        conversation.messages.map((message) => (
        <View
          key={message.id}
          style={[
            styles.messageRow,
            message.sender === 'me' ? styles.messageRowMine : null,
          ]}
        >
          <Card
            style={[
              styles.messageBubble,
              message.sender === 'me' ? styles.messageBubbleMine : null,
            ]}
          >
            <Text
              style={[
                styles.messageText,
                message.sender === 'me' ? styles.messageTextMine : null,
              ]}
            >
              {message.body}
            </Text>
          </Card>
        </View>
        ))
      ) : (
        <Text style={styles.empty}>No messages in this conversation.</Text>
      )}

      <Card style={styles.composer}>
        <Input
          placeholder="Write a message..."
          value={draft}
          onChangeText={setDraft}
        />
        {sendError ? <Text style={styles.error}>{sendError}</Text> : null}
        <Button
          title="Send"
          onPress={handleSend}
          loading={sending}
          disabled={!draft.trim()}
        />
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
  messageRow: {
    alignItems: 'flex-start',
    marginBottom: theme.spacing.sm,
  },
  messageRowMine: {
    alignItems: 'flex-end',
  },
  messageBubble: {
    borderBottomLeftRadius: theme.radius.sm,
    maxWidth: '82%',
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
  },
  messageBubbleMine: {
    backgroundColor: theme.colors.primary,
    borderBottomLeftRadius: theme.radius.lg,
    borderBottomRightRadius: theme.radius.sm,
    borderColor: theme.colors.primary,
  },
  messageText: {
    color: theme.colors.text,
    fontSize: 14,
    lineHeight: 20,
  },
  messageTextMine: {
    color: theme.colors.white,
  },
  composer: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    textAlign: 'center',
  },
  error: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
});
