<template>
  <div class="demo-ts-vue-extend">
    <div class="demo-ts-vue-extend__greeting">Hello, {{name}}{{exclamationMarks}}</div>
    enthusiasm:
    <button @click="decrement">-</button>
    <button @click="increment">+</button>
  </div>
</template>

<script lang="ts">
import Vue from "vue";

/**
 * based on
 * @see https://github.com/microsoft/TypeScript-Vue-Starter/blob/master/src/components/Hello.vue
 */
export default Vue.extend({
  name: "DemoTsVueExtend",
  props: {
    name: {
      type: String,
      default: 'user',
    },
    initialEnthusiasm: {
      type: Number,
      default: 0,
    }
  },
  data(): {
    enthusiasm: number,
  } {
    return {
      enthusiasm: this.initialEnthusiasm,
    }
  },
  computed: {
    exclamationMarks(): string {
      if (this.enthusiasm <= 1) {
        return '.';
      }
      return Array(this.enthusiasm).join('!');
    }
  },
  methods: {
    increment() {
      this.enthusiasm++;
    },
    decrement() {
      if (this.enthusiasm > 1) {
        this.enthusiasm--;
      }
    },
  },
});
</script>

<style lang="sass">
.demo-ts-vue-extend
  &__greeting
    font-size: 20px

  button
    border: 1px solid black
    min-width: 50px
    margin-left: 8px
</style>
