<template>
  <v-text-field
    class="label-active"
    v-model="value"
    v-bind="textfieldProps"
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
  data() {
    return {
      value: this.element.value,
      rules: [
        v =>
          !!v ||
          (this.label
            ? `Поле "${this.label}" необходимо заполнить.`
            : "Это поле необходимо заполнить.")
      ]
    };
  },
  computed: {
    isLogin() {
      return this.element.name === "login";
    },
    label() {
      if (!this.element.label) return false;
      let label = this.element.label;
      if (label.indexOf(":")) {
        label = label.split(":");
        label = label.join("");
      }
      return label;
    },
    textfieldProps() {
      let props = {
        name: this.element.name,
        label: this.element.label,
        required: this.element.required
      };
      if (this.isLogin) props["autocomplete"] = "username";
      if (this.isLogin) props["placeholder"] = " ";
      if (this.element.description) props["hint"] = this.element.description;
      if (this.element.required) props["rules"] = this.rules;
      return props;
    }
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
