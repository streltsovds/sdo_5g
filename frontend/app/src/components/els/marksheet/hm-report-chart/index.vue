<template>
  <div>
    <div class="report-chart">
      <h3 class="report-chart__title">
        {{ quest['title'] }}
      </h3>
      <div :class="isInline ? 'report-chart__wrap report-chart__wrap--inline' : 'report-chart__wrap'">
        <div class="report-chart__chart">
          <hm-chart
            :data="Object.values(quest['variants'])"
            :options="reportChartData.chartOptions"
            data-value="count"
          />
        </div>
        <div class="report-chart__table" v-if="!!tableOptions.showTable">
          <table class="report-chart__table">
            <thead>
              <tr>
                <th>#</th>
                <th>{{ tableOptions.dataTitle }}</th>
                <template v-for="(graph, _id) in graphs">
                  <th :key="_id" v-if="!tableOptions.hideData">
                    {{ graph.legend }}
                  </th>
                  <th :key="_id + 111" v-if="tableOptions.procentColumn">
                    {{ tableOptions['procentColumnName']? tableOptions['procentColumnName'] + ' (%)' : graph.legend }}
                  </th>
                </template>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, num) in data" :key="num">
                <td>{{ num + 1 }}</td>
                <template v-for="(cell, key) in row">
                  <td v-if="(!tableOptions.hideData) || (key == 'title')" :key="key">
                    {{ !cell ? '-' : cell }}
                  </td>
                  <td v-if="tableOptions['procentColumn'] && (key !== 'title')" :key="key">
                    {{ cell === false ? '-' : (tableOptions['totalValue']) ? Math.round(cell/tableOptions['totalValue'] * 100) + '%' : '-' }}
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="footnotes" v-if="!!tableOptions['footnote']">
          <p>*<span>{{ tableOptions['footnote'] }}</span></p>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import GradeType from "@/components/els/marksheet/lib/GradeType";
import HmChart from "@/components/media/hm-chart/index";
import getQuestReportChartOptions from './getQuestOptions';

export default {
  name: "HmReportChart",
  components: {
    HmChart
  },
  props: {
    quest: {
      type: Object,
      default: () => {},
    },
    id:{
      type: [Number,String],
      default: null
    }
  },
  computed : {
    reportChartData(){
      return getQuestReportChartOptions(this.quest, this.id);
    },
    tableOptions(){
      return this.reportChartData.tableOptions;
    },
    graphs(){
      return this.reportChartData.graphs;
    },
    data(){
      return Object.values(this.quest.variants);
    },
    isInline(){
      return +this.tableOptions.showTable === 1;
    }
  },
};
</script>

<style lang="scss">
.report-chart{
  &__title{

  }
  &__table{

  }
  &__chart{
    margin-right: 25px;
  }
  &__table{
    margin-bottom: 0!important;
  }
  &__wrap{
    &--inline{
      display: flex;
      align-items: center;
    }
  }

}
</style>

