<template>
  <v-card class="demo-hm-form-components pa-4">
    <h3>hm-date-picker</h3>

    <h4>v-model</h4>
    <v-row>
      <v-col>
        <hm-date-picker
          v-model="date"
          :attribs="{ label: 'Тест выбора даты' }"
        />
      </v-col>
      <v-col>
        <pre>
          date = {{ JSON.stringify(date) }}
        </pre>
      </v-col>
    </v-row>

    <h4>separate event</h4>
    <v-row>
      <v-col>
        <hm-date-picker
          :value="date2"
          @input="onDatePickerSeparate"
          label="Тест выбора даты 2"
        />
      </v-col>
      <v-col>
        <pre>
          date2 = {{ JSON.stringify(date2) }}
        </pre>
      </v-col>
    </v-row>

    <h4>dirtyValue</h4>
    <v-row>
      <v-col>
        <hm-date-picker
          :value="date3"
          label="Тест выбора даты 3"
        />
      </v-col>
      <v-col>
        <pre>
          date3 = {{ JSON.stringify(date3) }}
        </pre>
      </v-col>
    </v-row>

    <h3>hm-date-range-field</h3>
    <v-row>
      <v-col>
        <hm-date-range-field
          v-model="dateRange"
          label="Тест выбора диапазона дат"
        />
      </v-col>
      <v-col>
        <pre>
          dateRange = {{ JSON.stringify(dateRange) }}
        </pre>
      </v-col>
    </v-row>

    <h3>hm-switch-checkmark</h3>
    <v-toolbar dense class="pt-3">
      <v-checkbox
        v-model="switchCheckmarkLabelLeftSide"
        label="Метка слева"
      ></v-checkbox>
    </v-toolbar>
    <v-row>
      <v-col>
        <hm-switch-checkmark
          v-model="switchCheckmark"
          label="Тест переключателя"
          :label-left-side="switchCheckmarkLabelLeftSide"
        />
      </v-col>
      <v-col>
        <pre>
          switchCheckmark = {{ JSON.stringify(switchCheckmark) }}
        </pre>
      </v-col>
    </v-row>

    <h2>Vuetify: нативные компоненты</h2>

    <h3>v-datepicker</h3>
    <h4>range</h4>
    <v-row>
      <v-col>
        <v-date-picker
          v-model="dateRange2"
          no-title
          locale="ru"
          first-day-of-week="1"
          range
          full-width
        ></v-date-picker>
      </v-col>
      <v-col>
        <pre>
          dateRange2 = {{ JSON.stringify(dateRange2) }}
        </pre>
      </v-col>
    </v-row>

  </v-card>
</template>

<script>
import HmDatePicker from "@/components/forms/hm-date-picker";
import HmDateRangeField from "@/components/forms/hm-date-range-field";
import HmSwitchCheckmark from "@/components/controls/hm-switch-checkmark";
// import HmToggleButton from "../hm-toggle-button";

export default {
  name: "DemoHmFormComponents",
  components: {
    HmDatePicker,
    HmDateRangeField,
    HmSwitchCheckmark,
    // HmToggleButton,
  },
  data: function() {
    return {
      date: "",
      date2: "",
      date3: "10.10.2014",
      dateRange: null,
      dateRange2: [],
      switchCheckmark: false,
      switchCheckmarkLabelLeftSide: false,
    };
  },
  methods: {
    onDatePickerSeparate(value) {
      this.date2 = value;
      alert(value);
    },
  },
};
</script>
