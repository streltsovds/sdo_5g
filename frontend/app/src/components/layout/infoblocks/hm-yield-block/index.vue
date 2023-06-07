<template>
  <div class="INFOBLOCK infoblock-yield-block">
    <v-card-text>
      <div class="infoblock-yield-block_filters">
        <v-layout row wrap>
          <v-flex xs12 sm6 md12>
            <v-select
              v-model="selectedType"
              :items="typesIndicators"
              label="Показатель"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md12>
            <hm-chart
              id="infoblock-yield-block_chart"
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
export default {
  components: { HmChart },
  props: {
    url: {
      type: String,
      default: null
    },
    type: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      typesIndicators: [
        { text: "Количество проведенных сессий", value: "trainings" },
        { text: "Количество прошедших обучение", value: "trainees" }
      ],
      formFields: [],
      selectedType: {},
      chart: {
        type: "area",
        options: {
          margin: {
            left: 30,
            bottom: 40
          },
          height: 312,
          tooltip: {
            show: true,
            content: ""
          },
          axisY: {
            ticksCount: 5
          },
          axisX: {
            text: {
              rotate: true
            }
          },
          area: {
            fill: "rgba(75, 149, 40, 0.5)",
            dots: {
              fill: "#4B9528"
            }
          }
        }
      }
    };
  },
  watch: {
    selectedType(type) {
      this.updateTooltip(type);

      this.formFields = [
        { key: "key", value: "type" },
        { key: "value", value: type }
      ];
    }
  },
  created() {
    this.selectedType = this.type;
  },
  methods: {
    updateTooltip(type) {
      let content = null;
      if (type === "trainings") {
        content =
          "Количество проведенных учебных сессий с начала года: [[ value ]]";
      } else if (type === "trainees") {
        content = "Количество прошедших обучения с начала года: [[value]]";
      }
      this.chart.options.tooltip.content = content;
    }
  }
};
</script>
<style lang="scss">
  .infoblock-yield-block_filters {
    .hm-form-element{
      margin: 0 10px 0 0;
    }
  }
</style>
