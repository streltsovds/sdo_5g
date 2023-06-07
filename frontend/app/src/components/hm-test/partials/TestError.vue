<template>
  <v-alert
    v-model="isOpen"
    dismissible
    transition="slide-y-transition"
    type="error"
  >
    <span class="body-2">{{ errorText }}</span>
    <v-btn outlined dark round small @click="isDetailed = !isDetailed">
      Подробнее
    </v-btn>
    <div v-if="isDetailed">
      <pre>{{ errorStack }}</pre>
    </div>
  </v-alert>
</template>

<script>
export default {
  props: ["error"], // eslint-disable-line
  data: () => ({
    isOpen: true,
    isDetailed: false
  }),
  computed: {
    errorText() {
      const { message } = this.error;
      return message;
    },
    errorStack() {
      const { stack } = this.error;
      return stack;
    }
  },
  watch: {
    isOpen(value) {
      if (value === false) {
        this.$emit(`close`);
      }
    }
  }
};
</script>

<style></style>
