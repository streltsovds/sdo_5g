<template>
  <v-layout fill-height wrap class="INFOBLOCK infoblock-timesheet-block">
    <v-flex
      v-if="chart.data.length"
      xs12
      sm4
      md12
      class="hm-timesheet_chart-block"
    >
      <hm-chart
        id="hm-timesheet_chart"
        :data="chart.data"
        :type="chart.type"
        :options="chart.options"
        data-label="type"
        data-value="value"
      />
    </v-flex>
    <v-flex :sm8="chart.data && chart.data.length" xs12 md12>
      <list
              :actions="action_types"
              :items="combinedItems"
              @item-add="onItemAdd"
              @item-remove="onItemRemove"
      />
    </v-flex>
  </v-layout>
</template>

<script>
import axios from "axios";
import List from "./partials/list/index";
import HmChart from "@/components/media/hm-chart/index";

export default {
  components: {
    List,
    HmChart
  },
  props: {
    saveUrl: {
      type: String,
      default: () => false
    },
    loadUrl: {
      type: String,
      default: () => false
    },
    chartUrl: {
      type: String,
      default: () => false
    },
    deleteUrl: {
      type: String,
      default: () => false
    }
  },
  data() {
    return {
      items: [],
      action_types: [],
      fresh_items: [], // unused
      isLoading: false,
      chart: {
        data: [],
        type: "pie",
        options: {
          height: 200,
          legend: {
            show: true
          },
          tooltip: {
            show: true,
            content: "[[label]]: <span>[[value]] %</span>"
          },
          label: {
            outer: {
              show: true,
              value: {
                show: true,
                content: "[[value]] %"
              }
            }
          }
        }
      }
    };
  },
  computed: {
    combinedItems() {
      return [...this.items, ...this.fresh_items].sort(
        this.sortChronologically
      );
    }
  },
  created() {
    this.getData();
  },
  methods: {
    getData() {
      this.isLoading = true;
      axios
        .get(this.loadUrl)
        .then(response => response.data)
        .then(this.processResponse);
    },
    saveData() {
      let dataToSave = this.fresh_items.map(item => {
        return {
          description: item.description,
          action_type: item.type,
          begin_time: item.time.from,
          end_time: item.time.to
        };
      });
      this.isLoading = true;
      axios
              .post(this.saveUrl, dataToSave)
              .then(response => response.data)
              .then(data => {
                if (data && data.success) {
                  this.getData();
                } else {
                  this.isLoading = false;
                }
              });
    },
    deleteData(item) {
      let dataToDelete = {
        description: item.description,
        action_type: item.type,
        begin_time: item.time.from,
        end_time: item.time.to
      };
      this.isLoading = true;
      axios
              .post(this.deleteUrl, dataToDelete)
              .then(response => response.data)
              .then(data => {
                if (data && data.success) {
                  this.getData();
                } else {
                  this.isLoading = false;
                }
              });
    },
    onItemAdd(item) {
      this.fresh_items.push(item);
      this.saveData();
    },
    onItemRemove(item) {
      this.fresh_items = this.fresh_items.filter(
        () => !this.fresh_items.includes(item)
      );
      this.deleteData(item);
      this.load
    },
    processResponse(data) {
      if (typeof data === "object") {
        if (this.fresh_items.length) {
          this.fresh_items = [];
        }
        this.items = data.items;
        this.action_types = data.action_types;
        this.chart.data = data.chart_data;
        this.isLoading = false;
      }
    },
    sortChronologically(item_one, item_two) {
      let Afrom = item_one.time.from.split(":");
      let Bfrom = item_two.time.from.split(":");
      let [AfromHour, AfromMinute] = Afrom;
      let [BfromHour, BfromMinute] = Bfrom;

      if (AfromHour === BfromHour) {
        return parseInt(AfromMinute, 10) - parseInt(BfromMinute, 10);
      } else {
        return parseInt(AfromHour, 10) - parseInt(BfromHour, 10);
      }
    }
  }
};
</script>

<style lang="scss">
.infoblock-timesheet-block {
  .hm-timesheet_chart-block {
    &.sm4 {
      .module-chart_label-title {
        font-size: 11px;
      }
      .module-chart_label-value {
        font-size: 12px;
        /*transform: translate(0,0);*/
      }
      .module-chart_legends {
        li {
          font-size: 12px;
        }
        span {
          top: 1px;
        }
      }
    }
  }
}
</style>
