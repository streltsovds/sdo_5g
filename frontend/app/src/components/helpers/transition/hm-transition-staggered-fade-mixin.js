import Velocity from "velocity-animate";

export default {
  methods: {
    transitionStaggeredFadeEnter(el, done) {
      let delay = el.dataset.index * 150;
      setTimeout(() => {
        Velocity(el, "reverse", { complete: done });
      }, delay);
    },
    transitionStaggeredFadeLeave(el, done) {
      let delay = el.dataset.index * 150;
      setTimeout(() => {
        Velocity(el, { opacity: 0, height: 0 }, { complete: done });
      }, delay);
    }
  }
};
