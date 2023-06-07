<template>
  <div class="hm-test-placeholder__select">
    <v-select
      :value="value"
      :items="items"
      :label="label"
      :multiple="options && options.multiple"
      @change="change"
    />
  </div>
</template>
<script>
export default {
  props: {
    options: {
      type: Object,
      default: () => {}
    },
    label: {
      type: String,
      default: null
    },
    value: {
      type: [String, Array],
      default: null
    },
    variants: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    items() {
      return this.variants.map(variant => variant.value);
    }
  },
  methods: {
    change(value) {
      this.$emit("change", value);
    }
  }
};
</script>
<style lang="scss">
.hm-test-placeholder__select{
  margin: 0 10px;
  display: inline-block;

  & .v-select__selection {
    max-width: 100% !important;
  }

  & .v-select__selections input:only-child {
    min-width: 70px !important;
    width: auto;
  }

  & .v-select__selections input:not(:first-child) {
    display: none;
  }
}
</style>
