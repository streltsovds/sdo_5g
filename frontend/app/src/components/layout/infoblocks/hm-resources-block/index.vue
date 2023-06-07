<template>
  <div class="INFOBLOCK infoblock-resources-block">
    <v-card-text>
      <div class="infoblock-resources-block_filters">
        <v-layout row wrap>
          <v-flex sm12 md4>
            <v-select
              v-model="selectedType"
              :items="classifications"
              label="Классификация"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md4>
            <hm-date-picker
                    :label="datePickerFromLabel"
                    :value="datePickerFromValue"
                    name="infoblock-resources-block_from"
                    @input="beginDate = $event"
                    title="Дата создания ресурса"
            ></hm-date-picker>
          </v-flex>
          <v-flex sm12 md4>
            <hm-date-picker
                    :label="datePickerToLabel"
                    :value="datePickerToValue"
                    name="infoblock-resources-block_to"
                    @input="endDate = $event"
                    title="Дата создания ресурса"
            ></hm-date-picker>
          </v-flex>
          <v-flex sm12 md12>
            <hm-chart
              id="infoblock-resources-block_chart"
              :url="url"
              :form-fields="formFields"
              :type="chart.type"
              :options="chart.options"
            />
          </v-flex>
        </v-layout>
      </div>
    </v-card-text>
  </div>
</template>
<script>
import HmChart from "@/components/media/hm-chart/index";
import HmDatePicker from "../../../forms/hm-date-picker/index";
export default {
  components: { HmChart, HmDatePicker },
  props: {
    url: {
      type: String,
      default: null
    },
    typesClassification: {
      type: Object,
      default: () => {}
    },
    datePickerFromLabel: {
      type: String,
      default: "c"
    },
    datePickerFromValue: {
      type: String,
      default: null
    },
    classifier: {
      type: String,
      default: null
    },
    datePickerToLabel: {
      type: String,
      default: "по"
    },
    datePickerToValue: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedType: {},
      formFields: [],
      beginDate: null,
      endDate: null,
      chart: {
        type: "bar",
        options: {
          height: 312,
          margin: {
            left: 30,
            bottom: 40
          },
          tooltip: {
            show: true,
            content: "Количество ресурсов: [[ value ]]"
          },
          axisY: {
            ticksCount: 5
          }
        }
      }
    };
  },
  computed: {
    classifications: function() {
      const response = [];
      for (let prop in this.typesClassification) {
        if (!this.typesClassification.hasOwnProperty(prop)) continue;
        response.push({
          text: this.typesClassification[prop],
          value: prop
        });
      }

      return response;
    }
  },
  watch: {
    selectedType(v) {
      if (!v) return;

      this.formFields = [
        { key: "key", value: "classifier" },
        { key: "value", value: v }
      ];
    },
    beginDate(v) {
      if (!v) return;

      this.formFields = [
        { key: "key", value: "from" },
        { key: "value", value: v }
      ];
    },
    endDate(v) {
      if (!v) return;

      this.formFields = [
        { key: "key", value: "to" },
        { key: "value", value: v }
      ];
    }
  },
  created() {
    this.selectedType = String(this.classifier);
  }
};
</script>
<style lang="scss">
  .infoblock-resources-block_filters {
    .hm-form-element{
      margin: 0 10px 0 0;
    }
    .hm-form-element.hm-date-picker {
      margin-top: -4px;
    }
  }
</style>
