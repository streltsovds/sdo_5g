<template>
  <div ref="block" class="INFOBLOCK infoblock-chart-subject-progress-block">
    <div class="infoblock-chart-subject-progress-block_filter pb-0">
        <tree-select
          :orgstructureFilterData="orgstructureTree"
          v-on:changeDepartment="getDepartment"
          :initObject="selectedUnit ? selectedUnit : null"
        />
        <v-select
          :items="jobProfiles"
          @input="getProfiles"
          item-text="name"
          item-value="id"
          label="выбор должности"
          class="hm-form-element"
          clearable
          :value="selectedProfiles"
        />
    </div>
    <v-flex sm12 md12 class="infoblock-chart-subject-progress-block_chart-container">
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
import HmChart from "@/components/media/hm-chart/index";
import TreeSelect from "@/components/controls/hm-tree-select";
import { mapActions } from "vuex";
import HmEmpty from "@/components/helpers/hm-empty"
export default {
  components: { HmChart, TreeSelect, HmEmpty },
  props: {
    url: {
      type: String,
      default: null
    },
    orgstructureTree: {
      type: Array,
      default: null
    },
    jobProfilesData: {
      type: Array,
      default: null
    },
    selectedProfile: {
      type: Number,
      default: null
    },
    selectedUnit: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      jobProfiles: this.jobProfilesData,
      selectedProfiles: this.selectedProfile || null,
      componentKey: 0,
      isLoading: false,
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
        customTooltip: this.customTooltip,
        maxValue: 90
      },
      tooltipData: []
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
    customTooltip({series, seriesIndex, dataPointIndex, w}) {
      const tooltipData = this.tooltipData;
      return `<div class="apex-tooltip">
        <span class="apex-tooltip__text">Прошли курс ${tooltipData[dataPointIndex].count} из ${tooltipData[dataPointIndex].all} работников</span>
        </div>`
    },
    loadData() {
      if (this.isLoading) return;
      this.isLoading = true;

      const url = `${this.url}${this.department || this.department === 0 ? '/soid/' + this.department : ''}${this.selectedProfiles ? '/job_profile/' + this.selectedProfiles : ''}`;
      this.$axios
        .get(url)
        .then(r => {
          if (r.status !== 200) throw new Error();
          if(r.data.chartData) {
            this.chartData = r.data.chartData;
            this.getHeightChart();
            this.getWidthChart();
            this.forceRerender();
          } else {
            this.chartData = [];
            this.forceRerender();
          }
          if(r.data.tooltipData) this.tooltipData = r.data.tooltipData;
          else this.tooltipData = [];
        })
        .catch(e => {
          console.error(e);
        })
        .finally(() => (this.isLoading = false));
    },
    getHeightChart() {
      this.chartOptions.height = (25 * this.chartData.length) + 70;
    },
    getWidthChart() {
      this.chartOptions.width = this.$refs.block.offsetWidth - 10;
    },
    getProfiles(value) {
      this.selectedProfiles = value;
      this.loadData();
    },
    getDepartment(value) {
      this.department = value;
      this.loadData();
    }
  }
};
</script>
<style lang="scss">
  .infoblock-chart-subject-progress-block {
    .apexcharts-toolbar {
      display: none;
    }
    .apexcharts-canvas {
      margin-top: -28px;

    }
    .apexcharts-yaxistooltip {
      display: none;
    }
    .apex-tooltip {
      padding: 10px;
    }
    &_chart-container {
      max-height: 405px;
      overflow: auto;
      &::-webkit-scrollbar {
        width: 4px;
        height: 4px;
      }
      &::-webkit-scrollbar-thumb {
        background-color: #706e6e;
        border-radius: 4px;
      }
      &::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        border-radius: 4px;
      }
      &::-webkit-scrollbar-thumb:hover {
        background: #70889E;
      }
    }
  }
  .infoblock-chart-subject-progress-block_filter {
    display: flex;
    justify-content: space-between;
    & .select-treeview__button {
      margin-right: 36px !important;
      margin-top: 6px;
    }
    .hm-form-element {
      margin: 0 10px 0 0;
    }
  }
  @media(max-width: 768px) {
    .infoblock-chart-subject-progress-block {
      padding: 0 16px;
      &_filter {
        flex-direction: column;
        & .hm-form-element {
          margin-top: 12px;
        }
      }
    }
  }
</style>
