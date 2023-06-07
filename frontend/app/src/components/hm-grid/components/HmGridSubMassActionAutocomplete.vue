<template>
  <hm-autocomplete
    class="hm-grid-sub-mass-action-autocomplete"
    v-bind="hmAutocompleteProps"
    @invalid="onInvalid"
    @selected="onSelected"
    :isTags="typeTags"
  >
  </hm-autocomplete>
</template>

<script>
import {
  AUTOCOMPLETE_PROP,
  DATA_URL_PROP,
  ALLOWED_NEW_PROP,
  MAX_ITEMS_PROP,
  SELECTED_EVENT,
  INVALID_EVENT,
} from "../constants";

import HmAutocomplete from "@/components/controls/hm-autocomplete/index";
import lodashValues from "lodash/values"
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

export default {
  components: {
    HmAutocomplete
  },
  mixins: [VueMixinConfigColors],
  props: {
    action: {
      type: Object,
      default: () => ({})
    },
    itemText: {
      type: String,
      default: "text",
    },
    itemValue: {
      type: String,
      default: "value",
    },
  },
  data() {
    return {
      items: [],
      newItems: [],
      search: null,
      selected: [],
      isLoading: true,
      rules: [this.getMaxLengthRule]
    };
  },
  computed: {
    typeTags() {
      if(this.action.fcbk && this.action.fcbk.tags) return true
      else return false
    },
    autocompleteData() {
      return this.action[AUTOCOMPLETE_PROP][this.itemKey];
    },
    autocompleteUrl() {
      return this.autocompleteData[DATA_URL_PROP];
    },
    allowNewItems() {
      return this.autocompleteData[ALLOWED_NEW_PROP];
    },
    itemKey() {
      return Object.keys(this.action[AUTOCOMPLETE_PROP])[0];
    },
    maxItems() {
      return this.autocompleteData[MAX_ITEMS_PROP];
    },
    color() {
      return this.getColor(configColors.textDark, '#333');
    },
    hmAutocompleteProps() {
      let {allowNewItems, autocompleteUrl, color, maxItems} = this;

      return {
        allowNewItems,
        autocompleteUrl,
        color,
        itemText: 'text',
        itemValue: 'value',
        maxItems,
      };
    },
  },
  methods: {
    onInvalid(invalid) {
      this.$emit(INVALID_EVENT, invalid);
    },
    onSelected(selectedValue) {
      /**
       * передаётся через
       * @see HmGridFooter.handleSelectedSubMassAction() в
       * @see HmGridFooter.selectedSubMassActionValue
       **/
      this.$emit(SELECTED_EVENT, { label: this.itemKey, value: lodashValues(selectedValue) });
    },
    // addNewItem() {
    //   const newItem = {
    //     key: this.search,
    //     value: this.newItemIndex
    //   };
    //
    //   this.selected.push(newItem.value);
    //   this.newItems.push(newItem);
    //   this.search = null;
    // },
    addItem(key, value) {
      const item = {
        key, value
      };

      this.selected.push(item.value);
      this.newItems.push(item);
      this.search = null;
    },
    getMaxLengthRule() {
      const { isUnderMaxLength, maxItems } = this;
      if (isUnderMaxLength) {
        return false;
      } else {
        const endings = ["го", "х", "ти"];
        const ending = decline(maxItems, endings);
        return `Не более ${maxItems}-${ending}`;
      }
    }
  }
};
</script>

<style lang="scss">
.hm-grid-sub-mass-action-autocomplete {
  &__listitem-add-item {
    cursor: pointer;
  }
}
.hm-sub-mass-action__autocomplete-real .v-input__slot {
  margin-top: 18px;
}
.hm-sub-mass-action__autocomplete-real .v-text-field__details {
  margin-bottom: 0 !important;
}
.hm-sub-mass-action__autocomplete-real .v-select__slot input {
  margin-top: 0 !important;
}
.hm-grid__add-new-btn {
  text-transform: unset;
  // color: #535353;
}
</style>
