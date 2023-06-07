<template>
  <v-btn class="hm-print-btn white--text" :color="color" @click="openPrintWindow">{{
    text
  }}</v-btn>
</template>
<script>
export default {
  props: {
    text: {
      type: String,
      default: "Печать"
    },
    url: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    },
    color: {
      type: String,
      required: false,
      default: "#FF9800"
    },
  },
  data() {
    return {
      options: [
        "location=no",
        "menubar=yes",
        "status=no",
        "resizable=yes",
        "scrollbars=yes",
        "directories=no",
        "toolbar=no"
      ].join(",")
    };
  },
  methods: {
    openPrintWindow() {
      window.open(this.url, this.name, this.options);
    }
  }
};
</script>
