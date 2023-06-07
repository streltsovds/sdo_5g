<template>
  <div
    class="hm-grid-pagination__wrapper"
    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap"
  >
    <div
      v-if="isPaginationShowed"
      :style="paginationStyle"
      class="text-center text-md-left hm-grid-pagination py-3"
    >
      <v-pagination
        :value="currentPage"
        :length="pages"
        class="justify-start"
        total-visible="15"
        @input="updatePagination"
      ></v-pagination>
    </div>

    <div class="hm-grid-pagination__slot-wrapper py-3"
    >
      <div
        v-if="isPaginationShowed"
        :style="{ color: colorPaginationText }"
      >
        {{ pageText }}
      </div>

      {{ /** Кнопки экспорта */ }}
      <slot></slot>
    </div>
  </div>
</template>

<script>
import {
  DEFAULT_GRID_MODULE_NAME,
  MIN_PAGINATION_WIDTH,
  MAX_PAGINATION_WIDTH
} from "../constants";

import { decline } from "@/utilities";

import { createNamespacedHelpers } from "vuex";
import * as actions from "../module/actions/actions";

import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

export default {
  mixins: [VueMixinConfigColors],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    },
  },
  data() {
    return {
      // currentPage: 1
    };
  },
  computed: {
    currentPage() {
      return this.pagination.page;
    },
    // page: {
    //   get() {
    //     return this.currentPage;
    //   },
    //   set(val) {
    //     this.currentPage = val;
    //   }
    // },
    isPaginationShowed() {
      return this.pages > 1;
    },
    isOnSmallScreen() {
      return this.$vuetify.breakpoint.smAndDown;
    },
    paginationStyle() {
      const minWidth = [
        this.$vuetify.breakpoint.xsOnly && MIN_PAGINATION_WIDTH,
        this.$vuetify.breakpoint.smOnly && MAX_PAGINATION_WIDTH
      ].filter(Boolean)[0];
      return {
        minWidth: `${this.isOnSmallScreen ? minWidth : MAX_PAGINATION_WIDTH}px`
      };
    },
    pages() {
      if (
        this.pagination.rowsPerPage == null ||
        this.pagination.totalItems == null
      )
        return 0;

      return Math.ceil(
        this.pagination.totalItems / this.pagination.rowsPerPage
      );
    },
    pageText() {
      const {
        itemsLength,
        pageStart,
        pageStop,
        totalItems,
        totalItemsText
      } = this;
      if (itemsLength === totalItems) {
        const declanationsTotalItems = ["строка", "строки", "строк"];
        const declanationsTotalItemsShowed = [
          "Показана",
          "Показано",
          "Показано"
        ];
        return `${decline(
          totalItems,
          declanationsTotalItemsShowed
        )} ${totalItems} ${decline(totalItems, declanationsTotalItems)}`;
      } else {
        return `Показаны строки ${pageStart +
          1} - ${pageStop} из ${totalItems} ${totalItemsText}`;
      }
    },
    totalItems() {
      return this.pagination.totalItems;
    },
    totalItemsText() {
      const declanationsTotalItems = ["строки", "строк", "строк"];
      return decline(this.totalItems, declanationsTotalItems);
    },
    pageStart() {
      return this.pagination.rowsPerPage === -1
        ? 0
        : (this.pagination.page - 1) * this.pagination.rowsPerPage;
    },
    pageStop() {
      return this.pagination.page * this.pagination.rowsPerPage >=
        this.totalItems
        ? this.totalItems
        : this.pagination.page * this.pagination.rowsPerPage;
    },
    colorPaginationText() {
      return this.getColor(configColors.textLight);
    },
  },
  watch: {
    // currentPage(selectedPage) {
    //   this.updatePagination(selectedPage);
    // }
  },
  beforeCreate() {
    const namespace = this.$options.propsData.gridModuleName;
    const { mapState } = createNamespacedHelpers(namespace);
    const mappedState = mapState({
      pagination: state => state.pagination,
      itemsLength: state => state.items.length
    });
    this.$composeComputed(mappedState);
  },
  methods: {
    /**
     * Получить экшн или мутацию уже с неймспейсом
     *
     * @param {String} mutationOrActionName Название мутации или экшена
     */
    getNamespacedName(actionName) {
      return `${this.gridModuleName}/${actionName}`;
    },
    updatePagination(page) {
      const pagination = { ...this.pagination, page };
      this.$store.dispatch(
        this.getNamespacedName(actions.SET_PAGINATION),
        pagination
      );
    }
  }
};
</script>

<style lang="scss">

.hm-grid-pagination__wrapper {
  > * + * {
    margin-left: 26px;
  }
}

.hm-grid-pagination {
  min-width: 430px;
  max-width: 700px;
  flex-grow: 1;

  ul.v-pagination {
    justify-content: flex-start;

    > li:first-child > .v-pagination__navigation {
      /* левая граница первой кнопки */
      margin-left: 0;
    }
  }

  .v-pagination__item {
    box-shadow: none;

    &:not(.v-pagination__item--active) {
      /* TODO вынести цвет */
      background-color: #f5f5f5;
    }
  }

  .v-pagination__navigation {
    box-shadow: none;

    &:not(.v-pagination__navigation--active) {
      /* TODO вынести цвет */
      background-color: #f5f5f5;
    }
  }
}

.hm-grid-pagination__slot-wrapper {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex-grow: 1;

  // промежуток между блоками
  > * + * {
    margin-left: 26px;
  }
}
</style>
