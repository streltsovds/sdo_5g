<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on: onTooltip }">
      <v-btn
        class="hm-date-range-picker-btn-clear"
        v-bind="$attrs"
        outlined
        :color="colorButtonBorders"
        v-on="{ ...$listeners, ...onTooltip }"
      >
        <span :style="{ color: colorText }">
          {{ _("Сбросить дату") }}
        </span>
        <svg-icon
          name="close"
          :color="colorIcon"
          :width="12"
          :height="12"
          :title="_('Сбросить дату')"
          style="margin-left: 10px"
        />
      </v-btn>
    </template>

    <div class="hm-date-range-picker-btn-clear__tooltip">
      {{ _(tooltip) }}
    </div>
  </v-tooltip>
</template>

<script>
import svgIcon from "@/components/icons/svgIcon";
import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

/**
 * Кнопка "Очистить" в календаре
 */
export default {
  name: "HmDateRangeFieldBtnClear",
  props: {
    tooltip: {
      type: String,
      default: "Сбросить дату",
    },
  },
  components: {
    svgIcon,
  },
  mixins: [VueMixinConfigColors],
  computed: {
    colorButtonBorders() {
      return this.getColor(configColors.buttonBorderLight);
    },
    colorIcon() {
      return this.getColor(configColors.buttonBorderDark);
    },
    colorText() {
      return this.getColor(configColors.textContrast);
    },
  },
};
</script>

<style lang="scss">
.hm-date-range-picker-btn-clear {
  .v-btn__content {
    font-size: 14px;
    line-height: 21px;
    text-transform: none;
    font-weight: normal;
  }
}
</style>
