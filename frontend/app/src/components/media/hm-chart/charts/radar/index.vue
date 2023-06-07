<template>
  <div :class="{ 'module-chart_radar-xs': $vuetify.breakpoint.xsOnly }" :style="{height: chartOptions.chart.height + 'px', width: chartOptions.chart.width + 'px' }">
    <vue-apex-charts :options="chartOptions"
                     :series="series"
                     :height="chartOptions.chart.height"
    />
  </div>
</template>
<script>
import VueApexCharts from 'vue-apexcharts'
export default {
  components: { VueApexCharts },
  props: {
    data: {
      type: Object,
      default: () => {}
    },
    options: {
      type: Object,
      default: () => {}
    }
  },
  data: () => ({
    series: [{
      name: 'Series 1',
      data: [80, 50, 30, 40, 100, 20],
    }],
    chartOptions: {
      chart: {
        height: 350,
        type: 'radar',
      },
      xaxis: {
        categories: ['January', 'February', 'March', 'April', 'May', 'June']
      }
    },
  }),
  created(){
    // console.log(Object.entries(this.data.legends))

    this.series = [...Object.entries(this.data.legends).map(([key, val]) => {
      return {
        name: val.legend,
        data: [...this.data.chart.map(c => c[key])]
      }
    })]

    this.chartOptions = {
      ...this.chartOptions,
      chart: {
        toolbar: {
          show: false
        },
        height: this.options.height,
        width: this.options.width,
        type: 'radar',
      },
      colors: [...Object.values(this.data.legends).map(l => l.color || "")],
      yaxis: {
        forceNiceScale: true,
        max: this.options.maxValue,
        min: 0
      },
      xaxis: {
        categories: [...this.data.chart.map((item, index) => {
          if(item.title) return item.title
          else  return index + 1
        })],
      }
    }
  }
}
</script>

<style>

</style>
