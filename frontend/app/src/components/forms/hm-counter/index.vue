<template>
  <div
    class="hm-form-element hm-counter"
    :class="[`hm-form-element_${name}`, className]"
  >
    <v-text-field
      :value="num"
      :name="name"
      :label="label"
      :error-messages="errorsArray"
      :error="errorsExist"
      :hint="description"
      :disabled="true"
      :class="{ required }"
      persistent-hint
    ></v-text-field>
  </div>
</template>
<script>
export default {
  name: "HmCounter",
  props: {
    name: {
      type: String,
      required: true
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    value: {
      type: String,
      default: null
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      label: this.attribs.label || "",
      description: this.attribs.description || "",
      required: this.attribs.required || false,
      errorsData: this.errors,
      className: this.attribs.class,
      num: 0
    };
  },
  computed: {
    selector() {
      if (!this.className) return ".hm-counter";

      let res = "";
      let classArr = this.className.split(" ");
      classArr.forEach(classEl => {
        res += "." + classEl.trim();
      });

      return res;
    },
    errorsExist() {
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key))
          rules.push(this.errorsData[key]);
      }
      return rules;
    }
  },
  mounted() {
    this.num =
      this.value && this.value > 0
        ? this.value
        : document.querySelectorAll(this.selector).length;
  }
};
</script>
