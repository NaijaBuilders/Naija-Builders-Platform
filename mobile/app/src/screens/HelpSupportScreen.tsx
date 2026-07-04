import { Ionicons } from '@expo/vector-icons';
import React, { useEffect, useState } from 'react';
import { Linking, Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import { userService, type SupportContent } from '../services/userService';
import { theme } from '../theme';

const SUPPORT_EMAIL = 'info@naijabuilders.com';

const FALLBACK_REPLY =
  'We could not find an answer for that yet. Email our support team and we will respond as soon as possible.';

export function HelpSupportScreen() {
  const [content, setContent] = useState<SupportContent>({
    prompts: [],
    rules: [],
  });
  const [question, setQuestion] = useState('');
  const [answer, setAnswer] = useState('');

  useEffect(() => {
    let mounted = true;

    userService
      .getSupportContent()
      .then((support) => {
        if (mounted) {
          setContent(support);
        }
      })
      .catch(() => undefined);

    return () => {
      mounted = false;
    };
  }, []);

  const findAnswer = (text: string) => {
    const normalized = text.toLowerCase();
    const match = content.rules.find((rule) =>
      rule.keys.some((key) => normalized.includes(key.toLowerCase()))
    );

    setQuestion(text);
    setAnswer(match?.reply ?? FALLBACK_REPLY);
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Support"
        title="Help & support"
        subtitle="Quick answers, or reach the NaijaBuilders team directly."
      />

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Ask a question</Text>
        <Input
          label="What do you need help with?"
          onChangeText={setQuestion}
          placeholder="e.g. My listing is not showing"
          value={question}
        />
        <Button
          disabled={question.trim().length < 3}
          onPress={() => findAnswer(question)}
          title="Get answer"
          variant="outline"
        />

        {answer ? (
          <View style={styles.answerBox}>
            <Ionicons
              color={theme.colors.primary}
              name="chatbubble-ellipses"
              size={18}
            />
            <Text style={styles.answerText}>{answer}</Text>
          </View>
        ) : null}
      </Card>

      {content.prompts.length > 0 ? (
        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>Common questions</Text>
          {content.prompts.map((prompt, index) => (
            <Pressable
              accessibilityRole="button"
              key={prompt}
              onPress={() => findAnswer(prompt)}
              style={[
                styles.promptRow,
                index < content.prompts.length - 1 ? styles.promptBorder : null,
              ]}
            >
              <Ionicons
                color={theme.colors.primary}
                name="help-circle-outline"
                size={18}
              />
              <Text style={styles.promptText}>{prompt}</Text>
              <Ionicons
                color={theme.colors.textSubtle}
                name="chevron-forward"
                size={16}
              />
            </Pressable>
          ))}
        </Card>
      ) : null}

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Still stuck?</Text>
        <Pressable
          accessibilityRole="button"
          onPress={() =>
            Linking.openURL(
              `mailto:${SUPPORT_EMAIL}?subject=NaijaBuilders app support`
            ).catch(() => undefined)
          }
          style={styles.contactRow}
        >
          <View style={styles.contactIcon}>
            <Ionicons color={theme.colors.primary} name="mail-outline" size={18} />
          </View>
          <View style={styles.contactCopy}>
            <Text style={styles.contactLabel}>Email support</Text>
            <Text style={styles.contactValue}>{SUPPORT_EMAIL}</Text>
          </View>
          <Ionicons
            color={theme.colors.textSubtle}
            name="chevron-forward"
            size={18}
          />
        </Pressable>
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 86,
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  answerBox: {
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  answerText: {
    color: theme.colors.text,
    flex: 1,
    fontSize: 13.5,
    lineHeight: 20,
  },
  promptRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.sm,
  },
  promptBorder: {
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
  },
  promptText: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13.5,
    fontWeight: '700',
    lineHeight: 19,
  },
  contactRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  contactIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  contactCopy: {
    flex: 1,
  },
  contactLabel: {
    color: theme.colors.text,
    fontSize: 14.5,
    fontWeight: '900',
  },
  contactValue: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    marginTop: 2,
  },
});
