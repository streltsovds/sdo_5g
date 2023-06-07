<template>
  <tr
    :id="uid"
    class="hm-grid-header__wrapper"
    :class="{ 'state-filters-visible': filtersVisible }"
  >
    <v-style>
      #{{ uid }}.hm-grid-header__wrapper input::placeholder,
      #{{ uid }}.hm-grid-header__wrapper input::-webkit-input-placeholder,
      #{{ uid }}.hm-grid-header__wrapper .v-select__slot .v-label {
        color: {{ _colorPlaceholders }} !important;
      }
      #{{ uid }}.hm-grid-header__wrapper .hm-grid__header-cell__text {
        color: {{ _colorColumnTitle }} !important;
      }
      /* Подчёркивание в фильтрах */
      #{{ uid }}.hm-grid-header__wrapper .v-text-field > .v-input__control > .v-input__slot:before {
        border-color: {{ _colorFilterUnderline }};
      }

    </v-style>
    <th v-if="selectAll" class="hm-grid-header__toggle-all">
      <hm-grid-toggle-all-checkbox
        v-bind="toggleAllCheckboxProps"
        class="pt-2"
        @toggleAll="handleToggleAllRows"
        @toggleAllCurrent="handleToggleAllCurrentRows"
      />
    </th>

    <template v-for="header in headers">
      <hm-grid-header-cell
        :key="header.value"
        :header="header"
        ref="childrenHeaderCells"
        :grid-module-name="gridModuleName"
        class="pb-4"
      ></hm-grid-header-cell>
    </template>

    <th class="hm-grid__header-menu-icon-spacer"></th>
  </tr>
</template>

<script>
import { DEFAULT_GRID_MODULE_NAME } from "../constants";

import HmGridToggleAllCheckbox from "./HmGridToggleAllCheckbox";
import HmGridHeaderCell from "./HmGridHeaderCell";

import { createNamespacedHelpers } from "vuex";

import * as actions from "../module/actions/actions";
import * as getters from "../module/getters/names";

import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

export default {
  components: {
    HmGridToggleAllCheckbox,
    HmGridHeaderCell,
  },
  mixins: [VueMixinConfigColors],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    },
    selectAll: Boolean,
    headers: {
      type: Array,
      default: () => [],
    },
  },
  watch: {
    filtersVisible(value) {
      if (value) {
        this.$nextTick().then(() => {
          this.$refs.childrenHeaderCells[0].focusFilter();
        });
      }
    },
  },
  computed: {
    toggleAllCheckboxProps() {
      return {
        noCurrentRowsSelected: this[getters.NO_CURRENT_ROWS_SELECTED],
        allCurrentRowsSelected: this[getters.ALL_CURRENT_ROWS_SELECTED],
        notAllCurrentRowsSelected: this[getters.NOT_ALL_CURRENT_ROWS_SELECTED],
        allRowsSelected: this[getters.ALL_ROWS_SELECTED],
      };
    },
    _colorPlaceholders() {
      return this.getColor(configColors.textDefault);
    },
    _colorColumnTitle() {
      return this.getColor(
        configColors.textContrast,
        this.darkTheme ? "#FFF" : "#000"
      );
    },
    _colorFilterUnderline() {
      return this.getColor(
        configColors.textLight,
        this.darkTheme ? "#FFF" : "#000"
      );
    },
  },
  beforeCreate() {
    const namespace = this.$options.propsData.gridModuleName;
    const { mapGetters, mapState } = createNamespacedHelpers(namespace);
    this.$composeComputed(
      mapGetters([
        getters.NOT_ALL_CURRENT_ROWS_SELECTED,
        getters.NO_CURRENT_ROWS_SELECTED,
        getters.ALL_CURRENT_ROWS_SELECTED,
        getters.ALL_ROWS_SELECTED,
      ]),
      mapState({
        filtersVisible: state => state.filtersVisible,
        //   gridTitle: state => Boolean(state.config.title) && state.config.title,
      })
    );
  },
  methods: {
    /**
     * Получить экшн или мутацию уже с неймспейсом
     *
     * @param {String} mutationOrActionName Название мутации или экшена
     */
    handleToggleAllRows(payload) {
      const actionName = this.getNamespacedName(actions.TOGGLE_ALL_ROWS);
      this.$store.dispatch(actionName, payload);
    },
    getNamespacedName(actionName) {
      return `${this.gridModuleName}/${actionName}`;
    },
    handleToggleAllCurrentRows(payload) {
      const actionName = this.getNamespacedName(
        actions.TOGGLE_ALL_CURRENT_ROWS
      );
      this.$store.dispatch(actionName, payload);
    },
  },
};
</script>

<style lang="scss">
.hm-grid-header {
  padding-right: 0 !important;
}
.hm-grid__header-cell {
  padding-left: 0 !important;
  padding-right: 0 !important;
  position: relative;


  .hm-date-picker {
    max-width: 165px;
  }
}
.hm-grid__header-cell__left-border {
  position: absolute;
  width: 1px !important;
  background-color: rgba(0, 0, 0, 0.12);
  top: 0;
  bottom: 0;
  left: 0;
}
.hm-grid__header-cell__text {
  font-size: 1rem;
  font-weight: bold;
}
.hm-grid__header-menu-icon-spacer {
  padding: 0 !important;
}
.hm-grid-header__toggle-all {
  vertical-align: top;
  padding-top: 6px;
  padding-right: 0 !important;
  width: 48px;

  /* TODO Разобраться в причине разного расположения галочек в заголовке и в строках */
  .v-btn {
    margin-top: 3px;
    width: 24px;
  }
}

/**
 * TODO выделить фильтры в отдельный <tr>, чтобы высота фильтров и колонок нормально автоматически расчитывалась
 */
.hm-grid-header__wrapper.state-filters-visible .hm-grid__header-cell__text {
  min-height: 60px;
}

.hm-grid__header-cell.sortable .hm-grid__header-cell__text {
  cursor: pointer;
}
</style>
