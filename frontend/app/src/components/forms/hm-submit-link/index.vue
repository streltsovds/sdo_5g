<template>
  <v-flex
    xs12
    sm4
    class="hm-form-element hm-submit-link"
    :class="`hm-form-element_${name}`"
  >
    <a class="v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--large primary" :href="url" v-text="label"></a>
  </v-flex>
</template>
<script>
export default {
  name: "HmSubmitLink",
  props: {
    id: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    url: {
      type: String,
      required: true
    }
  }
};
</script>
<style lang="scss">
.hm-submit-link {
  display: inline-grid;
  /*margin-top: 2rem;*/
  min-width: 265px;
  button {
    margin: 0;
  }
}
</style>
