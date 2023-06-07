<template>
  <v-slide-y-transition>
    <v-alert
      :key="isShown ? 'opened' : 'closed'"
      v-model="isShown"
      class="hm-grid__error v-sheet elevation-10"
      type="error"
      dismissible
    >
      <p class="body-2">
        {{ errorMessage }}
      </p>
      <v-divider class="mb-1 mt-2" dark></v-divider>
      <aside class="caption d-flex wrap align-center font-italic text-right">
        <v-flex shrink class="pa-0 ma-0 text-left">
          <v-chip small dark>
            <v-avatar>
              <v-icon>error</v-icon>
            </v-avatar>
            Код ошибки - {{ errorCode }}
          </v-chip>
        </v-flex>
        <v-spacer></v-spacer>
        <v-flex shrink class="pa-0 ma-0">
          Вы можете закрыть это окно и попробовать снова
        </v-flex>
      </aside>
    </v-alert>
  </v-slide-y-transition>
</template>

<script>
import { DEFAULT_GRID_MODULE_NAME } from "../constants";

import { createNamespacedHelpers } from "vuex";
import * as mutations from "../module/mutations/types";

export default {
  props: {
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    }
  },
  computed: {
    isShown: {
      get() {
        return this.isErrorShown;
      },
      set(value) {
        this.$store.commit(`${this.gridModuleName}/${mutations.SET_ERROR}`, {
          isShown: value
        });
      }
    }
  },
  beforeCreate() {
    const namespace = this.$options.propsData.gridModuleName;
    const { mapState } = createNamespacedHelpers(namespace);
    const mappedState = mapState({
      errorMessage: state => state.error.message,
      errorCode: state => state.error.code,
      isErrorShown: state => state.error.isShown
    });
    this.$composeComputed(mappedState);
  }
};
</script>

<style lang="scss">
.hm-grid__error {
  max-width: 800px;
  z-index: 1;
  position: absolute;
  display: flex;
  top: 1rem;
  left: 0;
  right: 0;
  margin: 0 auto;
}
</style>
