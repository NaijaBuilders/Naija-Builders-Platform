import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import type { MainTabParamList } from '../navigation/types';
import { notificationService } from '../services';
import { theme } from '../theme';
import type { AppNotificationItem } from '../types';

type IconName = React.ComponentProps<typeof Ionicons>['name'];
type NotificationsNavigation = BottomTabNavigationProp<MainTabParamList>;

const EVENT_ICONS: Record<string, IconName> = {
  order_placed: 'receipt-outline',
  order_status: 'sync-outline',
  delivery_update: 'car-outline',
  delivery_confirm: 'home-outline',
  new_message: 'chatbubble-ellipses-outline',
  rfq_received: 'document-text-outline',
  new_quote: 'pricetag-outline',
  payment_received: 'wallet-outline',
  general: 'notifications-outline',
};

export function NotificationsScreen() {
  const navigation = useNavigation<NotificationsNavigation>();
  const [notifications, setNotifications] = useState<AppNotificationItem[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    try {
      const inbox = await notificationService.getInbox();
      setNotifications(inbox.notifications);
      setUnreadCount(inbox.unreadCount);
      setError('');
    } catch (reason) {
      setError(
        reason instanceof Error
          ? reason.message
          : 'Could not load notifications.'
      );
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const markAllRead = async () => {
    setNotifications((current) =>
      current.map((item) => ({ ...item, read: true }))
    );
    setUnreadCount(0);
    try {
      await notificationService.markRead();
    } catch {
      load();
    }
  };

  const openNotification = async (notification: AppNotificationItem) => {
    if (!notification.read) {
      setNotifications((current) =>
        current.map((item) =>
          item.id === notification.id ? { ...item, read: true } : item
        )
      );
      setUnreadCount((current) => Math.max(0, current - 1));
      notificationService.markRead(notification.id).catch(() => undefined);
    }

    if (notification.data.order_id) {
      navigation.navigate('Orders', {
        screen: 'OrderDetail',
        params: { orderId: notification.data.order_id },
      });
      return;
    }

    if (notification.data.contact_id) {
      navigation.navigate('Messages', {
        screen: 'Chat',
        params: { conversationId: notification.data.contact_id },
      });
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading notifications" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Updates"
        title="Notifications"
        subtitle={
          unreadCount > 0
            ? `${unreadCount} unread update${unreadCount === 1 ? '' : 's'}.`
            : 'You are all caught up.'
        }
      />

      {unreadCount > 0 ? (
        <Pressable
          accessibilityRole="button"
          onPress={markAllRead}
          style={styles.markAll}
        >
          <Ionicons
            color={theme.colors.primary}
            name="checkmark-done-outline"
            size={16}
          />
          <Text style={styles.markAllText}>Mark all as read</Text>
        </Pressable>
      ) : null}

      {error ? <Text style={styles.error}>{error}</Text> : null}

      {notifications.length === 0 && !error ? (
        <Card style={styles.emptyCard}>
          <View style={styles.emptyIcon}>
            <Ionicons
              color={theme.colors.primary}
              name="notifications-outline"
              size={30}
            />
          </View>
          <Text style={styles.emptyTitle}>No notifications yet</Text>
          <Text style={styles.emptyText}>
            Order updates, new messages and quotes will show up here.
          </Text>
        </Card>
      ) : (
        notifications.map((notification) => (
          <Card key={notification.id} style={styles.itemCard}>
            <Pressable
              accessibilityRole="button"
              onPress={() => openNotification(notification)}
              style={styles.itemRow}
            >
              <View
                style={[
                  styles.itemIcon,
                  !notification.read ? styles.itemIconUnread : null,
                ]}
              >
                <Ionicons
                  color={
                    notification.read
                      ? theme.colors.textSubtle
                      : theme.colors.primary
                  }
                  name={EVENT_ICONS[notification.eventKey] ?? EVENT_ICONS.general}
                  size={20}
                />
              </View>
              <View style={styles.itemCopy}>
                <Text
                  style={[
                    styles.itemTitle,
                    !notification.read ? styles.itemTitleUnread : null,
                  ]}
                >
                  {notification.title}
                </Text>
                {notification.body ? (
                  <Text numberOfLines={2} style={styles.itemBody}>
                    {notification.body}
                  </Text>
                ) : null}
                <Text style={styles.itemTime}>
                  {formatRelativeTime(notification.createdAt)}
                </Text>
              </View>
              {!notification.read ? <View style={styles.unreadDot} /> : null}
            </Pressable>
          </Card>
        ))
      )}
    </Screen>
  );
}

function formatRelativeTime(value: string): string {
  if (!value) {
    return '';
  }

  const then = new Date(value).getTime();
  if (!Number.isFinite(then)) {
    return value;
  }

  const diffMinutes = Math.round((Date.now() - then) / 60000);
  if (diffMinutes < 1) {
    return 'Just now';
  }
  if (diffMinutes < 60) {
    return `${diffMinutes}m ago`;
  }

  const diffHours = Math.round(diffMinutes / 60);
  if (diffHours < 24) {
    return `${diffHours}h ago`;
  }

  const diffDays = Math.round(diffHours / 24);
  if (diffDays < 7) {
    return `${diffDays}d ago`;
  }

  return new Date(value).toLocaleDateString();
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  markAll: {
    alignItems: 'center',
    alignSelf: 'flex-end',
    flexDirection: 'row',
    gap: theme.spacing.xs,
    marginBottom: theme.spacing.md,
  },
  markAllText: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '900',
  },
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
  emptyCard: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xl,
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
    fontSize: 17,
    fontWeight: '900',
  },
  emptyText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    textAlign: 'center',
  },
  itemCard: {
    marginBottom: theme.spacing.md,
    padding: 0,
  },
  itemRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  itemIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    height: 42,
    justifyContent: 'center',
    width: 42,
  },
  itemIconUnread: {
    backgroundColor: theme.colors.primarySoft,
  },
  itemCopy: {
    flex: 1,
  },
  itemTitle: {
    color: theme.colors.text,
    fontSize: 14.5,
    fontWeight: '700',
  },
  itemTitleUnread: {
    fontWeight: '900',
  },
  itemBody: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 2,
  },
  itemTime: {
    color: theme.colors.textSubtle,
    fontSize: 11.5,
    fontWeight: '800',
    marginTop: 4,
  },
  unreadDot: {
    backgroundColor: theme.colors.secondary,
    borderRadius: theme.radius.pill,
    height: 9,
    width: 9,
  },
});
