<template>
  <v-list
    :color="themeColors.contextMenu || '#ffffff'"
    class="hm-grid-actions-menu"
    dense
  >
    <template v-for="{ icon: iconTemplate, url, confirm, target } in actions">
      <component
        :is="transitionName"
        v-if="iconTemplate"
        :key="composeActionKey(iconTemplate, url)"
        duration="2000"
        appear
      >
        <!-- icon: custom html with icon -->
        <v-list-item :href="url" :target="target" @click="onActionClick(confirm, $event)">
          <hm-dependency
            :template="iconTemplate"
            class="hm-grid-action__injected-vue-template"
          />
        </v-list-item>
      </component>
    </template>
  </v-list>
</template>

<script>
import HmDependency from "@/components/helpers/hm-dependency";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import {
  SLIDEY_REVERSE_TRANSITION,
  SLIDEX_TRANSITION,
  SLIDEY_TRANSITION,
} from "../constants";

export default {
  mixins: [VueMixinConfigColors],
  components: {
    HmDependency,
  },
  props: {
    actions: {
      type: Array || undefined,
      default: () => [],
    },
  },
  computed: {
    isShown() {
      return this.actions && this.actions.length;
    },
    transitionName() {
      if (this.isOnSmallScreen) return SLIDEX_TRANSITION;
      return this.isLast ? SLIDEY_REVERSE_TRANSITION : SLIDEY_TRANSITION;
    },
    isOnSmallScreen() {
      return this.$vuetify.breakpoint.smAndDown;
    },
  },
  methods: {
    composeActionKey(icon = "", url = "") {
      return icon + url;
    },
    onActionClick(confirmText, event) {
      if (confirmText && !confirm(confirmText)) {
        event.preventDefault();
      }
    },
  },
};
</script>

<style lang="scss">
.hm-grid-action__injected-vue-template {
  display: flex;
  align-items: center;
  justify-content: space-around;
  margin-left: 46px;
  /*.ui-els-icon {
    margin-right: 16px;
  }*/
  .v-icon,
  .svg-icon {
    margin-left: -46px;
    margin-right: 22px;
  }
}

.hm-grid-actions-menu {
  .theme--light.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) {
    color: #333 !important;
  }
}
</style>
