<template>
  <div class="INFOBLOCK infoblock-kbase-block">
    <hm-chart
      id="infoblock-kbase-block_chart"
      :url="url"
      :type="type"
      :options="options"
    />
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
    }
  },
  data() {
    return {
      type: "area",
      options: {
        margin: {
          top: 100,
          bottom: 90,
          left: 40
        },
        height: 410,
        tooltip: {
          show: true,
          content: "Целевое покрытие, %: [[value]]"
        },
        axisY: {
          ticksCount: 5
        },
        axisX: {
          text: {
            rotate: true,
            wrap: false
          }
        },
        area: {
          fill: "#4588cd",
          dots: {
            fill: "#4B9528"
          }
        }
      }
    };
  }
};
</script>
