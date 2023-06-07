<template>
  <div class="hm-grid__header-actions"> <!--  v-if="tableSwitcher.length > 0" было -->
    <div :colspan="headers.length + 2">
      <div class="hm-grid__header-actions__content">
        <slot name="header-actions-before" />

        <hm-partials-actions
          class="hm-grid__header-actions__links"
          v-if="headerActions"
          :actions="headerActions"
          style="padding-bottom: 0"
          :enabled="!loading"
        >
        </hm-partials-actions>

        <div class="hm-grid__header-actions__line-end">
<!--            <div class="show-in-grid">-->
<!--              {{ tableSwitcher.label }}-->
<!--            </div>-->

          <hm-switch-checkmark
            v-if="tableSwitcher !== null"
            :value="switcherValue"
            :true-value="trueValue"
            :false-value="falseValue"
            :label="tableSwitcher.label"
            :title="tableSwitcher.title"
            @input="switcherValueSet"
            label-left-side
          ></hm-switch-checkmark>

          <hm-hotkey
            :filter-target="true"
            @pressed="btnShowFiltersClicked"
            keys="F|А"
            :tooltip="_(filtersApplied ? 'Показать применённые фильтры' : 'Показать фильтры')"
          >
            <hm-toggle-button
              :class="addCssClassesPrefixed(
                'hm-grid__header-actions__show-filters',
                {
                  '--applied': filtersApplied,
                }
              )"
              :color-off-bg="colorBtnShowFiltersBg"
              :color-off-border="colorBtnShowFiltersBorder"
              :value="filtersVisible"
              :label="_('Фильтр')"
              @click="btnShowFiltersClicked"
              svg-icon-name="filter"
            ></hm-toggle-button>
          </hm-hotkey>

        </div>
      </div>
    </div>
  </div>
</template>
<script>
import * as getter from "../module/getters/names";
import * as actions from "../module/actions/actions";
import { DEFAULT_GRID_MODULE_NAME } from "../constants";
import VueMixinStoreGridGenerator from "@/components/hm-grid/mixins/VueMixinStoreGridGenerator";
import addCssClassesPrefixed from "@/utilities/addCssClassesPrefixed";

import HmSwitchCheckmark from "@/components/controls/hm-switch-checkmark";
import HmToggleButton from "@/components/controls/hm-toggle-button";
import HmPartialsActions from "@/components/layout/hm-partials-actions";
import HmHotkey from "@/components/helpers/hm-hotkey";
import hexToRgba from "hex-to-rgba";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import * as mutations from "../module/mutations/types";

export default {
  name: "HmGridHeaderActions",
  components: {
    HmPartialsActions,
    HmToggleButton,
    HmSwitchCheckmark,
    HmHotkey,
  },
  mixins: [
    VueMixinConfigColors,
    VueMixinStoreGridGenerator({
      moduleNameProperty: "gridModuleName",

      mapStateToComputed: {
        filtersVisible: state => state.filtersVisible,
        headerActions: state => state.headerActions
      },

      mapGettersToComputed: {
        filtersApplied: getter.FILTERS_APPLIED,
        headers: getter.HEADERS_SHOWN,
        tableSwitcher: getter.TABLE_SWITCHER,
        loading: getter.LOADING
      },
    }),
  ],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    },
  },
  data() {
    return {
      addCssClassesPrefixed,
      // switcherValue: undefined,
      // activeAjax: false,
      // trueValue: true,
      // falseValue: false,
    };
  },
  computed: {
    falseValue() {
      return (this.tableSwitcher && this.tableSwitcher.modes)
        ? this.tableSwitcher.modes[0] + ""
        : 0;
    },
    trueValue() {
      return (this.tableSwitcher && this.tableSwitcher.modes)
        ? this.tableSwitcher.modes[1] + ""
        : 1;
    },
    colorBtnShowFiltersBg() {
      return this.filtersApplied ? this.colors.warningBackground : null;
    },
    colorBtnShowFiltersBorder() {
      return this.filtersApplied ? hexToRgba(this.colors.charts, 0.5) : null;
    },
    switcherValue() {
      return (this.tableSwitcher && this.tableSwitcher.modes)
        ? this.tableSwitcher.currentMode + ""
        : 0;
    }
  },
  // watch: {
  //   switcherValue(newValue, oldValue) {
  //     if (typeof(oldValue) == "undefined") {
  //       return;
  //     }
  //
  //     this.$storeGrid_commit(mutations.TABLE_SWITCHER_MODE_SET, newValue);
  //     this.$storeGrid_dispatch(actions.REQUEST_RELOAD);
  //
  //     // if(this.activeAjax) {
  //     // this.$storeGrid_dispatch(actions.APPLY_FILTER, {
  //     //   column: this.tableSwitcher.param,
  //     //   value: this.switcherValue,
  //     // });
  //   },
  // },
  // created(){
    // if (this.tableSwitcher && this.tableSwitcher.modes) {
      // this.falseValue = this.tableSwitcher.modes[0] + "";
      // this.trueValue = this.tableSwitcher.modes[1] + "";
      // this.switcherValue = this.tableSwitcher.currentMode + "";
    // }
  // },
  methods: {
    btnShowFiltersClicked() {
      this.$storeGrid_dispatch(actions.FILTERS_VISIBILITY_TOGGLE);
    },
    switcherValueSet(newValue) {
      // if (typeof(oldValue) == "undefined") {
      //   return;
      // }

      // if(this.activeAjax) {
      // this.$storeGrid_dispatch(actions.APPLY_FILTER, {
      //   column: this.tableSwitcher.param,
      //   value: this.switcherValue,
      // });

      // this.$storeGrid_commit(mutations.TABLE_SWITCHER_MODE_SET, newValue);
      // this.$storeGrid_commit(mutations.SET_PAGINATION, 1);
      // this.$storeGrid_commit(mutations.SET_PAGINATION_DIRTY, null);
      // this.$storeGrid_dispatch(actions.REQUEST_RELOAD);

      if(window.location.href.includes('assign')) this.$storeGrid_commit(mutations.SET_PAGINATION_DIRTY, {sortBy: 'assigned', page: 1, descending: true});
      else this.$storeGrid_commit(mutations.SET_PAGINATION_DIRTY, null);

      this.$storeGrid_dispatch(actions.APPLY_SWITCHER_MODE, newValue);
    }
  },
};
</script>
<style lang="sass">
.hm-grid__header-actions
  height: auto !important
  border: none !important
  width: 100%
  height: auto !important
  padding: 28px 16px !important

  &__links
    flex-wrap: wrap

  // th
  //   text-align: right
  //   height: auto !important
  //   padding: 28px 16px !important

  &__content
    display: flex
    flex-wrap: wrap

  .v-input--selection-controls
    /* vuetify 2 fix */
    margin: 7px

  .hm-partials-actions
    padding-bottom: 0

  &__line-end
    float: right
    display: flex
    align-items: center
    flex-grow: 1
    justify-content: flex-end

    > * + *
      margin-left: 26px

  //&__show-filters
  //  &--applied
  //    .v-btn:not(.v-btn--depressed)
  //      background-color: #FFE99D !important
  //      box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5) !important

  .show-in-grid
    font-size: 16px
    float: left
    margin-right: 10px
</style>
