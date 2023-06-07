<template>
  <v-alert v-bind="alertProps">
    <span> </span>
  </v-alert>
</template>

<script>
export default {
  props: {
    search: {
      type: String,
      default: () => false
    }
  },
  computed: {
    alertProps() {
      return {
        value: true
      };
    }
  }
};
</script>

<style></style>
