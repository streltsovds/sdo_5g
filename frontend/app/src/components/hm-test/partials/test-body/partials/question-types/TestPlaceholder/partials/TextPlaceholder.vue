<template>
  <div class="hm-test-placeholder__text">
    <v-text-field class="p-0"
                  v-model="value"
                  :label="label"
                  @change="change"
                  :style="{width: `${value ? value.length*11 : 100}px`}"
                  hide-details="true"
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
      type: String,
      default: null
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
.hm-test-placeholder__text{
  display: inline-block;
  margin: 0 10px;
  .v-input{
    margin-top: 0;
    padding: 0;
  }
  .v-text-field input{
    text-align: center;
    line-height: 14px;
  }
}

</style>
