<template>
  <div class="INFOBLOCK infoblock-activity-dev-block">
    <v-card-text>
      <div class="infoblock-activity-dev-block_filters">
        <v-layout row wrap>
          <v-flex sm12 md6>
            <v-select
              v-model="selectedActivityDistribution"
              :items="activityDistributions"
              label="Распределение активности"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md6>
            <v-select
              v-model="selectedPeriod"
              :items="periods"
              label="Период"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md12 class="infoblock-activity-dev-block_chart-container">
            <hm-chart
              id="infoblock-activity-dev-block_chart"
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
    activityDistributions: {
      type: Array,
      default: () => []
    },
    activityDistribution: {
      type: String,
      default: null
    },
    periods: {
      type: Array,
      default: () => []
    },
    period: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedActivityDistribution: {},
      selectedPeriod: {},
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
            content: "Среднее количество пользователей в это время: [[ value ]]"
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
      formFields: []
    };
  },
  watch: {
    selectedActivityDistribution(v) {
      if (!v) return;
      this.formFields = [
        { key: "key", value: "type" },
        { key: "value", value: v }
      ];
    },
    selectedPeriod(v) {
      if (!v) return;

      this.formFields = [
        { key: "key", value: "period" },
        { key: "value", value: v }
      ];
    }
  },
  created() {
    const defaultActivityDistribution = this.activityDistributions[0].value;
    this.selectedActivityDistribution =
      this.activityDistribution || defaultActivityDistribution;

    const defaultPeriod = this.periods[0].value;
    const selectedPeriod = this.periods.find(
      item =>
        item.value !== undefined &&
        item.value.toString() === this.period.toString()
    );
    this.selectedPeriod = selectedPeriod || defaultPeriod;
  }
};
</script>
<style lang="scss">
  .infoblock-activity-dev-block_filters {
    .hm-form-element{
      margin: 0 10px 0 0;
    }
  }
</style>
