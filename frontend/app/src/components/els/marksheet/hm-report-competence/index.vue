<template>
  <div>
    <div class="report-chart">
      <h3 class="report-chart__title">
        {{ tableData.title }}
      </h3>
      <div class="report-chart__wrap report-chart__wrap--inline">
        <div class="report-chart__chart">
          <hm-chart
            :data="tableData.competence"
            :options="tableData.options"
            data-value="count"
            type="bar"
          />
        </div>
        <div class="report-chart__table">
          <table class="report-chart__table">
            <thead>
              <tr>
                <th>Компетенция</th>
                <template v-for="(item, key) in tableData.competence">
                  <th :key="key">
                    {{ item.title }}
                  </th>
                </template>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Плановое значение</td>
                <template v-for="(item, key) in tableData.competence">
                  <td :key="key">
                    {{ item.count }}
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- <div class="footnotes" v-if="!!tableOptions['footnote']">
          <p>*<span>{{ tableOptions['footnote'] }}</span></p>
        </div> -->
      </div>
    </div>
  </div>
</template>


<script>
// import GradeType from "@/components/els/marksheet/lib/GradeType";
import HmChart from "@/components/media/hm-chart/index";
// import getQuestReportChartOptions from './getQuestOptions';

export default {
  name: "HmReportCompetence",
  components: {
    HmChart
  },
  props: ["data"],
  // props: {
    // quest: {
    //   type: Object,
    //   default: () => {},
    // },
    // id:{
    //   type: [Number,String],
    //   default: null
    // }
  // },
  data() {
    return {
      tableData: {
        title: "Диаграмма 1. Требования к уровню развития компетенций (профиль успешности)",
        competence: [
          {
            title: "Сергей",
            count: "3"
          },
          {
            title: "Стрессоустойчивость",
            count: "4"
          }
        ],
        options: {
          axisX: {
            text: {
              rotate: true
            }
          },
          axisY: {
            ticksCount: 5
          },
          height: 400,
          width: 400,
          tooltip: {
            content: "Плановое значение: [[value]]",
            show: true
          }
        }
      }
    }
  },
  mounted() {
    console.log(data)
  },
  computed : {
    // reportChartData(){
    //   return getQuestReportChartOptions(this.quest, this.id);
    // },
    // tableOptions(){
    //   return this.reportChartData.tableOptions;
    // },
    // graphs(){
    //   return this.reportChartData.graphs;
    // },
    // data(){
    //   return Object.values(this.quest.variants);
    // },
    // isInline(){
    //   return +this.tableOptions.showTable === 1;
    // }
  },
};
</script>

<style lang="scss">
.report-chart{
  & .module-chart .module-chart_x-axis .tick .text-vertical {
    transform: none;
  }
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

