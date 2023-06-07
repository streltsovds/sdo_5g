<template>
  <div class="demo-mouse-state__wrapper">
    <mouse-state :debug-mode="true">
        <span
            class="demo-mouse-state"
            :class="{
            'demo-mouse-state--hover': mouseState.hover,
            'demo-mouse-state--pressed-start': mouseState.pressedStart,
            'demo-mouse-state--pressed': mouseState.pressed,
          }"
            slot-scope="{ mouseState }"
        >
          MouseState:
          <pre>{{ json(mouseState) }}</pre>
        </span>
    </mouse-state>
  </div>
</template>

<script>
import Vue from 'vue';
import json from '../../../../lib/json';
import MouseState from '../index'

export default Vue.extend({
  name: 'DemoMouseState',
  components: {
    MouseState,
  },
  methods: {
    json
  }
});
</script>

<style lang="sass" src="./styles.sass" />
