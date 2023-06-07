<template>
  <div class="hm-form-element hm-text" :class="`hm-form-element_${name} ${htmlClass}`">
    <v-text-field
      :value=this.value
      :name="name"
      :label="label"
      :error-messages="errorsArray"
      :error="errorsExist"
      :hint="description"
      :disabled="disabled"
      :class="{ required }"
      :append-icon="appendIcon"
      :type="type"
      persistent-hint
      @input="update"
      @blur="$emit('blur')"
    >
      <!-- <template v-slot:message="{hint, key}">
        <span v-html="description"></span>
      </template> -->
    </v-text-field>
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";
import {mapState} from 'vuex';

export default {
  name: "HmText",
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
      default: () => {}
    },
    errors: {
      type: [Object, Array],
      default: () => {}
    }
  },
  data() {
    return {
      label: (this.attribs && this.attribs.label) || "",
      description: (this.attribs && this.attribs.description) || "",
      required: (this.attribs && this.attribs.required) || false,
      disabled: (this.attribs && this.attribs.disabled) || false,
      errorsData: this.errors,
      text: this.value || "",
      formId: (this.attribs && this.attribs.formId) || null,
      appendIcon: (this.attribs && this.attribs.appendIcon) || null,
      type: (this.attribs && this.attribs.type) || "text",
      htmlClass: (this.attribs && this.attribs.class) || ""
    };
  },
  computed: {
    scaleId() {
      return this.$store.getters['subjects/GET_SCALE_ID'];
    },
    autoMark() {
      return this.$store.getters['subjects/GET_AUTO_MARK'];
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
  watch: {
    value(v) {
      this.text = v;
    },
    scaleId(v) {
      if(this.name === 'threshold' && !this.attribs.disabled) {
        if(v === '1' || this.autoMark === false) this.disabled = true
        else this.disabled = false
      }
    },
    autoMark(v) {
      if(this.name === 'threshold' && !this.attribs.disabled) {
        if(v === true && this.scaleId !== '1') this.disabled = false
        else this.disabled = true
      }
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
      this.$emit("update", value);
    }
  }
};
</script>
<style lang="scss">
.hm-text {
  .v-input {
    transition: all 0.3s;
  }
  .v-input--is-label-active,
  .v-input--is-focused {
    margin-top: 16px;
  }
}
</style>
