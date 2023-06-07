<template>
  <transition-group
    name="staggered-fade"
    :css="false"
    @before-enter="beforeEnter"
    @enter="enter"
    @leave="leave"
  >
    <slot></slot>
  </transition-group>
</template>

<script>
import Velocity from "velocity-animate";

export default {
  methods: {
    beforeEnter(el) {
      // el.style.opacity = 0;
      // el.style.height = 0;
    },
    enter(el, done) {
      let delay = el.dataset.index * 150;
      setTimeout(() => {
        Velocity(el, "reverse", { complete: done });
      }, delay);
    },
    leave(el, done) {
      let delay = el.dataset.index * 150;
      setTimeout(() => {
        Velocity(el, { opacity: 0, height: 0 }, { complete: done });
      }, delay);
    }
  }
};
</script>
