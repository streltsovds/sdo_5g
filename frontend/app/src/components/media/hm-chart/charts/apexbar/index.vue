<template>
  <div :class="{ 'module-chart_radar-xs': $vuetify.breakpoint.xsOnly }"

  >
    <vue-apex-charts :options="chartOptions"
                     :series="series"
                     :width="chartOptions.chart.width"
                     :height="chartOptions.chart.height"
    />
  </div>
</template>
<script>
import VueApexCharts from 'vue-apexcharts'
import options from '../../../../forms/hm-tiny-mce/utilities/options';
export default {
  components: { VueApexCharts },
  props: {
    data: {
      type: [Object, Array],
      default: () => {}
    },
    options: {
      type: Object,
      default: () => {}
    }
  },
  data: () => ({
    series: [
      {
        name: 'Series 1',
        data: [80, 50, 30, 40, 100, 20],
      },
      {
        name: 'Series 2',
        data: [90, 60, 90, 60, 60, 50],
      }
    ],
    chartOptions: {
      chart: {
        height: 400,
        width: 550,
        type: 'bar',
      },
      plotOptions: {
        bar: {
          horizontal: true,
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        followCursor: true
      },
      xaxis: {
        categories: ['January', 'February', 'March', 'April', 'May', 'June']
      }
    },
  }),
  created(){
    // console.log(Object.entries(this.data.legends))

    console.log(this.data);
    let dataKeys = Object.keys(this.data[0]).filter(k => k !== 'title' && k !== 'color');

    this.series = [...dataKeys.map((itm, index) => {
      return {
        name: itm,
        data: [...this.data.map(item => item[itm])]
      }
    })];


    let colors = [...this.data.filter((item) => item.hasOwnProperty('color')).map((item) => item.color)];
    if(colors.length > 0) {
      this.chartOptions.colors = colors;
    }
    if(this.options.colors){
      this.chartOptions.colors = this.options.colors;
    }
    this.chartOptions = {
      ...this.chartOptions,
      chart: {
        toolbar: {
          show: false
        },
        height: this.options.height,
        width: this.options.width,
        type: 'bar',
      },
      plotOptions: {
        bar: {
          horizontal: this.options.horizontal,
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        custom: this.options.customTooltip,
        followCursor: true
      },
      yaxis: {
        forceNiceScale: true,
        max: this.options.maxValue,
        min: 0
      },
      xaxis: {
        categories: [...this.data.map((item, index) => item.title)],
      }
    }
  }
}
</script>

<style>

</style>
