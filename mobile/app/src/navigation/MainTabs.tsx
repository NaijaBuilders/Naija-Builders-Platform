import { Ionicons } from '@expo/vector-icons';
import {
  createBottomTabNavigator,
  type BottomTabBarProps,
} from '@react-navigation/bottom-tabs';
import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAppState } from '../context/AppContext';
import { theme } from '../theme';
import { haptics } from '../utils/haptics';
import {
  BrowseStack,
  HomeStack,
  MessagesStack,
  OrdersStack,
  ProfileStack,
} from './TabStacks';
import type { MainTabParamList } from './types';

const Tab = createBottomTabNavigator<MainTabParamList>();

type IconName = React.ComponentProps<typeof Ionicons>['name'];

const tabIcons: Record<
  keyof MainTabParamList,
  { active: IconName; inactive: IconName }
> = {
  Home: { active: 'grid', inactive: 'grid-outline' },
  Browse: { active: 'cube', inactive: 'cube-outline' },
  Orders: { active: 'reader', inactive: 'reader-outline' },
  Messages: { active: 'chatbubble', inactive: 'chatbubble-outline' },
  Profile: { active: 'person-circle', inactive: 'person-circle-outline' },
};

function CustomTabBar({
  state,
  descriptors,
  navigation,
}: BottomTabBarProps) {
  const insets = useSafeAreaInsets();

  return (
    <View
      style={[
        styles.barWrap,
        { paddingBottom: Math.max(insets.bottom, theme.spacing.sm) },
      ]}
    >
      <View style={styles.bar}>
        {state.routes.map((route, index) => {
          const { options } = descriptors[route.key];
          const label =
            typeof options.title === 'string' ? options.title : route.name;
          const focused = state.index === index;
          const icon = tabIcons[route.name as keyof MainTabParamList];

          const onPress = () => {
            const event = navigation.emit({
              type: 'tabPress',
              target: route.key,
              canPreventDefault: true,
            });

            if (!focused && !event.defaultPrevented) {
              haptics.tap();
              navigation.navigate(route.name);
            }
          };

          return (
            <Pressable
              accessibilityLabel={label}
              accessibilityRole="button"
              accessibilityState={{ selected: focused }}
              key={route.key}
              onPress={onPress}
              style={styles.tab}
            >
              <View style={[styles.iconPill, focused ? styles.iconPillActive : null]}>
                <Ionicons
                  color={focused ? theme.colors.white : theme.colors.textSubtle}
                  name={focused ? icon.active : icon.inactive}
                  size={22}
                />
              </View>
              <Text
                numberOfLines={1}
                style={[styles.label, focused ? styles.labelActive : null]}
              >
                {label}
              </Text>
            </Pressable>
          );
        })}
      </View>
    </View>
  );
}

export function MainTabs() {
  const { currentRole } = useAppState();
  const browseLabel = currentRole === 'supplier' ? 'Listings' : 'Browse';

  return (
    <Tab.Navigator
      screenOptions={{ headerShown: false }}
      tabBar={(props) => <CustomTabBar {...props} />}
    >
      <Tab.Screen name="Home" component={HomeStack} options={{ title: 'Home' }} />
      <Tab.Screen
        name="Browse"
        component={BrowseStack}
        options={{ title: browseLabel }}
      />
      <Tab.Screen name="Orders" component={OrdersStack} options={{ title: 'Orders' }} />
      <Tab.Screen
        name="Messages"
        component={MessagesStack}
        options={{ title: 'Chats' }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileStack}
        options={{ title: 'Profile' }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  barWrap: {
    backgroundColor: 'transparent',
    paddingHorizontal: theme.spacing.md,
    paddingTop: theme.spacing.sm,
  },
  bar: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.xl,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingHorizontal: theme.spacing.xs,
    paddingVertical: theme.spacing.sm,
    ...theme.shadows.elevated,
  },
  tab: {
    alignItems: 'center',
    flex: 1,
    gap: 3,
  },
  iconPill: {
    alignItems: 'center',
    borderRadius: theme.radius.pill,
    height: 38,
    justifyContent: 'center',
    width: 52,
  },
  iconPillActive: {
    backgroundColor: theme.colors.primary,
  },
  label: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '800',
  },
  labelActive: {
    color: theme.colors.primary,
  },
});
