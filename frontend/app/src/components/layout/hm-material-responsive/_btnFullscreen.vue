<template>
  <v-btn
    class="hm-material-responsive__btn-fullscreen"
    :class="{
      'state-fullscreen': fullscreen,
    }"
    text
    :color="colorTextCurrent"
    v-bind="$attrs"
    v-on="$listeners"
    @click="toggleFullscreen"
  >
    <v-style-head-once
      name="hmMaterialResponsiveBtnFullscreenStyle"
    >
      .hm-material-responsive__btn-fullscreen:hover {
        background-color: {{ backgroundColorHover }} !important;
      }
      .hm-material-responsive__btn-fullscreen:hover .svg-icon path {
        fill: {{ colorIconHover }} !important;
      }
      .hm-material-responsive__btn-fullscreen:hover .v-ripple__animation {
        background-color: {{ colorRipple }} !important;
        opacity: 0.4 !important;
      }

      .hm-material-responsive__btn-fullscreen.state-fullscreen:hover {
        background-color: {{ backgroundColorHoverFullscreen }} !important;
      }
      .hm-material-responsive__btn-fullscreen.state-fullscreen:hover .svg-icon path {
        fill: {{ colorTextBaseFullscreen }} !important;
      }
      .hm-material-responsive__btn-fullscreen.state-fullscreen:hover .v-ripple__animation {
        background-color: {{ colorRippleFullscreen }} !important;
        opacity: 0.4 !important;
      }
    </v-style-head-once>

    {{
      fullscreen
        ? "Выход из полноэкранного режима"
        : "Полноэкранный режим"
    }}

    <svg-icon
      :name="fullscreen ? 'fullscreen-back' : 'fullscreen'"
      :color="colorIconCurrent"
      title=""
    />
  </v-btn>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import VStyleHeadOnce from "@/components/helpers/v-style-head-once";
import configColors from "@/utilities/configColors";
import svgIcon from "@/components/icons/svgIcon";
import hexToRgba from "hex-to-rgba"

export default {
  name: "HmMaterialResponsiveBtnFullscreen",
  components: {
    VStyleHeadOnce,
    svgIcon,
  },
  mixins: [VueMixinConfigColors],
  props: {
    /** v-model, full screen */
    type: {
      type: String,
      default: ""
    },
    value: {
      type: null,
      default: false,
    },
  },
  computed: {
    fullscreen() {
      return this.value;
    },
    colorTextBaseFullscreen() {
      return "#FFF";
    },
    colorTextBaseCurrent() {
      return this.fullscreen ? this.colorTextBaseFullscreen : null;
    },
    colorTextCurrent() {
      if(this.type !== "html") return this.fullscreen ? this.colorTextBaseCurrent : this.getColor(configColors.textContrast);
    },
    colorIconCurrent() {
      if(this.type !== "html") return this.fullscreen ? this.colorTextCurrent : this.getColor(configColors.primaryLight);
    },

    colorIconHover() {
      return this.getColor(configColors.primaryDark);
    },

    backgroundColorHover() {
      return this.getColor(configColors.menuBackgroundSelected);
    },
    colorRipple() {
      return this.getColor(configColors.primaryDark);
    },

    backgroundColorHoverFullscreen() {
      return this.getColor(configColors.iconGray);
    },
    colorRippleFullscreen() {
      return this.getColor(configColors.textLight);
    },
  },
  methods: {
    toggleFullscreen() {
      this.$emit("input", !this.fullscreen);
    },
  },
};
</script>

<style lang="scss">
.hm-material-responsive__btn-fullscreen {
  text-transform: none;
  font-weight: normal;
  font-size: 14px;
  line-height: 21px;

  /* удаляем серый фильтр */
  &:before {
    opacity: 0 !important;
  }

  &:hover {
    box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.25);
  }

  .svg-icon {
    margin-left: 16px;
  }
}
</style>
