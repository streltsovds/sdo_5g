<template>
  <v-text-field
    class="hidden-field"
    type="hidden"
    :value="element.value"
    :name="element.name"
    @change="onChange"
    @input="onChange"
  />
</template>

<script>
export default {
  props: {
    element: {
      type: Object,
      default: () => ({})
    }
  },
  mounted() {
    let obj = {};
    obj[this.element.name] = this.element.value;
    this.$emit("field-input", obj);
  },
  methods: {
    onChange() {
      let obj = {};
      obj[this.element.name] = this.element.value;
      this.$emit("field-input", obj);
    }
  }
};
</script>

<style>
.hidden-field {
  visibility: hidden;
  opacity: 0;
  position: absolute;
}
</style>
