<template>
  <div v-if="errorsExist" class="hm-errors">
    <v-alert :value="true" type="error" outlined>
      <ul v-if="errors">
        <li v-for="(error, key) in errors" :key="key" v-text="error"></li>
      </ul>
    </v-alert>
  </div>
</template>
<script>
export default {
  props: {
    errors: {
      type: [Object, Array],
      default: () => {}
    }
  },
  computed: {
    errorsExist() {
      for (let key in this.errors) {
        if (this.errors.hasOwnProperty(key)) return true;
      }
      return false;
    }
  }
};
</script>
<style lang="scss">
.hm-errors {
  ul {
    list-style: none;
  }
}
</style>
