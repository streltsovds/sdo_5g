<template>
  <div class="INFOBLOCK infoblock-claims">
    <v-card-text>
      <div class="infoblock-claims-block_filters">
        <v-layout row wrap>
          <v-flex sm12 md6>
            <v-select
                    v-model="selectedPeriod"
                    :items="periods"
                    label="Период"
                    class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md6>
            <p>
              <slot name="totalLabel">Поступило заявок:</slot>
              {{ total }},
              <slot name="undoneLabel">не обработано:</slot>
              <a slot="activator" :href="urlClaimsList">{{ undone }}</a>
            </p>
          </v-flex>
          <v-flex sm12>
            <hm-chart
                    id="infoblock-claims_chart"
                    :url="url"
                    :form-fields="formFields"
                    :type="chart.type"
                    :options="chart.options"
                    @loadData="loadData"
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
      urlClaimsList: {
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
        selectedPeriod: {},
        formFields: [],
        total: null,
        undone: null,
        chart: {
          type: "bar",
          options: {
            margin: {
              left: 40,
              bottom: 40
            },
            height: 312,
            axisY: {
              ticksCount: 7
            }
          }
        }
      };
    },
    watch: {
      selectedPeriod(value) {
        if (!value) return;
        this.formFields = [{ key: "period", value }];
      }
    },
    created() {
      const selectedPeriod = this.periods.find(
              item =>
                      item.value !== undefined &&
                      item.value.toString() === this.period.toString()
      );
      this.selectedPeriod = selectedPeriod || this.periods[0].value;
    },
    methods: {
      loadData({ total, undone }) {
        this.total = total;
        this.undone = undone;
      }
    }
  };
</script>
<style lang="scss">
  .infoblock-claims-block_filters {
    .hm-form-element{
      margin: 0 10px 0 0;
    }
    p {
      margin: 24px 0 0 12px;
    }
  }
</style>
