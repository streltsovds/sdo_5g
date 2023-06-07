<template>
  <v-btn class="hm-download-btn" color="primary" @click="download">{{
    text
  }}</v-btn>
</template>
<script>
export default {
  props: {
    text: {
      type: String,
      default: "Скачать"
    },
    url: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    },
    options: {
      type: String,
      default: [
        "location=no",
        "menubar=yes",
        "status=no",
        "resizable=yes",
        "scrollbars=yes",
        "directories=no",
        "toolbar=no"
      ].join(",")
    }
  },
  methods: {
    download() {
      window.open(this.url, this.name, this.options);
    }
  }
};
</script>
