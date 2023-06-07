<template>
  <div class="hm-form-element hm-radio-group">
    <label
      v-if="label"
      class="v-label theme--light"
      :class="{ required }"
      v-text="label"
    ></label>
    <p
      v-if="description"
      v-text="description"
      class="hm-radio-group__description"
    ></p>
    <hm-errors :errors="errors"></hm-errors>
    <v-radio-group
      :value="checked"
      class="hm-radio-group_items"
      :mandatory="false"
      :name="name"
      @change="updateChecked"
    >
      <div
        v-for="(option, key) in options"
        :key="key"
        class="hm-radio-group_item"
      >
        <v-radio
          :label="option.label"
          :value="option.value"
          :disabled="option.disabled"
          :hint="option.description"
        />

        <div class="v-messages theme--light">
          <div class="v-messages__message">{{ option.description }}</div>
        </div>
        <transition name="fade">
          <div
            v-if="option.value === checked && dependences[checked]"
            class="hm-radio-group_dependences"
          >
            <hm-dependency :template="dependences[checked]" />
          </div>
        </transition>
      </div>
    </v-radio-group>
  </div>
</template>
<script>
import HmDependency from "./../../helpers/hm-dependency";
import HmErrors from "./../hm-errors";
import MixinState from "./../mixins/MixinState";

export default {
  components: { HmDependency, HmErrors },
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      default: "",
    },
    value: {
      type: [String, Number],
      default: null,
    },
    options: {
      type: Array,
      required: true,
    },
    dependences: {
      type: Object,
      default: () => {},
    },
    attribs: {
      type: Object,
      default: () => {},
    },
    errors: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      checked: this.value,
      label: this.attribs.label || null,
      description: this.attribs.description || null,
      formId: this.attribs.formId || null,
      required: (this.attribs && this.attribs.required) || false,
    };
  },
  created() {
    this.updateChecked(this.value);
  },
  methods: {
    updateChecked(value) {
      this.mixinStateUpdate("checked", value);
    },
  },
};
</script>
<style lang="scss">
.hm-radio-group {
  & .v-messages {
    min-height: 0 !important;
  }
}
.hm-radio-group__description {
  font-size: 12px;
  line-height: 12px;
  color: rgba(0, 0, 0, 0.4);
}
.hm-radio-group_item {
  & .v-messages {
    min-height: 0px !important;
  }
  .v-input--selection-controls__input {
    margin: 8px !important;
  }
}
.hm-radio-group_dependences {
  margin-left: 35px;
}
</style>
