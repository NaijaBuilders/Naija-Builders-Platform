import { useEffect } from 'react';
import {
  useAnimatedStyle,
  useSharedValue,
  withTiming,
} from 'react-native-reanimated';
import { animationEasing, animationTiming } from '../animations';

export function useScreenAnimation(offset = 10) {
  const opacity = useSharedValue(0);
  const translateY = useSharedValue(offset);

  useEffect(() => {
    opacity.value = withTiming(1, {
      duration: animationTiming.normal,
      easing: animationEasing.easeOut,
    });
    translateY.value = withTiming(0, {
      duration: animationTiming.normal,
      easing: animationEasing.easeOut,
    });
  }, [offset, opacity, translateY]);

  return useAnimatedStyle(() => ({
    opacity: opacity.value,
    transform: [{ translateY: translateY.value }],
  }));
}
