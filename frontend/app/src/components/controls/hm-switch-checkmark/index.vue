<template>
  <div
    :id="uid"
    class="hm-switch-checkmark__wrapper"
    :class="{
      'label-left-side': labelLeftSide,
    }"
  >
    <v-style>
      #{{ uid }}.hm-switch-checkmark__wrapper .v-label {
        color: {{ _colorLabel }};
      }
      #{{ uid }}.hm-switch-checkmark__wrapper .v-input--switch__thumb {
        background-color: {{ _colorHandle }};
      }
      #{{ uid }}.hm-switch-checkmark__wrapper .v-input--switch__track:before {
        background-image: url("{{ iconCheckmarkData }}");
      }
      /* выкл: */
      #{{ uid }}.hm-switch-checkmark__wrapper .v-input:not(.v-input--is-dirty) .v-input--switch__track {
        background-color: {{ _colorUnchecked }};
      }
    </v-style>
    <v-switch
      v-model="temporaryValue"
      class="hm-switch-checkmark"
      v-bind="$attrs"
      :color="_color"
      :hide-details="!showDetails"
      dense
      inset
      v-on="$listeners"
    >
    </v-switch>
  </div>
</template>

<script>

import configColors from "@/utilities/configColors";
import fnSvgCheckmark from "./fnSvgCheckmark";
import miniSvgDataUri from "mini-svg-data-uri";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

/**
 * Переключатель с галочкой. Использовать вместо <v-switch>, где возможно
 *
 * "галочка" внедряется через динамический css в виде background-image псевдоэлемента .v-input--switch__track:before
 * (в v-switch нет подходящего слота). css динамический, т. к. цвет галочки меняется из design.ini
 */
export default {
  name: "HmSwitchCheckmark",
  mixins: [VueMixinConfigColors],
  props: {
    labelLeftSide: {
      type: Boolean,
      default: false,
    },
    /** show vuetify hint, validation errors */
    showDetails: {
      type: Boolean,
      default: false,
    },
    color: {
      type: String,
      default: null,
    },
    colorUnchecked: {
      type: String,
      default: null,
    },
    colorLabel: {
      type: String,
      default: null,
    },
    colorHandle: {
      type: String,
      default: null,
    },
    value: {
      type: [String, Number, Boolean],
      default: null,
    },
  },
  data() {
    return {
      // initialize
      temporaryValue: this.value,
    };
  },
  computed: {
    _color() {
      return this.color || this.themeColors.accent;
    },
    _colorUnchecked() {
      return (
        this.colorUnchecked || this.getColor(configColors.disabledColored)
      );
    },
    _colorLabel() {
      return (
        this.colorLabel || this.getColor(configColors.textContrast)
      );
    },
    _colorHandle() {
      let defaultColor = this.darkTheme ? "#000000" : "#FFFFFF";

      return (this.colorHandle || defaultColor);
    },
    iconCheckmarkData() {
      let image = fnSvgCheckmark(this._colorHandle);
      let urlEncodedImage = miniSvgDataUri(image);
      return urlEncodedImage;
    }
  },
  watch: {
    // see https://stackoverflow.com/a/47312172
    temporaryValue(newValue) {
      this.$emit("input", newValue);
    },
  },
};
</script>

<style lang="scss">

.hm-switch-checkmark__wrapper {
  .v-input--selection-controls__input {
    /* TODO хак, центровка относительно label */
    margin-top: 1px;
  }
  .v-input__slot {
    margin-bottom: 0;

    > .v-label {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02rem;
    }
  }
  .v-input--switch--inset {
    .v-input--switch__track {
      height: 20px !important;
      width: 46px !important;
      left: -1px !important;
      top: 1px !important;
      opacity: 1;

      &:before {
        width: 8px;
        height: 8px;
        position: absolute;
        display: block;
        content: "";
        background-repeat: no-repeat;
        top: 7px;
        left: 8px;
        opacity: 0;
      }
    }
    .v-input--switch__thumb {
      height: 18px;
      width: 18px;
    }
    .v-input--selection-controls__ripple {
      height: 28px;
      width: 28px;
      margin: 7px;
    }

    /* вкл */
    &.v-input--is-dirty {
      .v-input--selection-controls__ripple,
      .v-input--switch__thumb {
        transform: translate(26px) !important;
      }

      .v-input--switch__track:before {
        opacity: 1;
      }
    }
  }

  .v-input--switch__thumb,
  .v-input--switch__track,
  .v-input--selection-controls__ripple {
    transition-duration: 0.2s;
  }
}

.hm-switch-checkmark__wrapper.label-left-side {
  .v-input__slot {
    > .v-label {
      order: -1;
    }
  }
  .v-input--selection-controls__input {
    margin-right: 0;
    margin-left: 14px;
  }
}
</style>
