<template>
  <th
    v-if="header"
    :class="classes"
  >
    <div style="display: flex; flex-direction: column; height: 100%; justify-content: space-between;">
      <div
        class=" hm-grid__header-cell__text pb-2 pt-2"
        style="
          padding: 0 20px;
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          display: flex;
          flex-wrap: wrap;
        "
        @click="changeSort(header.value, header.sortable)"
      >
        <span
          v-if="headerTextWithoutLastWord"
          style="white-space: nowrap; margin-right: 5px;"
        >
          {{ headerTextWithoutLastWord }}
        </span>
        <span style="white-space: nowrap">
          {{ headerTextLastWord }}
          <svg-icon
            v-if="header.sortable"
            class="hm-grid-header__sort-icon"
            :color="colorIcon"
            :name="iconName"
            style="display: inline; margin-left: 4px; vertical-align: text-top;"
            :width="14"
            :height="14"
          >
          </svg-icon>
        </span>
      </div>
      <v-expand-transition>

<!--        class="pt-5"-->
        <hm-grid-header-cell-filter
          v-if="filterIsRendered"
          v-show="filterIsVisible"
          ref="childCellFilter"
          :filter="header.filters"
          style="transition: 0.5s ease-in-out; padding: 16px 20px 0  20px;"
          :style="{
            opacity: (filterIsVisible ? 1 : 0),
          }"
          @filter="handleFilterRequest($event)"
        />
      </v-expand-transition>
    </div>
  </th>
  <th v-else>???</th>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

import HmGridHeaderCellFilter from "./HmGridHeaderCellFilter";
import svgIcon from "../../icons/svgIcon";
import {READY_FILTERS, DEFAULT_GRID_MODULE_NAME, SELECTED_EVENT} from "../constants";
import * as actions from "../module/actions/actions";
import { createNamespacedHelpers } from "vuex";
import cloneDeep from "lodash/cloneDeep";

export default {
  name: "HmGridHeaderCell",
  components: {
    HmGridHeaderCellFilter,
    svgIcon,
  },
  mixins: [VueMixinConfigColors],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME,
    },
    header: {
      type: Object,
      default: null,
    },
  },
  computed: {
    classes() {
      let header = this.header;

      let result = ["hm-grid__header-cell", "column"];

      if (header.sortable) {
        result.push("sortable");
      }

      result.push(this.sortDescending ? "sort-desc" : "sort-asc");

      if (header.align) {
        result.push(`text-xs-${header.align}`);
      }

      if (this.sortActive) {
        result.push("sort-active");
      }

      if (this.sortLoading) {
        result.push("sort-loading");
      }

      if (this.filterIsVisible) {
        result.push("state-filter-visible");
      }

      return result;
    },
    filterIsRendered() {
      return this.header.filters && this.isFilterAllowed(this.header.filters.type)
    },
    filterIsVisible() {
      return this.filterIsRendered && this._filtersVisible;
    },
    /** нужно для объедиения последнего слова заголовка со стрелочкой сортировки в один элемент, чтобы она не переносилась на другую строку */
    headerTextSplitLastWord() {
      let s = this.header.text;
      let words = s.split(" ");
      let lastWord = words.pop();
      return [words.join(" "), lastWord];
    },
    headerTextLastWord() {
      return this.headerTextSplitLastWord[1];
    },
    headerTextWithoutLastWord() {
      return this.headerTextSplitLastWord[0];
    },
    columnName() {
      return this.header.value;
    },
    // by this column
    sortActive() {
      return this.columnName === this.pagination.sortBy;
    },
    iconName() {
      return this.sortDescending ? "sort-descending" : "sort-ascending";
    },
    colorIcon() {
      return this.getColor(
        this.sortActive
          ? (this.sortLoading ? configColors.primarySaturated : configColors.textContrast)
          : configColors.grayLight
      );
    },
    // by this column
    sortDescending() {
      return this.sortActive && this.pagination.descending;
    },
    pagination() {
      return this._paginationDirty || this._pagination;
    },
    sortLoading() {
      return this._paginationDirty && this.sortActive;
    },
  },
  beforeCreate() {
    const namespace = this.$options.propsData.gridModuleName;
    const { mapState } = createNamespacedHelpers(namespace);
    this.$composeComputed(
      mapState({
        _pagination: state => state.pagination,
        _paginationDirty: state => state.paginationDirty,
        _filtersVisible: state => state.filtersVisible,
      })
    );
  },
  methods: {
    focusFilter() {
      this.$nextTick().then(() => {
        this.$refs.childCellFilter.focus();
      });
    },
    /**
     * Получить экшн или мутацию уже с неймспейсом
     *
     * @param {String} mutationOrActionName Название мутации или экшена
     */
    getNamespacedName(mutationOrActionName) {
      return `${this.gridModuleName}/${mutationOrActionName}`;
    },
    isFilterAllowed(type) {
      return READY_FILTERS.includes(type);
    },
    changeSort() {
      let header = this.header;

      if (!header.sortable) {
        console.log("Not sortable");
        return;
      }

      let newPagination = cloneDeep(this.pagination);

      if (this.sortActive) {
        newPagination.descending = !newPagination.descending;
      } else {
        newPagination.sortBy = this.columnName;
        newPagination.page = 1;
        newPagination.descending = false;
      }
      this.$store.dispatch(this.getNamespacedName(actions.SET_PAGINATION), newPagination);
    },
    handleFilterRequest(value) {
      const filter = {
        column: this.columnName,
        value,
      };

      // /quest/list/tests

      let actionName =
        // value && value !== -1 && value.toString()
        /** Добавлена поддержка 0 для /quest/list/tests, "Статус ресурса БЗ: Не опубликован" */
        value !== null && typeof value !== 'undefined' && value !== -1 && value.toString()
          ? actions.APPLY_FILTER
          : actions.REMOVE_FILTER;

      this.$store.dispatch(this.getNamespacedName(actionName), filter);
    },
  },
};
</script>

<style lang="scss">
.hm-grid__header-cell {
  transition: all 0.3s;
  min-width: 0;
  vertical-align: top;
  height: min-content !important;

  &.state-filter-visible {
    /* для анимации расширения колонки. TODO почти не оказывает эффекта */
    min-width: 120px;
  }
}
</style>
