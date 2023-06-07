<template>
  <div
    class="hm-grid__filter-cell"
    v-if="componentName"
  >
    <component
      :is="componentName"
      ref="childFilterComponent"
      v-bind="filterProps"
      @filter="handleFilterValue"
    />
  </div>
</template>

<script>
import {
  FILTER_EVENT,
  TEXT_FILTER_TYPE,
  TEXT_COMPONENT_NAME,
  SELECT_FILTER_TYPE,
  SELECT_COMPONENT_NAME,
  DATESMART_FILTER_TYPE,
  DATESMART_COMPONENT_NAME,
  DATE_FILTER_TYPE,
  DATE_COMPONENT_NAME,
} from "../constants";
import HmGridFilterText from "./filters/HmGridFilterText";
import HmGridFilterSelect from "./filters/HmGridFilterSelect";
import HmGridFilterDateRange from "./filters/HmGridFilterDateRange";
export default {
  components: {
    HmGridFilterText,
    HmGridFilterSelect,
    HmGridFilterDateRange
  },
  props: {
    filter: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    filterProps() {
      return {
        value: this.filter.value,
        options: this.filter.options,
      }
    },
    componentName() {
      switch (this.filter.type) {
        case TEXT_FILTER_TYPE:
          return TEXT_COMPONENT_NAME;
        case SELECT_FILTER_TYPE:
          return SELECT_COMPONENT_NAME;
        case DATESMART_FILTER_TYPE:
          return DATESMART_COMPONENT_NAME;
        case DATE_FILTER_TYPE:
          return DATE_COMPONENT_NAME;
        default:
          return false;
      }
    }
  },
  methods: {
    focus() {
      this.$refs.childFilterComponent.focus();
    },
    handleFilterValue(value) {
      this.dirtyValue = value;
      this.$emit(FILTER_EVENT, value);
    }
  }
};
</script>
<style lang="scss">
.hm-grid__filter-cell {
  display: flex;
  align-items: flex-end;
  min-height: 64px
}
.hm-grid__filter-cell input,
.hm-grid__filter-cell .v-select__selection,
.hm-grid__filter-cell .v-label {
  font-weight: 400 !important;
}
// сбрасываем стили с иконок которые накинул datatable
.v-datatable thead th.column.sortable .hm-grid__filter-cell .v-icon {
  opacity: 1;
  display: inline-flex;
  font-size: 24px;
  color: rgba(0, 0, 0, 0.54);
  transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
}
</style>
