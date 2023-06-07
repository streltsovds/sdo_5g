<template>
  <component
    :is="subMassActionType"
    :action="action"
    @invalid="handleInvalid"
    class="hm-grid-sub-mass-action"
    @selected="handleSelect"
  />
</template>

<script>
import {
  SELECTED_EVENT,
  SUB_MASS_ACTION_SELECT_TYPE,
  SUB_MASS_ACTION_AUTOCOMPLETE_TYPE,
  SUB_MASS_ACTION_INPUT_TYPE,
  INVALID_EVENT,
  hmGridComponentName
} from "../constants";

import HmGridSubMassActionSelect from "./HmGridSubMassActionSelect";
import HmGridSubMassActionAutocomplete from "./HmGridSubMassActionAutocomplete";
import HmGridSubMassActionInput from "./HmGridSubMassActionInput";

export default {
  components: {
    HmGridSubMassActionSelect,
    HmGridSubMassActionInput,
    HmGridSubMassActionAutocomplete
  },
  props: {
    action: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    subMassActionType() {
      if (this.hasAction(SUB_MASS_ACTION_SELECT_TYPE)) {
        return hmGridComponentName.SELECT;
      }
      if (this.hasAction(SUB_MASS_ACTION_AUTOCOMPLETE_TYPE)) {
        return hmGridComponentName.AUTOCOMPLETE;
      }
      if (this.hasAction(SUB_MASS_ACTION_INPUT_TYPE)) {
        return hmGridComponentName.INPUT;
      }
      return hmGridComponentName.DEFAULT;
    }
  },
  methods: {
    hasAction(actionType) {
      return this.action[actionType] !== undefined;
    },
    handleSelect(value) {
      this.$nextTick().then(() => {
        this.$emit(SELECTED_EVENT, value);
      });
    },
    handleInvalid(isValid) {
      this.$emit(INVALID_EVENT, isValid);
    }
  }
};
</script>

<style lang="scss">
.hm-grid-sub-mass-action {
  font-size: 14px;
}
</style>
