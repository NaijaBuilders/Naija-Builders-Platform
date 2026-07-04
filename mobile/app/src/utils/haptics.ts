import * as Haptics from 'expo-haptics';

/**
 * Small wrappers around expo-haptics. Every call is fire-and-forget and
 * swallowed on devices/simulators without a haptic engine.
 */
export const haptics = {
  /** Light tick for small interactions: toggles, pins, steppers. */
  tap() {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light).catch(() => undefined);
  },

  /** Success pulse: added to cart, saved, message sent. */
  success() {
    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success).catch(
      () => undefined
    );
  },

  /** Warning/destructive pulse: remove, delete, dispute. */
  warning() {
    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Warning).catch(
      () => undefined
    );
  },
};
