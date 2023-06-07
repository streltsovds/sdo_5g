<template>
  <v-text-field v-model="selected" v-bind="textfieldProps" class="grid-mass-textfield"> </v-text-field>
</template>

<script>
import { SELECTED_EVENT, INPUT_PROP } from "../constants";

export default {
  props: {
    action: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      selected: null
    };
  },
  computed: {
    textfieldProps() {
      return {
        outline: true,
        singleLine: true,
        hideDetails: true,
        clearable: true,
        class: "hm-sub-mass-action__input",
        placeholder: Object.values(this.action[INPUT_PROP])[0].title,
      };
    },
    itemKey() {
      return Object.keys(this.action[INPUT_PROP])[0];
    }
  },
  watch: {
    selected(val) {
      this.$emit(SELECTED_EVENT, { label: this.itemKey, value: val });
    }
  }
};
</script>

<style lang="scss">
.hm-sub-mass-action__input .v-select__slot input {
  margin-top: 0 !important;
}
.grid-mass-textfield {
  padding: 0;

  input {
    min-width: 206px !important;
  }
}
</style>
