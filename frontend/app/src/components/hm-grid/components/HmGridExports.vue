<template xmlns:slot="http://www.w3.org/1999/XSL/Transform">
  <div class="hm-grid-exports__wrapper">
    <v-style>
      #{{ uid }}.hm-grid-exports .hm-grid-exports__link:hover svg path {
        fill: {{ _colorIconHover }};
      }

      #{{ uid }}.hm-grid-exports .hm-grid-exports__link:active svg path {
        fill: {{ _colorIconClicked }};
      }

      /*
        #{{ uid }}.hm-grid-exports .hm-grid-exports__link .v-ripple__animation,
        #{{ uid }}.hm-grid-exports .hm-grid-exports__link .v-ripple__container {
          color: {{ _colorIconClicked }};
        }
      */
    </v-style>

    <v-btn-toggle
      :id="uid"
      class="hm-grid-exports primary white--text v-sheet"
      dark
    >
      <v-tooltip v-for="actionType in exports" :key="actionType" bottom>
        <template v-slot:activator="{ on: onTooltip }">
          <v-btn
            v-on="onTooltip"
            :loading="isLoading === actionType"
            class="hm-grid-exports__link"
            text
            @click="handleExportClick(actionType)"
          >
            <svg-icon v-bind="getSvgIconAttrs(actionType)" />
          </v-btn>
        </template>

        {{ getButtonTitle(actionType) }}
      </v-tooltip>

    </v-btn-toggle>
  </div>
</template>

<script>
import svgIcon from "@/components/icons/svgIcon";
import { DEFAULT_GRID_MODULE_NAME } from "../constants";
import * as actions from "../module/actions/actions";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

export default {
  components: {
    svgIcon,
  },
  mixins: [VueMixinConfigColors],
  props: {
    exports: {
      type: Array,
      default: () => []
    },
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    }
  },
  data() {
    return {
      isLoading: null,
      selectedAction: []
    };
  },
  computed: {
    _colorIcon() {
      return "#FFFFFF";
    },
    _colorIconHover() {
      return this.getColor(configColors.charts);
    },
    _colorIconClicked() {
      return this.getColor(configColors.textDark);
    },
  },
  methods: {
    handleExportClick(exportType) {
      this.$store.dispatch(
        `${this.gridModuleName}/${actions.EXPORT_GRID}`,
        exportType
      );
    },
    getSvgIconName(actionType) {
      let DEFAULT_ICON_NAME = "printer";

      let ACTION_TO_SVG_ICON_NAME = {
        print: DEFAULT_ICON_NAME,
        excel: "file-excel",
        word: "file-word",
      };

      return ACTION_TO_SVG_ICON_NAME[actionType] || DEFAULT_ICON_NAME;
    },
    getSvgIconAttrs(actionType) {
      return {
        name: this.getSvgIconName(actionType),
        color: this._colorIcon,
        title: "",
      };
    },
    getButtonTitle(actionType) {
      let ACTION_TO_BUTTON_TITLE = {
        print: "Печать",
        excel: "Сохранить как файл Excel",
        word: "Сохранить как файл Word",
      };

      return ACTION_TO_BUTTON_TITLE[actionType] || "Экспорт";
    },
  },
};
</script>

<style lang="scss">
.hm-grid-exports.v-btn-toggle {
  border-radius: 6px;
  padding: 0;

  .hm-grid-exports__link.v-btn {
    width: 40px !important;
    height: 45px !important;
    min-width: unset !important;
    border: none;
    margin: 0 !important;
    padding: 0;
    transition: all 0.3s;

    /* Отключаем затенение */
    &:before {
      opacity: 0 !important;
    }

    &:first-child {
      width: 48px !important;
      padding-left: 8px;
    }
    &:last-child {
      width: 48px !important;
      padding-right: 8px;
    }

    /* Отключаем эффект разводов при нажатии */
    .v-ripple__animation,
    .v-ripple__container {
      opacity: 0;
    }
  }
}
</style>
