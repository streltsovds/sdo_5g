<template>
  <span class="hm-modal-activator">
    <span class="hm-modal-activator__activator" @click="isShown = true">
      <slot></slot>
    </span>

    <hm-modal-html
      :is-shown="isShown"
      @close="isShown = false"
      v-bind="$props"
    >
    </hm-modal-html>
  </span>
</template>

<script>
// import HmModal from "./index"
import HmModalHtml from "./html"

export default {
  name: "HmModalActivator",
  components: {
    HmModalHtml
  },
  // такие же как у hm-modal-html
  props: {
    content: {
      type: String,
      default: null,
    },
    contentUrl: {
      type: String,
      default: null,
    },
    printUrl: {
      type: String,
      default: null,
    },
    title: {
      type: String,
      default: null,
    },
  },
  created() {
  },
  data() {
    return {
      isShown: false,
    };
  },
  computed: {},
  methods: {
    print() {
      window.open(this.printUrl);
    },
  },
};
</script>

<style lang="scss">
</style>
