<template>
  <div class="INFOBLOCK infoblock-leasing">
    <v-card-text>
      <v-layout row wrap>
        <v-flex xs12 sm6 md12>
          <v-select
            v-model="selectedResourse"
            :items="resourses"
            :label="label"
          />
        </v-flex>
        <v-flex sm12 md12 class="infoblock-leasing_chart-container">
          <hm-chart
            id="infoblock-leasing_chart"
            :url="url"
            :form-fields="formFields"
            :type="chart.type"
            :options="chart.options"
          />
        </v-flex>
      </v-layout>
    </v-card-text>
  </div>
</template>
<script>
import HmChart from "@/components/media/hm-chart/index";

export default {
  components: { HmChart },
  props: {
    url: {
      type: String,
      default: null
    },
    resourses: {
      type: Array,
      default: () => []
    },
    selected: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: "Использование ресурсов:"
    }
  },
  data() {
    return {
      selectedResourse: this.selected,
      chart: {
        type: "bar",
        options: {
          height: 400,
          margin: {
            left: 30,
            bottom: 80
          },
          tooltip: {
            show: true,
            content: "[[ value ]]"
          },
          axisX: {
            text: {
              rotate: true
            }
          },
          axisY: {
            ticksCount: 5
          }
        }
      },
      formFields: [
        { key: "key", value: "type" },
        { key: "value", value: this.selected }
      ]
    };
  },
  watch: {
    selectedResourse(v) {
      if (!v) return;
      this.formFields = [
        { key: "key", value: "type" },
        { key: "value", value: v }
      ];
    }
  }
};
</script>
