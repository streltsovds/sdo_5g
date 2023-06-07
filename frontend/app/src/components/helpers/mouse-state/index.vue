<template>
  <span
      class="mouse-state"
      ref="mouseStateEl"
      @mouseover="onMouseOver"
      @mousemove="onMouseOver"
      @mouseleave="onMouseLeave"
      @mousedown="onMouseDown"
      @mouseup="onMouseUp"
      @touchstart="onTouchStart"
      @touchend="onTouchEnd"
      @touchmove="onTouchMove"
  >
    <slot v-bind:mouseState="actualMouseState" />
  </span>
</template>

<script>
import Vue from 'vue';
// import mouseLeftButtonPressed from "@/utilities/mouseLeftButtonPressed";
import touchInside from "@/utilities/touchInside";

export default Vue.extend({
  name: 'MouseState',
  props: {
    debugMode: {
      type: null,
      default: false,
    },
    detectTouchInside: {
      type: null,
      default: true,
    }
  },
  data() {
    return  {
      logPrefix: 'mouse-state:',
      mouseState: {
        pressedStart: false,
        hover: false,
      }
    };
  },
  computed: {
    actualMouseState() {
      let ms = this.mouseState;

      return {
        ...ms,
        pressed: ms.pressedStart && ms.hover,
      };
    }
  },
  methods: {
    debugLog() {
      if (this.debugMode) {
        console.log(this.logPrefix, ...arguments)
      }
    },
    onMouseOver(e) {
      this.debugLog('onMouseOver');
      this.mouseState.hover = true;
      // this.mouseState.pressed = mouseLeftButtonPressed();
    },
    onMouseLeave(e) {
      this.debugLog('onMouseLeave');
      this.mouseState.hover = false;
      // this.mouseState.pressed = false;
    },
    onMouseDown(e) {
      this.debugLog('onMouseDown');
      this.mouseState.pressedStart = true;
      this.mouseState.hover = true;
    },
    onMouseUp(e) {
      this.debugLog('onMouseUp');
      this.mouseState.pressedStart = false;

      /** событие mouseUp срабатывает после touchEnd, поэтому приходится отключать hover */
      this.mouseState.hover = false;
    },
    onTouchEnd(e) {
      this.debugLog('onTouchEnd');
      this.mouseState.pressedStart = false;
      this.mouseState.hover = false;
    },
    onTouchStart(e) {
      this.debugLog('onTouchStart');
      this.mouseState.pressedStart = true;
      this.mouseState.hover = true;
    },
    onTouchMove(evt) {
      this.debugLog('onTouchMove');

      if (this.detectTouchInside) {
        let el = this.$refs.mouseStateEl;
        let inside = touchInside(el, evt);
        this.mouseState.hover = inside;
      }
    },
  },
});
</script>

<style lang="sass" src="./styles.sass" />
