<template>
  <component
    :is="this.isTableCell ? 'td' : 'div'"
    class="hm-context-menu-button"
    ref="refMenuButton"
  >
    <v-style-head-once>
      .hm-context-menu-button .svg-icon g,
      .hm-context-menu-button .svg-icon path {
        fill: {{ _colorContextMenuButton }} !important;
      }

      .hm-context-menu-button:hover .svg-icon g,
      .hm-context-menu-button:hover .svg-icon path {
        fill: {{ _colorContextMenuButtonHover }} !important;
      }
    </v-style-head-once>
    <div class="hm-context-menu-button__container">
    </div>
  </component>
</template>
<script>
import VStyleHeadOnce from "@/components/helpers/v-style-head-once";

import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

/**
 * this.isTableCell - возможность рендера как td, т. к. div не задать на всю ширину и высоту ячейки таблицы, если она динамическая
 */
export default {
  name: "HmContextMenuNoButton",
  components: {
    VStyleHeadOnce,
  },
  mixins: [VueMixinConfigColors],
  props: {
    isTableCell: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    _colorContextMenuButton() {
      return this.getColor(configColors.textLight);
    },
    _colorContextMenuButtonHover() {
      return this.getColor(configColors.primarySaturated);
    },
  },
};
</script>

<style lang="scss">
.hm-context-menu-button {
  cursor: pointer;
  &__container {
    display: flex;
    justify-content: flex-end;
  }

  /* по центру вертикальной */
  .svg-icon {
    display: block;
  }
}
td.hm-context-menu-button {
  padding-right: 36px !important;
  padding-left: 6px !important;
}
</style>
