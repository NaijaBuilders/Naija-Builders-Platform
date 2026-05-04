import React, { useContext, useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { EmptyState, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, radius, spacing } from '../styles/theme';

// Maps to /api/mobile/messages endpoints (Mobile/MessageController).
export default function MessagesScreen({ route }) {
  const { user } = useContext(AuthContext);
  const initialReceiverId = route?.params?.receiver_id || 0;
  const [contacts, setContacts] = useState([]);
  const [threadMessages, setThreadMessages] = useState([]);
  const [selectedContactId, setSelectedContactId] = useState(initialReceiverId);
  const [selectedContact, setSelectedContact] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const loadMessages = async (contactId, refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getMessages({ contact_id: contactId });
      setContacts(response.data.contacts || []);
      setThreadMessages(response.data.thread_messages || []);
      setSelectedContactId(response.data.selected_contact_id || contactId);
      setSelectedContact(response.data.selected_contact || null);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load messages.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadMessages(initialReceiverId);
  }, [initialReceiverId]);

  const handleSend = async () => {
    if (!selectedContactId || message.trim() === '') {
      return;
    }

    try {
      await api.sendMessage({ receiver_id: selectedContactId, content: message.trim() });
      setMessage('');
      await loadMessages(selectedContactId, true);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to send message.');
    }
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadMessages(selectedContactId, true)}>
      <ScreenHeader title="Messages" subtitle="Keep buyer and supplier conversations clear and project-focused." />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading messages..." /> : null}
      <FlatList
        horizontal
        data={contacts}
        keyExtractor={(item) => String(item.id)}
        showsHorizontalScrollIndicator={false}
        style={styles.contactList}
        renderItem={({ item }) => (
          <Pressable
            style={[styles.contactChip, item.id === selectedContactId && styles.contactChipActive]}
            onPress={() => loadMessages(item.id)}
          >
            <Text style={[styles.contactText, item.id === selectedContactId && styles.contactTextActive]}>
              {item.full_name || item.company || 'Contact'}
            </Text>
            {item.unread_count > 0 ? <Text style={styles.unread}>{item.unread_count}</Text> : null}
          </Pressable>
        )}
      />

      <View style={styles.threadCard}>
        {selectedContact ? <Text style={styles.threadTitle}>{selectedContact.full_name || selectedContact.company || 'Conversation'}</Text> : null}
        {!isLoading && contacts.length === 0 ? <EmptyState title="No contacts yet" body="Message a supplier from a material detail page to start a conversation." /> : null}
        {!isLoading && contacts.length > 0 && threadMessages.length === 0 ? <Text style={styles.meta}>No conversation yet. Send the first message.</Text> : null}
        {threadMessages.map((msg) => (
          <View key={msg.id} style={[styles.messageBubble, msg.sender_id === user?.id ? styles.ownBubble : styles.otherBubble]}>
            <Text style={styles.messageText}>{msg.content}</Text>
            <Text style={styles.messageMeta}>{msg.created_at}</Text>
          </View>
        ))}
      </View>

      <View style={styles.inputRow}>
        <TextInput
          style={styles.input}
          placeholder="Type a message"
          value={message}
          onChangeText={setMessage}
        />
        <Pressable style={styles.sendButton} onPress={handleSend}>
          <Text style={styles.sendButtonText}>Send</Text>
        </Pressable>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  contactList: {
    marginVertical: 12,
  },
  contactChip: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: 6,
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
    marginRight: 8,
    backgroundColor: colors.card,
  },
  contactChipActive: {
    backgroundColor: colors.accentSoft,
    borderColor: colors.accent,
  },
  contactText: {
    color: colors.muted,
  },
  contactTextActive: {
    color: colors.accent,
    fontWeight: '600',
  },
  unread: {
    backgroundColor: colors.danger,
    borderRadius: 999,
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '800',
    overflow: 'hidden',
    paddingHorizontal: 6,
    paddingVertical: 2,
  },
  threadCard: {
    padding: 14,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
    minHeight: 200,
  },
  threadTitle: {
    color: colors.ink,
    fontWeight: '800',
    marginBottom: spacing.sm,
  },
  messageBubble: {
    padding: 10,
    borderRadius: radius.sm,
    marginBottom: 8,
    maxWidth: '88%',
  },
  ownBubble: {
    alignSelf: 'flex-end',
    backgroundColor: colors.accentSoft,
  },
  otherBubble: {
    alignSelf: 'flex-start',
    backgroundColor: '#F4F1EC',
  },
  messageText: {
    color: colors.ink,
  },
  messageMeta: {
    marginTop: 4,
    fontSize: 12,
    color: colors.muted,
  },
  inputRow: {
    marginTop: 12,
    flexDirection: 'row',
    gap: 8,
    alignItems: 'center',
  },
  input: {
    flex: 1,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 12,
    backgroundColor: '#FFFDF9',
  },
  sendButton: {
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 12,
    backgroundColor: colors.accent,
  },
  sendButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  error: {
    color: '#B23A3A',
    marginTop: 6,
  },
  meta: {
    color: colors.muted,
  },
});
