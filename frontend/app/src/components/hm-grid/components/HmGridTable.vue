<template>
  <component
    :is="gridTitle ? `v-card-text` : `v-card`"
    :class="{
      'pa-0': isOnSmallScreen
    }"
  >
    <hm-grid-header-filter-tabs :grid-module-name="gridModuleName" >
      <template v-slot:header-actions-before>
        <slot name="header-actions-before" />
      </template>
    </hm-grid-header-filter-tabs>

<!--    :pagination="pagination"-->
    <v-data-table
      ref="tableRef"
      v-model="selectedRows"
      class="hm-grid__table"
      mobile-breakpoint="200"
      disable-pagination
      hide-default-header
      hide-default-footer
      v-bind="tableProps"
    >
      <template slot="header" slot-scope="{ on, props }">

        <!-- <hm-grid-header-filter-tabs :grid-module-name="gridModuleName" >
          <template v-slot:header-actions-before>
            <slot name="header-actions-before" />
          </template>
        </hm-grid-header-filter-tabs> -->

        <hm-grid-header
          :select-all="selectAll"
          :grid-module-name="gridModuleName"
          v-bind="props"
          v-on="on"
        />
      </template>
      <hm-grid-no-data slot="no-data" />
      <template slot="item" slot-scope="props">
        <hm-grid-row
          v-bind="{
            selected: getIsRowSelected(props.item),
            isSelectable: selectAll,
            rowProps: props,
            tableWidth,
            headers: headersOrdered
          }"
          @select="handleRowSelect($event, props.item)"
        >
          <hm-grid-actions-menu :actions="props.item.actions" />
        </hm-grid-row>
      </template>
    </v-data-table>
    <v-card-actions class="pa-0">
      <hm-grid-footer
        :select-all="selectAll"
        :width="tableWidth"
        :grid-module-name="gridModuleName"
      />
    </v-card-actions>
  </component>
</template>

<script>
import HmGridNoData from "./HmGridNoData";
import HmGridRow from "./HmGridRow";
import HmGridActionsMenu from "./HmGridActionsMenu";
import HmGridFooter from "./HmGridFooter";
import HmGridHeader from "./HmGridHeader";
import HmGridHeaderActions from "./HmGridHeaderActions";

import VueMixinStoreGridGenerator from "@/components/hm-grid/mixins/VueMixinStoreGridGenerator";

const debounce = require("lodash.debounce");

// import { createNamespacedHelpers } from "vuex";
import * as getter from "../module/getters/names";
import * as actions from "../module/actions/actions";

import {
  // NO_ELEVATION,
  // LOW_ELEVATION,
  // MID_ELEVATION,
  TABLE_WRAPPER_CLS,
  TABLE_BODY_SELECTOR,
  TABLE_BODY_LOADING_CLS,
  MIN_TABLE_WIDTH,
  RESIZE_DOM_EVENT,
  DEFAULT_GRID_MODULE_NAME
} from "../constants";

export default {
  components: {
    HmGridNoData,
    HmGridFooter,
    HmGridHeader,
    HmGridRow,
    HmGridActionsMenu,
    HmGridHeaderFilterTabs: HmGridHeaderActions,
  },
  mixins: [
    VueMixinStoreGridGenerator({
      moduleNameProperty: "gridModuleName",

      mapStateToComputed: {
        items: state => state.items,
        gridTitle: state => Boolean(state.config.title) && state.config.title,
        selectedRows: state => state.selectedRows,
        defaultMassActionsColumn: state => state.defaultMassActionsColumn,
        pagination: state => state.pagination,
        backendRowsPerPageItems: state => state.rowsPerPageItems
      },

      mapGettersToComputed: [
        getter.HEADERS_ORDERED,
        getter.HIDE_ACTIONS,
        getter.HEADERS_HIDE,
        getter.HEADERS_SHOWN,
        getter.LOADING,
        getter.ITEM_KEY,
        getter.IS_ALL_SELECTABLE,
        getter.ACTION_FIELD
      ],
    }),
  ],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    }
  },
  data() {
    return {
      tableWidth: MIN_TABLE_WIDTH,
      isFirstPaginationChange: true
    };
  },
  computed: {
    selectedRows: {
      get() {
        return this.$store.state[this.gridModuleName].selectedRows;
      },
      set(val) {
        if (this.isFirstPaginationChange) {
          return;
        } else {
          this.$storeGrid_dispatch(actions.UPDATE_SELECTED_ROWS, val);
        }
      }
    },
    paginationProps() {
      return {
        gridModuleName: this.gridModuleName
      };
    },
    className() {
      // if (this.gridTitle)
      //   return this.isOnSmallScreen ? NO_ELEVATION : LOW_ELEVATION;
      // return MID_ELEVATION;
      return '';
    },
    tableProps() {
      return {
        hideActions: true,
        headersHide: this[getter.HEADERS_HIDE],
        class: this.className,
        // loading: this[getter.LOADING],
        items: this.items,
        itemKey: this[getter.ITEM_KEY],
        headers: this[getter.HEADERS_SHOWN],
        rowsPerPageItems: this.rowsPerPageItems,
        totalItems: this.pagination.totalItems,
        selectAll: this.selectAll
      };
    },
    selectAll() {
      return this[getter.IS_ALL_SELECTABLE];
    },
    isOnSmallScreen() {
      return this.$vuetify.breakpoint.smAndDown;
    },

    tableRef() {
      return this.$refs.tableRef;
    },
    vTableOverflowEl() {
      return this.tableRef.$el.querySelector(`.${TABLE_WRAPPER_CLS}`);
    },
    vTableBodyEl() {
      return this.tableRef.$el.querySelector(TABLE_BODY_SELECTOR);
    },
    rowsPerPageItems() {
      const rowsPerPageItems = this.backendRowsPerPageItems.slice();
      if (rowsPerPageItems.length === 1) {
        return rowsPerPageItems;
      }
      const { totalItems } = this.pagination;
      const max = rowsPerPageItems.pop();
      const showAll = {
        text: "$vuetify.dataIterator.rowsPerPageAll",
        value: totalItems
      };
      if (max === totalItems) {
        rowsPerPageItems.push(showAll);
      } else {
        rowsPerPageItems.push(max);
        if (totalItems <= 100) {
          rowsPerPageItems.push(showAll);
        }
      }

      return rowsPerPageItems;
    }
  },
  watch: {
    [getter.LOADING](val) {
      if (val) {
        this.scrollToTop();
        this.vTableBodyEl.classList.add(TABLE_BODY_LOADING_CLS);
      } else {
        this.vTableBodyEl.classList.remove(TABLE_BODY_LOADING_CLS);
      }
    }
  },
  mounted() {
    this.setUpSizeDOMHandlers();
  },
  beforeDestroy() {
    this.removeSizeDOMHandlers();
  },
  methods: {
    handleRowSelect(value, row) {
      const column = row[this.defaultMassActionsColumn];
      this.$storeGrid_dispatch(actions.TOGGLE_ROW, { column, value });
    },
    getIsRowSelected(row) {
      return (
        this.selectedRows.filter(
          selectedRow =>
            selectedRow[this.defaultMassActionsColumn] ===
            row[this.defaultMassActionsColumn]
        ).length === 1
      );
    },
    setUpSizeDOMHandlers() {
      window.addEventListener(RESIZE_DOM_EVENT, this.debouncedUpdateTableWidth);
      this.debouncedUpdateTableWidth();
    },
    removeSizeDOMHandlers() {
      window.removeEventListener(
        RESIZE_DOM_EVENT,
        this.debouncedUpdateTableWidth
      );
    },
    scrollToTop() {
      return this.$vuetify.goTo(this.vTableOverflowEl, {
        offset: 15,
        easing: "easeInQuart"
      });
    },
    debouncedUpdateTableWidth: debounce(function() {
      this.$nextTick().then(() => {
        this.tableWidth = this.vTableOverflowEl.offsetWidth;
      });
    }, 50)
  }
};
</script>

<style lang="scss">
.hm-grid__table {
  /*box-shadow: none !important;*/


  > .v-data-table__wrapper {
    /* vuetify 2 fix: была видна вертикальная полоса прокрутки */
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;

    table {
      overflow: visible !important;
      min-width: 100%;
      display: table !important;
      max-width: unset !important;
      margin-bottom: 0 !important;

      td, th {
        font-size: 14px !important;
        line-height: 21px;
      }
      td:not(.hm-grid-row__cell-checkbox):not(.hm-context-menu-button) {
        // было 24px
        padding-right: 20px !important;
        padding-left: 20px !important;
        /*
          убрано, иначе мешает прицелиться по v-expand

          padding-top: 4px !important;
          padding-bottom: 4px !important;
         */
      }
    }
  }
}
.v-datatable tbody {
  will-change: opacity;
  transition: opacity 0.5s ease-out;
  opacity: 1;

  &.isLoading {
    pointer-events: none;
    opacity: 0.5;
  }
}
</style>
