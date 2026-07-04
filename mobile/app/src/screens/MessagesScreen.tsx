import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Alert,
  FlatList,
  ListRenderItemInfo,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  ConversationRow,
  FloatingBackButton,
  Input,
  Loader,
} from '../components';
import { useConversations } from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import type { MainTabParamList, MessagesStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Conversation } from '../types';

type IconName = React.ComponentProps<typeof Ionicons>['name'];
type Category = 'rfq' | 'order' | 'delivery' | 'service' | 'general';
type TabKey = 'all' | 'rfq' | 'order' | 'delivery' | 'service' | 'archived';

const PINNED_KEY = 'naijabuilders.messages.pinned';
const ARCHIVED_KEY = 'naijabuilders.messages.archived';

const TABS: Array<{ key: TabKey; label: string; icon: IconName }> = [
  { key: 'all', label: 'All Chats', icon: 'chatbubbles-outline' },
  { key: 'rfq', label: 'RFQs', icon: 'pricetag-outline' },
  { key: 'order', label: 'Orders', icon: 'cart-outline' },
  { key: 'delivery', label: 'Deliveries', icon: 'car-outline' },
  { key: 'service', label: 'Services', icon: 'construct-outline' },
  { key: 'archived', label: 'Archived', icon: 'archive-outline' },
];

const CATEGORY_TAG: Record<
  Category,
  { label: string; tone: 'primary' | 'success' | 'warning' | 'neutral' } | null
> = {
  rfq: { label: 'Quote', tone: 'primary' },
  order: { label: 'Order', tone: 'primary' },
  delivery: { label: 'Delivery', tone: 'warning' },
  service: { label: 'Service', tone: 'success' },
  general: null,
};

const EMPTY_COPY: Record<TabKey, string> = {
  all: 'No conversations yet. Start a chat from a product or RFQ.',
  rfq: 'No quote chats yet. Messages about RFQs and quotes show here.',
  order: 'No order chats yet. Conversations about orders show here.',
  delivery: 'No delivery chats yet. Delivery coordination shows here.',
  service: 'No service chats yet. Service bookings show here.',
  archived: 'No archived chats. Long-press a chat to archive it.',
};

const listPerformanceProps = {
  initialNumToRender: 8,
  maxToRenderPerBatch: 8,
  removeClippedSubviews: Platform.OS === 'android',
  updateCellsBatchingPeriod: 50,
  windowSize: 7,
};

function deriveCategory(conversation: Conversation): Category {
  const thread = conversation.messages?.map((message) => message.body).join(' ') ?? '';
  const text = `${conversation.lastMessage} ${thread}`.toLowerCase();

  if (/\b(rfq|quote|quotation|pricing|price list)\b/.test(text)) {
    return 'rfq';
  }
  if (/\b(deliver|delivery|dispatch|truck|offload|drop off|driver)\b/.test(text)) {
    return 'delivery';
  }
  if (/\b(order|invoice|payment|paid|receipt|po\b)\b/.test(text)) {
    return 'order';
  }
  if (/\b(service|install|installation|engineer|technician|artisan|repair|booking|plumber|electrician)\b/.test(text)) {
    return 'service';
  }
  return 'general';
}

export function MessagesScreen() {
  const navigation =
    useNavigation<NativeStackNavigationProp<MessagesStackParamList, 'MessagesMain'>>();
  const { data: conversations, error, loading } = useConversations();
  const animatedStyle = useScreenAnimation();

  const [activeTab, setActiveTab] = useState<TabKey>('all');
  const [query, setQuery] = useState('');
  const [pinned, setPinned] = useState<string[]>([]);
  const [archived, setArchived] = useState<string[]>([]);

  useEffect(() => {
    Promise.all([
      AsyncStorage.getItem(PINNED_KEY),
      AsyncStorage.getItem(ARCHIVED_KEY),
    ])
      .then(([p, a]) => {
        if (p) {
          setPinned(JSON.parse(p));
        }
        if (a) {
          setArchived(JSON.parse(a));
        }
      })
      .catch(() => undefined);
  }, []);

  const persist = useCallback((key: string, value: string[]) => {
    AsyncStorage.setItem(key, JSON.stringify(value)).catch(() => undefined);
  }, []);

  const categoryById = useMemo(() => {
    const map = new Map<string, Category>();
    conversations.forEach((conversation) => {
      map.set(conversation.id, deriveCategory(conversation));
    });
    return map;
  }, [conversations]);

  const counts = useMemo(() => {
    const result: Record<TabKey, number> = {
      all: 0,
      rfq: 0,
      order: 0,
      delivery: 0,
      service: 0,
      archived: 0,
    };
    conversations.forEach((conversation) => {
      if (archived.includes(conversation.id)) {
        result.archived += 1;
        return;
      }
      result.all += 1;
      const category = categoryById.get(conversation.id);
      if (category && category !== 'general') {
        result[category] += 1;
      }
    });
    return result;
  }, [archived, categoryById, conversations]);

  const filtered = useMemo(() => {
    const normalizedQuery = query.trim().toLowerCase();

    const base = conversations.filter((conversation) => {
      const isArchived = archived.includes(conversation.id);
      if (activeTab === 'archived') {
        return isArchived;
      }
      if (isArchived) {
        return false;
      }
      if (activeTab === 'all') {
        return true;
      }
      return categoryById.get(conversation.id) === activeTab;
    });

    const searched = normalizedQuery
      ? base.filter((conversation) =>
          [conversation.participantName, conversation.company, conversation.lastMessage]
            .join(' ')
            .toLowerCase()
            .includes(normalizedQuery)
        )
      : base;

    return [...searched].sort((a, b) => {
      const aPinned = pinned.includes(a.id) ? 1 : 0;
      const bPinned = pinned.includes(b.id) ? 1 : 0;
      return bPinned - aPinned;
    });
  }, [activeTab, archived, categoryById, conversations, pinned, query]);

  const togglePin = useCallback(
    (id: string) => {
      setPinned((current) => {
        const next = current.includes(id)
          ? current.filter((item) => item !== id)
          : [...current, id];
        persist(PINNED_KEY, next);
        return next;
      });
    },
    [persist]
  );

  const toggleArchive = useCallback(
    (id: string) => {
      setArchived((current) => {
        const next = current.includes(id)
          ? current.filter((item) => item !== id)
          : [...current, id];
        persist(ARCHIVED_KEY, next);
        return next;
      });
    },
    [persist]
  );

  const openActions = useCallback(
    (conversation: Conversation) => {
      const isPinned = pinned.includes(conversation.id);
      const isArchived = archived.includes(conversation.id);
      Alert.alert(conversation.participantName, undefined, [
        {
          text: isPinned ? 'Unpin' : 'Pin to top',
          onPress: () => togglePin(conversation.id),
        },
        {
          text: isArchived ? 'Unarchive' : 'Archive',
          onPress: () => toggleArchive(conversation.id),
        },
        { text: 'Cancel', style: 'cancel' },
      ]);
    },
    [archived, pinned, toggleArchive, togglePin]
  );

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
    ({ item, index }: ListRenderItemInfo<Conversation>) => {
      const category = categoryById.get(item.id) ?? 'general';
      const tag = activeTab === 'all' ? CATEGORY_TAG[category] : null;
      return (
        <ConversationRow
          animationIndex={index}
          conversation={item}
          onPress={openConversation}
          onLongPress={openActions}
          pinned={pinned.includes(item.id)}
          tagLabel={tag?.label}
          tagTone={tag?.tone}
        />
      );
    },
    [activeTab, categoryById, openActions, openConversation, pinned]
  );

  const keyExtractor = useCallback((item: Conversation) => item.id, []);

  const header = useMemo(
    () => (
      <>
        <View style={styles.titleRow}>
          <Text style={styles.title}>Messages</Text>
          <Ionicons
            color={theme.colors.textSubtle}
            name="create-outline"
            size={22}
          />
        </View>
        <View style={styles.searchWrap}>
          <Ionicons
            color={theme.colors.textSubtle}
            name="search"
            size={18}
            style={styles.searchIcon}
          />
          <Input
            placeholder="Search chats..."
            value={query}
            onChangeText={setQuery}
            containerStyle={styles.searchInput}
            style={styles.searchField}
          />
        </View>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.tabRail}
        >
          {TABS.map((tab) => {
            const active = tab.key === activeTab;
            const count = counts[tab.key];
            return (
              <Pressable
                key={tab.key}
                accessibilityRole="button"
                onPress={() => setActiveTab(tab.key)}
                style={[styles.tab, active ? styles.tabActive : null]}
              >
                <Ionicons
                  color={active ? theme.colors.white : theme.colors.textMuted}
                  name={tab.icon}
                  size={15}
                />
                <Text
                  style={[styles.tabText, active ? styles.tabTextActive : null]}
                >
                  {tab.label}
                </Text>
                {count > 0 ? (
                  <View
                    style={[styles.tabCount, active ? styles.tabCountActive : null]}
                  >
                    <Text
                      style={[
                        styles.tabCountText,
                        active ? styles.tabCountTextActive : null,
                      ]}
                    >
                      {count}
                    </Text>
                  </View>
                ) : null}
              </Pressable>
            );
          })}
        </ScrollView>
      </>
    ),
    [activeTab, counts, query]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={filtered}
          keyExtractor={keyExtractor}
          ListHeaderComponent={header}
          ListEmptyComponent={
            loading ? (
              <Loader label="Loading messages" />
            ) : (
              <View style={styles.empty}>
                <Ionicons
                  color={theme.colors.textSubtle}
                  name="chatbubbles-outline"
                  size={34}
                />
                <Text style={styles.emptyText}>
                  {error?.message || EMPTY_COPY[activeTab]}
                </Text>
              </View>
            )
          }
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
  contentWithFloatingBack: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
    paddingTop: 86,
  },
  titleRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: theme.spacing.md,
  },
  title: {
    color: theme.colors.text,
    fontSize: theme.typography.title,
    fontWeight: '900',
    letterSpacing: -0.5,
  },
  searchWrap: {
    justifyContent: 'center',
    marginBottom: theme.spacing.md,
  },
  searchIcon: {
    left: theme.spacing.md,
    position: 'absolute',
    zIndex: 1,
  },
  searchInput: {
    flex: 1,
  },
  searchField: {
    paddingLeft: 42,
  },
  tabRail: {
    gap: theme.spacing.sm,
    paddingBottom: theme.spacing.lg,
  },
  tab: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    flexDirection: 'row',
    gap: 6,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: 9,
  },
  tabActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  tabText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '900',
  },
  tabTextActive: {
    color: theme.colors.white,
  },
  tabCount: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 20,
    justifyContent: 'center',
    minWidth: 20,
    paddingHorizontal: 6,
  },
  tabCountActive: {
    backgroundColor: theme.colors.glassLight,
  },
  tabCountText: {
    color: theme.colors.primaryDark,
    fontSize: 11,
    fontWeight: '900',
  },
  tabCountTextActive: {
    color: theme.colors.white,
  },
  empty: {
    alignItems: 'center',
    gap: theme.spacing.md,
    paddingHorizontal: theme.spacing.lg,
    paddingVertical: theme.spacing.xxl,
  },
  emptyText: {
    color: theme.colors.textMuted,
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
});
