<template>
  <div class="INFOBLOCK infoblock-top-subjects">
    <v-card-text>
      <hm-chart
        v-if="chart.data"
        id="infoblock-top-subjects_chart"
        :url="url"
        :type="chart.type"
        :options="chart.options"
        data-value="column-1"
        data-label="category"
      ></hm-chart>
      <hm-empty v-else>
        {{ _('Нет данных для отображения') }}
      </hm-empty>
    </v-card-text>
  </div>
</template>

<script>
  import axios from "axios";
  import HmChart from "@/components/media/hm-chart/index";
  import HmEmpty from "@/components/helpers/hm-empty";

  export default {
    components: {
      HmChart,
      HmEmpty
    },
    props: {
      url: {
        type: String,
        default: null
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
            height: 308,
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
    },
    created() {
      this.getData();
    },
    methods: {
      getData() {
        this.isLoading = true;
        axios
          .get(this.url)
          .then(response => response.data)
          .then(this.processResponse);
      },
      processResponse(data) {
        if (typeof data === "object") {
          if (this.fresh_items.length) {
            this.fresh_items = [];
          }
          this.items = data.items;
          this.action_types = data.action_types;
          this.chart.data = data.data;
          this.isLoading = false;
        }
      }
    }
  };
</script>
