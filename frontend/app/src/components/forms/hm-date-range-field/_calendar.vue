<template>
  <v-card class="hm-date-range-picker-calendar">
    <div
      v-if="caption"
      class="hm-date-range-picker-calendar__caption"
      :style="{ color: colorCaption }"
    >
      {{ _(caption) }}
    </div>

    <v-style-head-once>
      .hm-date-range-picker-calendar .v-date-picker-header {
        background-color: {{ colorMonthBackground }};
      }
    </v-style-head-once>

    <v-date-picker
      :value="visibleRangeValue"
      no-title
      locale="ru"
      first-day-of-week="1"
      :weekday-format="getDayOfWeekLabel"
      range
      v-bind="$attrs"
      v-on="$listeners"
    />

    <div class="hm-date-range-picker-calendar__actions">
      <hm-date-range-field-btn-clear
        v-if="showClearButton"
        :tooltip="clearTooltip"
        @click="$emit('clear')"
      />
    </div>
  </v-card>
</template>

<script>
import VStyleHeadOnce from "@/components/helpers/v-style-head-once";
import HmDateRangeFieldBtnClear from "./_btnClear";
import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import hexToRgba from "hex-to-rgba";

const daysOfWeekLabels = ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"];

export default {
  name: "HmDateRangeFieldCalendar",
  components: {
    VStyleHeadOnce,
    HmDateRangeFieldBtnClear,
  },
  mixins: [VueMixinConfigColors],
  props: {
    caption: {
      type: String,
      default: null,
    },
    visibleRangeValue: {
      type: Array,
      default: () => {
        return {};
      },
    },
    clearTooltip: {
      type: String,
      default: null,
    },
    showClearButton: {
      type: null,
      default: true,
    },
  },
  computed: {
    colorCaption() {
      return this.getColor(configColors.textLight);
    },
    colorMonthBackground() {
      return hexToRgba(this.themeColors.primary, 0.1);
    },
  },
  methods: {
    getDayOfWeekLabel(date) {
      let i = new Date(date).getDay(date);
      return daysOfWeekLabels[i];
    },
  },
};
</script>

<style lang="scss">
/* карточка с календарём при нажатии на поле */
.hm-date-range-picker-calendar {
  .v-picker.v-card {
    box-shadow: none !important;
  }

  & .v-picker__body {
    background: inherit !important;
  }

  &__actions {
    padding: 16px;

    .hm-date-range-picker-btn-clear {
      width: 100%;
    }
  }

  &__caption {
    padding: 12px 16px;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02rem;
  }

  .v-date-picker-header {
    padding: 0;
    margin: 4px 16px;
    border-radius: 5px;

    &__value button {
      font-weight: 500;
    }
  }

  .v-date-picker-table {
    height: auto;

    th,
    td,
    td .v-btn {
      font-weight: normal !important;
      font-size: 14px !important;
    }
  }
}
</style>
