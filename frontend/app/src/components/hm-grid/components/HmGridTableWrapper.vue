<template>
  <component :is="gridTitle ? `v-card` : `div`" class="hm-grid-table-wrapper">
    <v-card-title v-if="gridTitle">
      <h2 class="display-3">
        {{ gridTitle }}
      </h2>
    </v-card-title>
    <v-divider v-if="gridTitle"></v-divider>

    <hm-grid-table :grid-module-name="gridModuleName" >
      <template v-slot:header-actions-before>
        <slot name="header-actions-before" />
      </template>
    </hm-grid-table>
  </component>
</template>

<script>
import { DEFAULT_GRID_MODULE_NAME } from "../constants";

// import { createNamespacedHelpers } from "vuex";

import HmGridTable from "./HmGridTable";
import VueMixinStoreGridGenerator from "@/components/hm-grid/mixins/VueMixinStoreGridGenerator";

export default {
  components: {
    HmGridTable
  },
  mixins: [
    VueMixinStoreGridGenerator({
      moduleNameProperty: "gridModuleName",
      mapStateToComputed: {
        gridTitle: state => Boolean(state.config.title) && state.config.title
      },
    }),
  ],
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    }
  },
  beforeCreate() {
    // const namespace = this.$options.propsData.gridModuleName;
    // const { mapState } = createNamespacedHelpers(namespace);
    // this.$composeComputed(
    //   mapState({
    //     gridTitle: state => Boolean(state.config.title) && state.config.title
    //   })
    // );
  }
};
</script>

<style></style>
