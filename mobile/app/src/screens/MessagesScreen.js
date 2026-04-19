import React, { useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to /api/mobile/messages endpoints (Mobile/MessageController).
export default function MessagesScreen({ route }) {
  const initialReceiverId = route?.params?.receiver_id || 0;
  const [contacts, setContacts] = useState([]);
  const [threadMessages, setThreadMessages] = useState([]);
  const [selectedContactId, setSelectedContactId] = useState(initialReceiverId);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const loadMessages = async (contactId) => {
    try {
      const response = await api.getMessages({ contact_id: contactId });
      setContacts(response.data.contacts || []);
      setThreadMessages(response.data.thread_messages || []);
      setSelectedContactId(response.data.selected_contact_id || contactId);
    } catch (err) {
      setError('Unable to load messages.');
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
      await loadMessages(selectedContactId);
    } catch (err) {
      setError('Unable to send message.');
    }
  };

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Messages</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
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
          </Pressable>
        )}
      />

      <View style={styles.threadCard}>
        {threadMessages.length === 0 ? <Text style={styles.meta}>No conversation yet.</Text> : null}
        {threadMessages.map((msg) => (
          <View key={msg.id} style={styles.messageBubble}>
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
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  contactList: {
    marginVertical: 12,
  },
  contactChip: {
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
  threadCard: {
    padding: 14,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
    minHeight: 200,
  },
  messageBubble: {
    padding: 10,
    borderRadius: 12,
    backgroundColor: '#F4F1EC',
    marginBottom: 8,
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
