<template>
  <chart-line :data="data" :options="options" :show-area="true" />
</template>
<script>
import ChartLine from "./../line";
export default {
  components: { ChartLine },
  props: {
    data: {
      type: Array,
      default: () => []
    },
    options: {
      type: Object,
      default: () => {}
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  .module-chart_area {
    opacity: 0.5;
  }
}
</style>
