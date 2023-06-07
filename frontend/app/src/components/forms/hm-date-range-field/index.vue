<template>
  <div class="hm-form-element hm-date-range-picker" style="display: flex; ">
    <!-- picker FROM -->
    <v-menu
      :value="currentMenu == 1"
      :close-on-content-click="false"
      :nudge-right="32"
      :nudge-top="20"
      transition="scale-transition"
      offset-y
      max-width="290px"
      min-width="290px"
      :disabled="disabled"
      :left="left"
      @input="dirtyMenu = $event ? 1 : 0"
    >
      <template v-slot:activator="{ on }">
        <div
          class="hm-date-range-picker__from-activator"
          style="display: flex; align-items: flex-end;"
          v-on="on"
        >
          <icon-calendar
            :color="colorIcon"
            :title="label"
            style="margin-right: 8px; flex-shrink: 0;"
          />

          <v-text-field
            class="hm-date-range-picker__first"
            :value="formattedValueFromForTextField"
            :label="label"
            :hint="description"
            :error-messages="errorsArray"
            :error="errorsExist"
            :disabled="disabled"
            :loading="loading"
            persistent-hint
            readonly
            :class="{ required }"
          />
        </div>
      </template>

      <hm-date-range-field-calendar
        caption="Выбрать начальную дату"
        clear-tooltip="Сбросить начальную дату"
        :show-clear-button="parsedArrayValue[0]"
        :visible-range-value="datePickerRangeValue"
        @clear="valueIndexUpdateTo(0, null)"
        @input="dateFromUpdated"
      />
    </v-menu>

    <!-- picker TO -->

    <v-menu
      :value="currentMenu == 2"
      :close-on-content-click="false"
      :nudge-right="12"
      :nudge-top="20"
      transition="scale-transition"
      offset-y
      max-width="290px"
      min-width="290px"
      :disabled="disabled"
      :left="left"
      @input="dirtyMenu = $event ? 2 : 0"
    >
      <template v-slot:activator="{ on }">
        <v-text-field
          class="hm-date-range-picker__to-activator"
          style="display: flex; align-items: center;"
          :value="formattedValueToForTextField"
          :hint="description"
          :disabled="disabled"
          :loading="loading"
          persistent-hint
          readonly
          clearable
          :class="{ required }"
          v-on="on"
          @click:clear="valueUpdateTo('')"
        />
      </template>

      <hm-date-range-field-calendar
        caption="Выбрать конечную дату"
        clear-tooltip="Сбросить конечную дату"
        :show-clear-button="parsedArrayValue[1]"
        :visible-range-value="datePickerRangeValue"
        @clear="valueIndexUpdateTo(1, null)"
        @input="dateToUpdated"
      />
    </v-menu>

    <input type="hidden" :name="name" :value="formattedValue" />
  </div>
</template>

<script>
import isEmpty from "lodash/isEmpty";
import moment from "moment";

import MixinState from "./../mixins/MixinState";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import iconCalendar from "@/components/icons/items/iconCalendar";
import configColors from "@/utilities/configColors";
import HmDateRangeFieldCalendar from "./_calendar";

export default {
  name: "HmDateRangeField",
  components: {
    iconCalendar,
    HmDateRangeFieldCalendar,
  },
  mixins: [MixinState, VueMixinConfigColors],
  props: {
    name: {
      type: String,
      default: null,
    },
    /** formatted: "15.01.2019,31.03.2019" */
    value: {
      type: String,
      default: null,
    },
    errors: {
      type: Object,
      default: function() {
        return {};
      },
    },
    // Align the component towards the left
    left: {
      type: Boolean,
      default: false,
    },
    label: {
      type: String,
      default: "Диапазон",
    },
    description: {
      type: String,
      default: null,
    },
    required: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    formId: {
      type: Number || String,
      default: null,
    },
    /* 0 - закрыто, 1 - дата 1, 2 - дата 2 */
    menu: {
      type: Number,
      default: 0,
    },
    errorsData: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  data() {
    return {
      // dirty* - временное состояние компонента, отличное от props.
      // Чтобы компонет можно было использовать в обычных формах.
      /**
       * formatted: "15.01.2019, 31.03.2019"
       * Обнуляется, если в props пришло новое value (см. watch)
       **/
      dirtyValue: null,
      dirtyErrorsData: null,
      dirtyMenu: null,
    };
  },
  computed: {
    currentMenu() {
      return this.dirtyMenu === null ? this.menu : this.dirtyMenu;
    },
    /** "15.01.2019, 31.03.2019" */
    currentValue() {
      return this.dirtyValue || this.value;
    },
    /** ["15.01.2019", "31.03.2019"] */
    parsedArrayValue() {
      let valStr = this.currentValue;
      if (isEmpty(valStr)) {
        valStr = "";
      }
      let val = valStr.split(",");

      let dateFrom = isEmpty(val[0]) ? null : this.parseDate(val[0]);
      let dateTo   = isEmpty(val[1]) ? null : this.parseDate(val[1]);

      // if (dateFrom && dateTo && dateFrom > dateTo) {
      //    [dateFrom, dateTo] = [dateTo, dateFrom];
      // };

      return [dateFrom, dateTo];
    },
    /** formatted: "15.01.2019,31.03.2019" */
    formattedValue() {
      // let ar = [ this.formatDate(this.parsedValue[0]) ];
      //
      // let to = this.formatDate(this.parsedValue[1]);
      //
      // if (to) {
      //   ar.push(to);
      // }
      // return ar.join(",");

      return this.formatValue(this.parsedArrayValue);
    },
    formattedValueFromForTextField() {
      return this.formatDate(this.parsedArrayValue[0]);
    },
    formattedValueToForTextField() {
      let from = this.formatDate(this.parsedArrayValue[0]);
      let to = this.formatDate(this.parsedArrayValue[1]);
      if (to) {
        return to;
      }
      if (from) {
        // чтобы появилась кнопка "крестик" для сброса
        return " ";
      }
      return "";
    },
    /** для отображения в range date picker */
    datePickerRangeValue() {
      let r = [...this.parsedArrayValue];

      if (!r[0] && !r[1]) {
        return [];
      }
      if (!r[0]) {
        r[0] = moment(r[1]).startOf("month").format("YYYY-MM-DD");
        return r;
      }

      if (!r[1]) {
        r[1] = moment(r[0]).endOf("month").format("YYYY-MM-DD");
        return r;
      }

      return r;
    },
    currentErrorsData() {
      return this.dirtyErrorsData ? this.dirtyErrorsData : this.errorsData;
    },
    errorsExist() {
      for (let key in this.currentErrorsData) {
        if (this.currentErrorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      let errData = this.currentErrorsData;
      for (let key in errData) {
        if (errData.hasOwnProperty(key))
          rules.push(errData[key]);
      }
      return rules;
    },
    colorIcon() {
      return this.getColor(configColors.primarySaturated);
    },
  },
  watch: {
    value() {
      this.dirtyValue = null;
    },
    menu() {
      this.dirtyMenu = null;
    },
    errorsData() {
      this.dirtyErrorsData = null;
    },
  },
  beforeMount() {

  },
  methods: {
    clearErrors() {
      this.dirtyErrorsData = {};
    },

    valueUpdateTo(newFormattedValue) {
      this.dirtyValue = newFormattedValue;
      this.$emit("input", newFormattedValue);
    },

    valueIndexUpdateTo(index, newParsedValue) {
      if (!this.dirtyValue) {
        this.dirtyValue = this.value || "";
      }

      let newParsed = [...this.parsedArrayValue];

      newParsed[index] = newParsedValue;
      if (newParsed[0] && newParsed[1] && newParsed[1] < newParsed[0]) {
        newParsed = newParsed.reverse();
      }

      let newFormattedValue = this.formatValue(newParsed);

      this.valueUpdateTo(newFormattedValue);
    },

    dateFromUpdated(newValue) {
      // @input="dirtyMenu = 2"
      if (!(newValue[0])) {
        return;
      }

      let newFromValue = newValue[0];
      this.valueIndexUpdateTo(0, newFromValue);

      this.dirtyMenu = 2;
    },

    dateToUpdated(newValue) {
      // @input="dirtyMenu = false"
      if (!(newValue[0])) {
        return;
      }

      let newToValue = newValue[0];
      this.valueIndexUpdateTo(1, newToValue);

      this.dirtyMenu = 0;
    },

    dateUpdated(newDate) {
      this.clearErrors();

      let newFormattedDate = this.formatDate(newDate);

      // this.mixinStateUpdate("dateFormatted", newFormattedDate);

      this.dirtyValue = newFormattedDate;

      this.$emit("input", newFormattedDate);
      // this.$emit("update", newFormattedDate);
    },

    formatDate(date) {
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${day}.${month}.${year}`;
    },

    formatValue(dateAr) {
      let ar = [ this.formatDate(dateAr[0]) ];

      let to = this.formatDate(dateAr[1]);

      if (to) {
        ar.push(to);
      }
      return ar.join(",");
    },

    parseDate(date) {
      if (!date) return null;

      const [day, month, year] = date.split(".");
      return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
    },
  },
};
</script>
<style lang="scss">
/*
.hm-date-picker {
  .v-input__slot {
    margin-bottom: 8px !important;
  }
}
*/

.hm-date-range-picker {
  width: 220px;

  &__from-activator, &__to-activator {
    flex-basis: 50%;
    .v-input__slot {
      margin-bottom: 0;
    }

    .v-text-field__details {
      display: none;
    }
  }

  &__from-activator {
    .v-text-field {
      margin-top: 16px !important;
    }
    .v-text-field__slot .v-label {
      /* эмуляция .v-label--active */
      max-width: 300%;
      transform: translateY(-18px) scale(.75)
    }
  }

  &__to-activator {
    position: relative;
    margin-top: 16px !important;

    &:before {
      content: "-";
      display: inline-block;
      position: absolute;
      font-size: 21px;
      line-height: 28px;
      top: 12px;
      left: 0;
    }

    input {
      text-align: right;
    }
  }

  .v-input input {
    font-size: 14px;
    letter-spacing: 0.02rem;
  }

  /* крестик очистки поля ввода */
  .v-input__append-inner {
    padding: 0;
  }
}
</style>
