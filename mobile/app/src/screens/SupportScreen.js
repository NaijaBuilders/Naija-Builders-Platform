import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Mirrors GET /support.php and GET /api/mobile/support (Mobile/SupportController@index).
export default function SupportScreen() {
  const [rules, setRules] = useState([]);
  const [prompts, setPrompts] = useState([]);
  const [messages, setMessages] = useState([
    { sender: 'bot', text: 'Hello. I am Support AI. I can help with login issues, plan questions, listings, orders, and messaging.' },
  ]);
  const [input, setInput] = useState('');

  useEffect(() => {
    const loadSupport = async () => {
      try {
        const response = await api.getSupport();
        setRules(response.data.rules || []);
        setPrompts(response.data.prompts || []);
      } catch (err) {
        setRules([]);
      }
    };

    loadSupport();
  }, []);

  const getReply = (text) => {
    const lower = text.toLowerCase();
    for (const rule of rules) {
      if (rule.keys?.some((key) => lower.includes(key))) {
        return rule.reply;
      }
    }
    return 'I can help with login, listing visibility, messages, orders, and subscription plans.';
  };

  const sendMessage = (text) => {
    const trimmed = text.trim();
    if (!trimmed) {
      return;
    }

    setMessages((prev) => [...prev, { sender: 'user', text: trimmed }, { sender: 'bot', text: getReply(trimmed) }]);
    setInput('');
  };

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Support Center</Text>
      <View style={styles.card}>
        {messages.map((message, index) => (
          <View key={`${message.sender}-${index}`} style={styles.bubbleWrap}>
            <View style={[styles.bubble, message.sender === 'user' ? styles.userBubble : styles.botBubble]}>
              <Text style={styles.bubbleText}>{message.text}</Text>
            </View>
          </View>
        ))}
      </View>

      <View style={styles.promptRow}>
        {prompts.map((prompt) => (
          <Pressable key={prompt} style={styles.promptChip} onPress={() => sendMessage(prompt)}>
            <Text style={styles.promptText}>{prompt}</Text>
          </Pressable>
        ))}
      </View>

      <View style={styles.inputRow}>
        <TextInput
          style={styles.input}
          placeholder="Ask Support AI"
          value={input}
          onChangeText={setInput}
        />
        <Pressable style={styles.sendButton} onPress={() => sendMessage(input)}>
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
    marginBottom: 12,
  },
  card: {
    padding: 14,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
    minHeight: 240,
  },
  bubbleWrap: {
    marginBottom: 8,
  },
  bubble: {
    padding: 10,
    borderRadius: 12,
  },
  botBubble: {
    backgroundColor: '#EEF4F1',
  },
  userBubble: {
    backgroundColor: colors.highlight,
    alignSelf: 'flex-end',
  },
  bubbleText: {
    color: colors.ink,
  },
  promptRow: {
    marginTop: 12,
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  promptChip: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: '#FFFDF9',
  },
  promptText: {
    color: colors.muted,
    fontSize: 12,
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
});
