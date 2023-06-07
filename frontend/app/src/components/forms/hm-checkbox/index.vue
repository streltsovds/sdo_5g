<template>
  <div class="hm-form-element hm-checkbox">
    <hm-errors :errors="errors"></hm-errors>
    <v-checkbox
      :input-value="value"
      :label="label"
      :class="{ required }"
      :hint="description"
      persistent-hint
      :disabled="disabled || proctoringDisabled"
      @change="updateValue"

    ></v-checkbox>
    <input type="hidden" :name="name" :value="value ? 1 : 0" />
  </div>
</template>
<script>
import HmErrors from "./../hm-errors";
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmCheckbox",
  components: { HmErrors },
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    checked: {
      type: Boolean,
      default: false
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      value: this.checked,
      label: this.attribs.label || null,
      hideDetails: this.attribs.hideDetails || 'auto',
      description: this.attribs.description || null,
      required: this.attribs.required || false,
      disabled: this.attribs.disabled || false,
      formId: this.attribs.formId || null,
      mixinStateNameValueField: "value",
      proctoringDisabled: false
    };
  },
  watch:{
    checked(){
      setTimeout(()=>{
        this.value = this.checked;
      })
    },
    typeMaterial() {
      if((this.typeMaterial === 'forum' || this.typeMaterial === 'eclass') && this.name === 'has_proctoring') {
        this.proctoringDisabled = true;
        this.value = false
      }
      else this.proctoringDisabled = false
    }
  },
  computed: {
    typeMaterial() {
      return this.$store.getters['proctoring/GET_TYPE_MATERIAL']
    }
  },
  created() {
    this.updateValue(this.checked);
  },
  methods: {
    updateValue(value) {
      this.mixinStateUpdate("value", value);
      this.$emit("change", value);
    }
  }
};
</script>
<style lang="scss">
  .hm-checkbox {
    .v-messages__message {
      margin-left: 11px !important;
    }
    .v-input__slot {
      margin-bottom: 0 !important;
    }
    .v-input--selection-controls__input {
      margin: 8px !important;
    }
  }
</style>
