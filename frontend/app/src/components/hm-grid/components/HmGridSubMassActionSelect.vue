<template>
  <component
    :is="componentType"
    v-model="selected"
    class="hm-sub-mass-action-select"
    :class="componentCls"
    dense
    hide-details
    hide-messages
    :items="shownItems"
    :label="selectLabel"
    :multiple="isMultiple"
    :menu-props="{
      contentClass: 'hm-sub-mass-action-select__menu',
      offsetY: true,
    }"
    outline
    single-line
  />
</template>

<script>
import {
  SELECTED_EVENT,
  SELECT_PROP,
  VUETIFY_AUTOCOMPLETE_TYPE,
  VUETIFY_SELECT_TYPE,
  AUTOCOMPLETE_TRESHOLD,
  MULTIPLE_PROP,
  COMPONENT_CLS_AUTOCOMPLETE,
  COMPONENT_CLS_SELECT
} from "../constants";

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
    isMultiple() {
      return this.action[MULTIPLE_PROP];
    },
    componentCls() {
      return this.componentType === VUETIFY_AUTOCOMPLETE_TYPE
        ? COMPONENT_CLS_AUTOCOMPLETE
        : COMPONENT_CLS_SELECT;
    },
    componentType() {
      return this.shownItems.length >= AUTOCOMPLETE_TRESHOLD
        ? VUETIFY_AUTOCOMPLETE_TYPE
        : VUETIFY_SELECT_TYPE;
    },
    itemKey() {
      return Object.keys(this.action[SELECT_PROP])[0];
    },
    selectLabel() {
      return this.items[0].text === 'Выберите курс' ? this.items[0].text : null;
    },
    shownItems() {
      return this.items[0].text === 'Выберите курс' ? [... this.items ].splice(1, this.items.length) :  this.items;
    },
    items() {
      return Object.entries(Object.values(this.action[SELECT_PROP])[0]).map(
        ([value, text]) => ({ value, text })
      ).sort((a, b) => {

        // "0" - Выберите курс
        if (a.value + "" === "0") {
          return -1;
        }
        if (b.value + "" === "0") {
          return 1;
        }
        return this.strcmp(a.text, b.text);
      });
    },
  },
  watch: {
    selected(val) {
      // if (this.isMultiple) {
        this.$nextTick().then(() => {
          this.$emit(SELECTED_EVENT, { label: this.itemKey, value: val });
        });
      // } else {
      //   this.$nextTick().then(() => {
      //     this.$emit(SELECTED_EVENT, { label: this.itemKey, value: [val] });
      //   });
      // }
    },
  },
  methods: {
    strcmp(a, b) {
      a = (a + "").toLowerCase();
      b = (b + "").toLowerCase();
      return (a < b ? -1 : (a > b ? 1 : 0));
    },
  },
};
</script>

<style lang="scss">
.hm-sub-mass-action__autocomplete .v-select__slot input {
  margin-top: 0 !important;
}
.hm-sub-mass-action-select__menu {
  .v-list-item__action:first-child {
    margin-right: 16px;
  }
}
</style>
