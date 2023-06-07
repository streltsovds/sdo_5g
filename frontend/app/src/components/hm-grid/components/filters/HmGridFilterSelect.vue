<template>
  <v-select
    class="hm-grid__filter-select mb-1"
    ref="childFilter"
    :value="currentValue"
    v-bind="selectProps"
    :label="_('Фильтр')"
    :menu-props="{ offsetY: true }"
    @input="handleInput"
  />
</template>

<script>
import { FILTER_EVENT } from "../../constants";

export default {
  props: {
    value: {
      type: [String, Number],
      default: null
    },
    options: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      dirtyValue: null,
    };
  },
  computed: {
    selectProps() {
      return {
        singleLine: true,
        dense: true,
        itemText: "value",
        itemValue: "key",
        items: this.options,
        clearable: true,
        hideDetails: true,
        outline: true
      };
    },
    currentValue() {
      return this.value || this.dirtyValue;
    },
  },
  watch: {
    value() {
      this.dirtyValue = null;
    }
  },
  methods: {
    focus() {
      this.$refs.childInput.focus();
    },
    handleInput(newValue) {
      this.dirtyValue = newValue;

      if (newValue === undefined) {
        this.$emit(FILTER_EVENT, -1);
      } else {
        this.$emit(FILTER_EVENT, newValue);
      }
    },
  },
};
</script>

<style lang="scss">
.hm-grid__filter-select {
  padding: 0;
  margin: 0;
  min-width: 120px;
  max-width: 150px;

  /* TODO что это за иконка? */
  .v-input__icon-- {
    display: none;
  }

  /* заполненный фильтр */
  &.v-input--is-dirty {
    .v-select__selections {
      max-width: 118px;
      .v-select__selection {
        display: inline;
        text-overflow: ellipsis;
        overflow: hidden;
      }
    }
    /* скрыть иконку выбора для заполненного фильтра */
    .v-input__icon--append {
      display: none;
    }
  }

  &.v-input--is-dirty .v-select__selection + input {
    display: none;
  }
  & .v-input__slot {
    /*box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
      0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);*/
    border-width: 1px !important;
    min-height: 0 !important;
    margin: 0;
    height: 32px;
    & .v-input__append-inner {
      margin-top: 6px !important;

      /* чтобы можно было убрать отступ скрытой иконки */
      padding-left: 0;
      margin-left: 0;
      .v-input__icon {
        padding-left: 4px;
        margin-left: 4px;
      }
    }
    & input {
      margin: 0 !important;
      max-height: 100% !important;
      /*padding: 8px 0 8px;*/
    }
  }

  .v-select__slot {
    .v-label {
      font-size: 12px;
    }
  }
  .v-select__selection {
    font-size: 12px;
  }
}
</style>
