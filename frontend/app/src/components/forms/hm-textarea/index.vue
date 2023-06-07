<template>
  <div class="hm-form-element hm-textarea">
    <v-textarea
      :value="text"
      :name="name"
      :label="label"
      :hint="description"
      :error="errorsExist"
      :class="{ required }"
      :error-message="errorsArray"
      persistent-hint
      @change="update"
      outlined
    >
      <template slot="message">
        <span v-html="description"></span>
      </template>

    </v-textarea>
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmTextarea",
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: String,
      default: null
    },
    attribs: {
      type: Object,
      required: true
    },
    errors: {
      type: [Object, Array],
      default: () => {}
    }
  },
  data() {
    return {
      label: this.attribs.label || "",
      description: this.attribs.description || "",
      required: this.attribs.required || false,
      errorsData: this.errors,
      formId: this.attribs.formId || null,
      text: this.value || ""
    };
  },
  computed: {
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
  created() {
    this.updateText(this.value);
  },
  methods: {
    clearErrors() {
      this.errorsData = null;
    },
    update(value) {
      this.updateText(value);
      this.clearErrors();
    },
    updateText(value) {
      this.mixinStateUpdate("text", value);
    }
  }
};
</script>
