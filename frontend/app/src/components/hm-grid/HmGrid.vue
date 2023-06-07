<template>
  <section
    class="section hm-grid__section"
    v-if="isGridInited"
    :id="uid"
  >
    <v-style>
      #{{ uid }}.hm-grid__section .v-data-table {
        color: {{ _colorText }};
      }
      #{{ uid }}.hm-grid__section .hm-grid__section th {
        color: {{ _colorTextContrast }};
      }
      #{{ uid }}.hm-grid__section table tbody tr:hover,
      #{{ uid }}.hm-grid__section table tbody tr.state-menu-opened {
        background-color: {{ _colorGridBackgroundSelected }} !important;
      }
      #{{ uid }}.hm-grid__section .lessFormatedHtml a {
        color: {{ _colorLinks }};
      }

      #{{ uid }}.hm-grid__section .highlighted-highlighted,
      #{{ uid }}.hm-grid__section .highlighted-error {
        background-color: {{ _colorHighlightError }} !important;
      }
      #{{ uid }}.hm-grid__section .highlighted-warning {
        background-color: {{ _colorHighlightWarning }} !important;
      }
      #{{ uid }}.hm-grid__section .highlighted-success {
        background-color: {{ _colorHighlightSuccess }} !important;
      }
      #{{ uid }}.hm-grid__section .hm-grid__table,
      #{{ uid }}.hm-grid__section .hm-grid-footer {
        background-color: {{ _backgroundColorGrid }} !important;
      }
    </v-style>

    <hm-grid-table-wrapper
      v-if="!isInitLoading"
      :grid-module-name="storeGridModuleName"
    >
      <template v-slot:header-actions-before>
        <slot name="header-actions-before" />
      </template>
    </hm-grid-table-wrapper>

    <hm-grid-loading v-else></hm-grid-loading>
    <hm-grid-error :grid-module-name="storeGridModuleName"></hm-grid-error>
  </section>
  <v-alert v-else value="true" type="error">
    Ошибка инициализации
  </v-alert>
</template>

<script>
import hexToRgba from "hex-to-rgba";

// import { DEFAULT_GRID_MODULE_NAME } from "./constants";

import HmGridTableWrapper from "./components/HmGridTableWrapper";
import HmGridLoading from "./components/HmGridLoading";
import HmGridError from "./components/HmGridError";

import { registerGridVuexModule } from "./module";

import * as mutations from "./module/mutations/types";
import * as actions from "./module/actions/actions";

// import { createNamespacedHelpers } from "vuex";

import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import VueMixinStoreGridGenerator from "@/components/hm-grid/mixins/VueMixinStoreGridGenerator";

export default {
  name: "HmGrid",
  components: {
    HmGridTableWrapper,
    HmGridError,
    HmGridLoading,
  },
  mixins: [
    VueMixinConfigColors,
    VueMixinStoreGridGenerator({
      moduleNameProperty: "storeGridModuleName",
      mapStateToComputed: {
        isGridInited: state => state.isGridInited,
        isInitLoading: state => state.isInitLoading,
      },
    }),
  ],
  props: {
    loadUrl: {
      type: String,
      default: () => window.location.href,
    },
    id: {
      type: String,
      // default: () => DEFAULT_GRID_MODULE_NAME,
      default: null,
    },
    colorText: {
      type: String,
      default: null
    },
    colorTextContrast: {
      type: String,
      default: null,
    },
    debug: Boolean,
    initialData: {
      type: Object,
      default: null,
    }
  },
  data() {
    return {
      storeGridModuleName: "grid",
      url: this.loadUrl,
      // storeStateAvailable: false,
    };
  },
  computed: {
    // gridState() {
    //   return this.$store.state.grid.items[this.id];
    // },
    _colorText() {
      return (
        this.colorText || this.getColor(configColors.textDefault)
      );
    },
    _colorTextContrast() {
      return (
        this.colorTextContrast ||
        this.getColor(configColors.textContrast)
      );
    },
    _colorGridBackgroundSelected() {
      return this.themeColors.gridBackgroundSelected;
    },
    _colorLinks() {
      return this.getColor(configColors.primarySaturated, "#00F");
    },
    _colorHighlightError() {
      return hexToRgba(this.themeColors.error, 0.1);
    },
    _colorHighlightWarning() {
      return hexToRgba(this.themeColors.warning, 0.1);
    },
    _colorHighlightSuccess() {
      return hexToRgba(this.themeColors.success, 0.1);
    },
    _backgroundColorGrid() {
      return this.themeColors.contentColor;
    },
    // _colorRowMenuIcon() {
    //   return this.getColor(configColors.textLight);
    // },
    // _colorRowMenuIconActive() {
    //   return this.getColor(configColors.primarySaturated);
    // },
  },
  // watch: {
  //   gridState(v) {
  //     if (v.hasOwnProperty("loadUrl")) {
  //       this.url = v.loadUrl;
  //       this.updateGridUrlInStore();
  //       this.loadGrid();
  //     }
  //   },
  // },
  beforeDestroy() {
    this.$store.unregisterModule(this.storeGridModuleName);
  },
  // beforeCreate() {
    // const { mapState } = createNamespacedHelpers(this.storeGridModuleName);
    //
    // this.$composeComputed(
    //   mapState({
    //     isGridInited: state => state.isGridInited,
    //     isInitLoading: state => state.isInitLoading,
    //   })
    // );
  // },
  created() {
    /** @see VueMixinStoreGridGenerator */
    this.storeGridModuleName = registerGridVuexModule(this.$store, this.id);

    this.$storeGrid_commit(mutations.INIT_GRID);
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.updateGridUrlInStore();

      this.$storeGrid_commit(mutations.SET_GRID_ID, this.id);

      this.$nextTick(() => {
        if (this.initialData) {
          this.$storeGrid_dispatch(actions.INIT_LOADING_SUCCESS, this.initialData);
        } else {
          this.loadGrid()
        }
      });
    },
    updateGridUrlInStore() {
      if (!this.url) {
        return;
      }
      this.$storeGrid_commit(mutations.SET_API_URL, this.url);
    },
    loadGrid() {
      if (!this.url) {
        return;
      }
      this.$storeGrid_dispatch(actions.INIT_LOAD_REQUEST, this.url);
    },
  },
};
</script>

<style lang="scss">
/**
 * стили для цветов генерируются динамически в <v-style> сверху
 * стили для самой таблицы данных в components/HmGridTable.vue
 * стили для содержимого строк в components/HmGridRow.vue
 * стили заголовков в components/HmGridHeader.vue
 */
.hm-grid__section {
  position: relative;

  &.section {
    min-height: 500px;
  }
}

.hm-grid-row {
  .lessFormatedHtml {
    padding: 6px 6px;
    border-radius: 4px;
  }
  img{
    margin-right: 7.5px;
  }
}
</style>
