<template>
  <div>
    <v-checkbox
      v-model="value"
      :label="element.label"
      @change="onChange"
      @input="onChange"
    />
  </div>
</template>

<script>
export default {
  props: {
    element: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      value: this.element.value
    };
  },
  mounted() {
    let obj = {};
    obj[this.element.name] = this.value;
    this.$emit("field-input", obj);
  },
  methods: {
    onChange() {
      let obj = {};
      obj[this.element.name] = this.value;
      this.$emit("field-input", obj);
    }
  }
};
</script>

<style></style>
