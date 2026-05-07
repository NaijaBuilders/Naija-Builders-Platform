import type { NativeStackNavigationOptions } from '@react-navigation/native-stack';
import { theme } from '../theme';

export const defaultStackScreenOptions: NativeStackNavigationOptions = {
  animation: 'slide_from_right',
  animationDuration: 280,
  contentStyle: {
    backgroundColor: theme.colors.background,
  },
  gestureEnabled: true,
  headerShown: false,
};

export const modalStackScreenOptions: NativeStackNavigationOptions = {
  animation: 'slide_from_bottom',
  animationDuration: 280,
  contentStyle: {
    backgroundColor: 'rgba(17, 24, 39, 0.38)',
  },
  gestureEnabled: true,
  headerShown: false,
  presentation: 'transparentModal',
};
