<template>
  <div class="hm-form-element hm-date-picker">
    <v-menu
      :value="_menu"
      @input="dirtyMenu = $event"
      :close-on-content-click="false"
      :nudge-right="40"
      transition="scale-transition"
      offset-y
      max-width="290px"
      min-width="290px"
      :disabled="disabled"
      :left="left"
    >
      <template v-slot:activator="{ on }">
        <div
          class="hm-date-picker__activator"
          style="display: flex; align-items: center;"
          v-on="on"
        >
          <icon-calendar
            :color="_colorIcon"
            :title="label"
            style="margin-right: 8px; flex-shrink: 0;"
          />

          <v-text-field
            :value="_formattedValueInput"
            :label="label"
            :hint="description"
            :error-messages="errorsArray"
            :error="errorsExist"
            :disabled="disabled"
            :loading="loading"
            persistent-hint
            @change="dateUpdated"
            type="date"
            min="1900-01-01"
            max="3000-12-31"
            clearable
            :class="{ required }"
            @click:clear="dateUpdated('')"
          ></v-text-field>
        </div>
      </template>

      <v-date-picker
        :value="_parsedValue"
        no-title
        locale="ru"
        first-day-of-week="1"
        @input="dirtyMenu = false"
        @change="dateUpdated"
        style="width: 600px;"
        class="hm-date-picker__calendar"
      ></v-date-picker>
    </v-menu>
    <input type="hidden" :name="name" :value="_formattedValue" />
  </div>
</template>
<script>

import MixinState from "./../mixins/MixinState";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import iconCalendar from "@/components/icons/items/iconCalendar";
import configColors from "@/utilities/configColors";

export default {
  name: "HmDatePicker",
  components: {
    iconCalendar,
  },
  mixins: [MixinState, VueMixinConfigColors],
  props: {
    name: {
      type: String,
      default: null
    },
    value: {
      type: String,
      default: ""
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
      default: false
    },
    label: {
      type: String,
      default: "Дата",
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
      type: [Number, String],
      default: null,
    },
    menu: {
      type: Boolean,
      default: false,
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
      // временное состояние компонента.
      // Чтобы компонет можно было использовать в обычных формах.
      // Обнуляется, если в props пришло новое value
      dirtyValue: null,
      dirtyErrorsData: null,
      dirtyMenu: null,
      maxYear: 9999,
      minYear: 1000
    };
  },
  computed: {
    _menu() {
      return this.dirtyMenu === null ? this.menu : this.dirtyMenu;
    },
    _value() {
      return this.dirtyValue || this.value;
    },
    _errorsData() {
      return this.dirtyErrorsData ? this.dirtyErrorsData : this.errorsData;
    },
    errorsExist() {
      for (let key in this._errorsData) {
        if (this._errorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      for (let key in this._errorsData) {
        if (this._errorsData.hasOwnProperty(key))
          rules.push(this._errorsData[key]);
      }
      return rules;
    },
    _colorIcon() {
      return this.getColor(configColors.primarySaturated);
    },
    _parsedValue() {
      return this.parseDate(this._value);
    },
    _formattedValue() {
      return this.formatDate(this._parsedValue);
    },
    _formattedValueInput() {
      return this._parsedValue;
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
    dateUpdated(newDate) {
      this.clearErrors();

      let newFormattedDate = this.formatDate(newDate);
      this.dirtyValue = newFormattedDate;
      this.$emit("input", newFormattedDate);
    },
    formatDate(date) {
      if (!date) {
        // В противном случае по "крестику" дата не удаляется из инпута 'birthdate'
        // и на бэк уходит та же, что пришла и сохраняется в базу
        this.value = '';
        return null;
        }

      const [year, month, day] = date.split("-");
      return `${day}.${month}.${this.calibrateYear(year)}`;
    },
    parseDate(date) {
      if (!date) return null;

      const [day, month, year] = date.split(".");
      return `${this.calibrateYear(year)}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
    },
    calibrateYear(year) {
      return Math.min(this.maxYear, Math.max(this.minYear, year));
    }
  },
};
</script>
<style lang="scss">
.hm-date-picker {
  & input[type=date]::-webkit-calendar-picker-indicator {
    display: none;
  }
  &__calendar {
    .v-date-picker-table {
      height: auto !important;
      padding-bottom: 36px;
    }
  }
  .v-input__slot {
    margin-bottom: 8px !important;
  }
}
</style>
