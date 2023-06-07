<template>
  <v-text-field
    class="hm-grid__filter-text mb-1"
    ref="childInput"
    @input="handleInput"
    :value="currentValue"
    :placeholder="inputPlaceHolder"
    single-line
    hide-details
    clearable
  >
  </v-text-field>
</template>

<script>
import { FILTER_EVENT } from "../../constants";

export default {
  props: {
    value: {
      type: [Object, String],
      default: null
    }
  },
  data() {
    return {
      dirtyValue: null
    };
  },
  computed: {
    inputPlaceHolder() {
      return "Поиск...";
    },
    currentValue() {
      return this.value || this.dirtyValue;
    },
  },
  watch: {
    // input: {
    //   handler(val) {
    //     this.sendFilterRequest(val);
    //   }
    // },
    value() {
      this.dirtyValue = null;
    }
  },
  methods: {
    focus() {
      this.$refs.childInput.focus();
    },
    handleInput(val) {
      this.dirtyValue = val;
      this.sendFilterRequest(val);
    },
    sendFilterRequest(val) {
      // this.$emit(FILTER_EVENT, [val]);
      this.$emit(FILTER_EVENT, val);
    }
  }
};
</script>

<style lang="scss">
.hm-grid__filter-text {
  padding: 0;
  margin: 0;
  min-width: 100px;
  max-width: 150px;
  font-size: 12px;
  .v-input__slot {
    /*box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
      0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);*/
    border-width: 1px !important;
    min-height: 0 !important;
    margin: 0;
    .v-input__append-inner {
      margin-top: 6px !important;
    }
    input {
      margin: 0 !important;
      max-height: 100% !important;
      padding: 6px 0;
    }
  }
}
</style>
