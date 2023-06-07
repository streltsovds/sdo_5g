<template>
  <div class="hm-form-element hm-multi-checkbox">
    <div
      v-if="label"
      class="elements-title v-label theme--light "
      :class="{ required }"
      v-text="label"
    ></div>
    <p v-if="description" v-text="description"></p>
    <hm-errors :errors="errors"></hm-errors>

    <template v-if="Object.keys(checkboxs).length > 0">
      <div
        v-for="(checkbox, checkboxValue) in checkboxs"
        :key="checkboxValue"
        class="hm-multi-checkbox_item"
      >
        <template
          v-if="
            typeof checkbox === 'object' &&
              checkbox.items &&
              Object.keys(checkbox.items).length > 0">
          <label
            v-if="checkbox.title"
            class="v-label theme--light"
            v-text="checkbox.title"
          ></label>
          <template v-for="(name, value) in checkbox.items">
            <v-checkbox
              v-model="checkedItems"
              :value="value"
              :label="name"
            ></v-checkbox>
          </template>
        </template>
        <template v-else>
          <v-checkbox
            v-model="checkedItems"
            :value="checkboxValue"
            :label="checkbox"
          ></v-checkbox>
        </template>
      </div>
    </template>
    <div class="hm-multi-checkbox_result">
      <input
        v-for="(result, key) in results"
        :key="key"
        type="hidden"
        :name="name"
        :value="result"
      />
    </div>
  </div>
</template>
<script>
import HmErrors from "./../hm-errors";
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmMultiCheckbox",
  components: { HmErrors },
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: Array,
      default: () => []
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
      label: this.attribs.label || null,
      description: this.attribs.description || null,
      required: this.attribs.required || false,
      formId: this.attribs.formId || null,
      results: [],
      checkedItems: [],
      checkboxs: this.attribs.MultiOptions || []
    };
  },
  watch: {
    checkedItems(v) {
      this.updateValue(v);
    },
    value() {
      this.setCheckedItems();
    }
  },
  created() {
    this.setCheckedItems();
  },
  methods: {
    setCheckedItems() {
      this.checkedItems =
        this.value && this.value.length > 0
          ? this.value.map(item => item.toString())
          : [];
    },
    updateValue(value) {
      this.mixinStateUpdate("results", value);
      this.$emit("update", value);
    }
  }
};
</script>
<style lang="scss">
.hm-multi-checkbox {
  .elements-title {
      margin-bottom: 15px;
  }
  .hm-multi-checkbox_item {
    .v-input--checkbox {
      margin-top: 0;
    }
    > label {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E !important;
    }
    .v-input__slot {
      margin-bottom: 0;
    }
  }
}
</style>
