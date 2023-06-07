<template>
  <div ref="block" class="INFOBLOCK infoblock-chart-comparison-block">
    <div class="infoblock-chart-comparison-block_filter pb-0">
      <tree-select
        :orgstructureFilterData="orgstructureTree"
        v-on:changeDepartment="getDepartment"
        :initObject="selectedUnit ? selectedUnit : null"
      />
      <div class="infoblock-chart-comparison-block_filter-wrapper">
        <hm-date-picker
          :label="datePickerFromLabel"
          :disabled="isLoading"
          :loading="isLoading"
          :value="datePickerFromValue"
          name="infoblock-chart-comparison-block_from"
          @input="
            beginDate = $event;
            loadData();
          "
        ></hm-date-picker>
        <hm-date-picker
          :label="datePickerToLabel"
          :disabled="isLoading"
          :loading="isLoading"
          :value="datePickerToValue"
          name="infoblock-chart-comparison-block_to"
          @input="
            endDate = $event;
            loadData();
          "
        ></hm-date-picker>
      </div>
    </div>
    <v-flex sm12 md12 class="infoblock-chart-comparison-block_chart-container">
      <hm-chart
        v-if="chartData.length > 0"
        :data="chartData"
        type="apexbar"
        :options="chartOptions"
        :key="componentKey"
      />
       <hm-empty v-else empty-type="full" />
    </v-flex>
  </div>
</template>
<script>
import HmDatePicker from "../../../forms/hm-date-picker/index";
import HmChart from "@/components/media/hm-chart/index";
import TreeSelect from "@/components/controls/hm-tree-select";
import { mapActions } from "vuex";
import HmEmpty from "@/components/helpers/hm-empty"
export default {
  components: { HmDatePicker, HmChart, TreeSelect, HmEmpty },
  props: {
    datePickerFromLabel: {
      type: String,
      default: "За период c"
    },
    datePickerFromValue: {
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
    },
    url: {
      type: String,
      default: null
    },
    orgstructureTree: {
      type: Array,
      default: null
    },
    selectedUnit: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      componentKey: 0,
      isLoading: false,
      beginDate: this.datePickerFromValue,
      endDate: this.datePickerToValue,
      department: this.selectedUnit ? this.selectedUnit.key : null,
      chartData: [],
      chartOptions: {
        dataLabel: "title",
        height: 400,
        width: 550,
        type: "apexbar",
        horizontal: true,
        title: 'заголовок',
        id: "comparison",
        colors: ["rgba(0, 227, 150, 0.85)", "rgba(200, 49, 74, 0.85)"],
      }
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    forceRerender() {
      this.componentKey += 1;
    },
    loadData() {
      if (this.isLoading) return;
      this.isLoading = true;
      const data = new FormData();
      data.set("from", this.beginDate);
      data.set("to", this.endDate);
      data.set("soid", this.department)

      this.$axios
        .post(this.url, data)
        .then(r => {
          if (r.status !== 200) throw new Error();
          if(r.data.length > 0) {
            this.chartData = r.data;
            this.getHeightChart();
            this.getWidthChart();
            this.forceRerender();
          } else {
            this.chartData = []
            this.forceRerender();
          }
        })
        .catch(e => {
          console.error(e);
        })
        .finally(() => (this.isLoading = false));
    },
    getHeightChart() {
      this.chartOptions.height = (50 * this.chartData.length) + 85;
    },
    getWidthChart() {
      this.chartOptions.width = this.$refs.block.offsetWidth - 10;
    },
    getDepartment(value) {
      this.department = value;
      this.loadData();
    }
  }
};
</script>
<style lang="scss">
  .infoblock-chart-comparison-block {
    .hm-date-picker__activator .v-text-field {
      margin-top: 0;
    }
    .apexcharts-toolbar {
      display: none;
    }
    .apexcharts-canvas {
      margin-top: -28px;

    }
    .apexcharts-yaxistooltip {
      display: none;
    }
    &_chart-container {
      max-height: 405px;
      overflow: auto;
      &::-webkit-scrollbar {
        width: 4px;
        height: 4px;
      }
      &::-webkit-scrollbar-thumb {
        background: #706e6e;
        border-radius: 4px;
      }
      &::-webkit-scrollbar-thumb:hover {
        background: #70889E;
      }
      &::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        border-radius: 4px;
      }
    }
  }
  .infoblock-chart-comparison-block_filter {
    display: flex;
    justify-content: space-between;
    & .select-treeview__button {
      margin-right: 36px !important;
      margin-top: 6px;
    }
    &-wrapper {
      display: flex;
      align-items: center;
    }
    .hm-form-element {
      margin: 0 10px 0 0;
    }
  }
  @media(max-width: 768px) {
    .infoblock-chart-comparison-block {
      padding: 0 16px;
      &_filter {
        flex-wrap: wrap;
        &-wrapper {
          width: 100%;
          margin-top: 12px;
          & .hm-date-picker {
            width: 50%;
          }
        }
      }
    }
  }
</style>
