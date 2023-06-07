<template>
  <div class="hm-grid-filter-date-range mb-1">
    <hm-date-range-field
      :value="currentValue"
      ref="childInput"
      label="Диапазон"
      name="hm-grid-filter-date-range__picker"
      @input="sendFilterRequest"
    />
  </div>
</template>

<script>
import { FILTER_EVENT } from "../../constants";
import HmDateRangeField from "@/components/forms/hm-date-range-field";

export default {
  components: {
    HmDateRangeField,
  },
  props: {
    /** formatted: "15.01.2019,31.03.2019" */
    value: {
      type: String,
      default: () => ({}),
    },
  },
  data() {
    return {
      dirtyValue: null,
    };
  },
  computed: {
    currentValue() {
      return (this.dirtyValue !== null) ? this.dirtyValue : this.value;
    },
  },
  watch: {
    value() {
      this.dirtyValue = null;
    },
  },
  methods: {
    focus() {
      this.$refs.childInput.focus();
    },
    sendFilterRequest(newValue) {
      this.dirtyValue = newValue;
      this.$emit(FILTER_EVENT, this.dirtyValue);
    },
  },
};
</script>

<style lang="scss">
.hm-grid-filter-date-range {
  .hm-date-range-picker {
    margin-top: -16px;
  }

}
</style>
