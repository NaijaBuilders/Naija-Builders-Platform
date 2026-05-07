import {
  Easing,
  FadeIn,
  FadeOut,
  Layout,
  SlideInRight,
  SlideInUp,
  ZoomIn,
} from 'react-native-reanimated';

export const animationTiming = {
  fast: 180,
  normal: 260,
  slow: 320,
};

export const animationEasing = {
  easeOut: Easing.out(Easing.cubic),
  easeIn: Easing.in(Easing.cubic),
  easeInOut: Easing.inOut(Easing.cubic),
};

export const animationSpring = {
  damping: 16,
  mass: 0.85,
  stiffness: 240,
};

export const fadeIn = FadeIn.duration(animationTiming.normal).easing(
  animationEasing.easeOut
);

export const fadeOut = FadeOut.duration(animationTiming.fast).easing(
  animationEasing.easeIn
);

export const slideInRight = SlideInRight.duration(animationTiming.normal).easing(
  animationEasing.easeOut
);

export const slideUp = SlideInUp.duration(animationTiming.normal).easing(
  animationEasing.easeOut
);

export const scaleIn = ZoomIn.duration(animationTiming.normal).easing(
  animationEasing.easeOut
);

export const layoutTransition = Layout.springify()
  .damping(animationSpring.damping)
  .mass(animationSpring.mass)
  .stiffness(animationSpring.stiffness);

export function staggeredFadeScale(index = 0) {
  return FadeIn.delay(Math.min(index * 55, 280))
    .duration(animationTiming.normal)
    .easing(animationEasing.easeOut)
    .withInitialValues({
      opacity: 0,
      transform: [{ scale: 0.97 }, { translateY: 8 }],
    });
}

export function staggeredSlideUp(index = 0) {
  return FadeIn.delay(Math.min(index * 45, 240))
    .duration(animationTiming.normal)
    .easing(animationEasing.easeOut)
    .withInitialValues({
      opacity: 0,
      transform: [{ translateY: 12 }],
    });
}
