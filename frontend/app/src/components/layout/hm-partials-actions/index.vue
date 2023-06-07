<template>
    <v-btn-toggle
    :background-color="themeColors.contentColor"
    :class="{
      'hm-partials-actions': hasActions,
      'hm-partials-actions__disabled': !enabled,
    }"
    borderless
  >
      <v-btn
        v-for="action in actions"
        :key="action.label"
        :color="colorButton"
        :disabled="!enabled"
        :href="action.href"
        text
      >
        <svg-icon
          v-if="action.icon"
          :name="action.icon"
          :title="action.label"
          :color="colorIcon"
          :width="16"
          :height="16"
          :stroke-width="0.5"
          style="margin-right: 8px"
        >
        </svg-icon>

        {{ action.label }}
      </v-btn>
      <slot></slot>
    </v-btn-toggle>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";
import svgIcon from "@/components/icons/svgIcon";

/** Применяется для действий сверху hm-grid */
export default {
  name: "HmPartialsActions",
  components: {
    svgIcon,
  },
  mixins: [VueMixinConfigColors],
  props: {
    actions: {
      type: Array,
      default: () => [],
    },
    enabled: {
      type: Boolean,
      default: true,
    }
  },
  computed: {
    colorButton() {
      return this.getColor(configColors.textLight);
    },
    colorIcon() {
      return this.enabled ? this.themeColors.accent : this.colors.textLight;
    },
    hasActions() {
      return this.actions.length > 0;
    }
  },
};
</script>

<style lang="scss">
/* Действия над grid */
.hm-partials-actions {
  padding-bottom: 16px;
  background: white;

  &.v-item-group .v-btn{
    text-transform: none;
    font-weight: normal;
    height: 36px !important;

    & .v-btn__content {
      line-height: 16px;
      //border-bottom: 1px solid rgba(25, 118, 210, 0.3);
    }
    opacity: 1;
  }
}
</style>
