<template>
  <v-btn-toggle class="hm-group-btn" v-model="active" mandatory>
    <v-style-head-once name="style-hm-group-btn">
      .hm-group-btn {
        border: 1px solid {{ colorBorderTransparent }};
      }
      /* Увеличение приоритета правила через дублирование класса */
      .hm-group-btn > .v-item--active.v-item--active.v-item--active.v-item--active {
          border: 1px solid {{ colorIcons }} !important;
      }
    </v-style-head-once>
    <template v-for="(btn, key) in buttons">
      <v-tooltip v-if="btn.icon" :key="key" bottom>
        <template v-slot:activator="{ on: onTooltip }">
          <v-btn
            :color="colorIcons"
            :href="btn.href"
            text
            v-on="onTooltip"
          >
            <svg-icon :color="colorIcons" :name="btn.icon" title="" />
          </v-btn>
        </template>
        <span>{{ btn.label }}</span>
      </v-tooltip>
      <v-btn v-else :key="key" :href="btn.href" text v-on="on">{{ btn.label }}</v-btn>
    </template>
  </v-btn-toggle>
</template>
<script>
import svgIcon from "@/components/icons/svgIcon";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import VStyleHeadOnce from "@/components/helpers/v-style-head-once";
import hexToRgba from "hex-to-rgba";

export default {
  components: {
    svgIcon,
    VStyleHeadOnce,
  },
  mixins: [VueMixinConfigColors],
  props: {
    buttons: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      active: null
    };
  },
  computed: {
    colorIcons() {
      return this.colors.primarySaturated;
    },
    colorBorderTransparent() {
      return hexToRgba(this.colors.primarySaturated, 0.5);
    },
  },
  created() {
    let activeIndex = this.buttons.findIndex(btn => btn.isActive);
    this.active = activeIndex != -1 ? activeIndex : null;
  },
};
</script>

<style lang="scss">
.hm-group-btn {
  background-color: transparent !important;
  box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.14);

  > .v-btn {
    padding-right: 16px !important;
    padding-left: 16px !important;
    height: 40px !important;
  }

  > .v-item--active {
    &:before {
      opacity: 0.3 !important;
    }
    cursor: unset;
  }
}
</style>
