<template>
  <div
    :id="uid"
    class="hm-toggle-button__wrapper"
  >
    <v-style>
      #{{ uid }}.hm-toggle-button__wrapper .v-btn:before {
        background-color: {{ _colorBase }} !important;
      }
      #{{ uid }}.hm-toggle-button__wrapper .v-btn:not(.v-btn--depressed) {
        background-color: {{ _colorOffBg }} !important;
        border-color: {{ _colorOffBorder }} !important;
      }
      #{{ uid }}.hm-toggle-button__wrapper .v-btn.v-btn--depressed {
        background-color: {{ _colorOnBg }};
        border-color: {{ _colorOnBorder }} !important;
      }
    </v-style>
    <v-btn
      :class="{
        'hm-toggle-button': true,
        'hm-toggle-button__state-limit-width': maxWidth,
        'v-btn--depressed': value
      }"
      v-on="$listeners"
      v-bind="$attrs"
      :color="_colorBase"
      :depressed="value"
      :text="value"
      :style="{
        maxWidth: maxWidth ? addPx(maxWidth) : null,
      }"
      dense
      inset
    >
      <slot>
        <svg-icon
          v-if="svgIconName"
          :name="svgIconName"
          :color="_colorIcon"
          :width="iconSize"
          :height="iconSize"
          style="margin-right: 6px;"
        ></svg-icon>
        <span class="hm-toggle-button__label">{{ label }}</span>
      </slot>
    </v-btn>
  </div>
</template>

<script>
// <v-style>
//   #{{ uid }}.hm-switch-checkmark__wrapper .v-label {
//     color: {{ _colorLabel }};
//   }
// </v-style>

// import configColors from "../../../utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import svgIcon from "../../icons/svgIcon";
import hexToRgba from 'hex-to-rgba';

import addPx from "@/utilities/addPx";

/**
 * Переключатель с галочкой. Использовать вместо <v-switch>, где возможно
 *
 * "галочка" внедряется через динамический css в виде background-image псевдоэлемента .v-input--switch__track:before
 * (в v-switch нет подходящего слота). css динамический, т. к. цвет галочки меняется из design.ini
 */
export default {
  name: "HmToggleButton",
  components: { svgIcon },
  mixins: [VueMixinConfigColors],
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    colorOffBg: {
      type: String,
      default: null,
    },
    colorOffBorder: {
      type: String,
      default: null,
    },
    label: {
      type: String,
      default: null,
    },
    svgIconName: {
      type: String,
      default: null,
    },
    iconSize: {
      type: [String, Number],
      default: "16px",
    },
    maxWidth: {
      type: [String, Number],
      default: null,
    }
  },
  data() {
    return {
      // initialize
      temporaryValue: this.value,
    };
  },
  computed: {
    _colorBase() {
      return this.themeColors.accent;
    },
    _colorOffBg() {
      return this.colorOffBg || (this.darkTheme ? "#000" : "#FFF");
    },
    _colorOnBg() {
      return hexToRgba(this._colorBase, 0.3);
    },
    _colorOffBorder() {
      return this.colorOffBorder || this.colors.iconGray;
    },
    _colorOnBorder() {
      return hexToRgba(this.colors.iconGray, 0.3);
    },
    _colorIcon() {
      return this.value ? this.colors.primarySaturated : this.colors.textContrast;
    },
  },
  watch: {
    // see https://stackoverflow.com/a/47312172
    temporaryValue(newValue) {
      this.$emit("input", newValue);
    },
  },
  methods: {
    addPx,
  }
};
</script>

<style lang="sass">
.hm-toggle-button
  text-transform: none
  font-weight: normal
  border: 1px solid

  box-shadow: none

  &__state-limit-width
    .v-btn__content
      width: 100%

      .hm-toggle-button__label
        white-space: nowrap
        overflow: hidden
        text-overflow: ellipsis

  &:hover
    box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.25) !important

</style>
