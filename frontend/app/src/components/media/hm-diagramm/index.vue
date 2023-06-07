<template>
    <canvas ref="canvasDiagramm"></canvas>
</template>

<script>
export default {
  name: 'hm-diagramm',
  props: {
    chartData: {
      type: [Number,String],
      default: 0
    },
    colors: {
      type:Object,
      default: () => {
        return {
          standard:'#D4E3FB',
          shaded: '#4a90e2'
        }
      }
    },
    size: {
      type: Object,
      default: () => {
        return {
          width: 75,
          height: 75,
        }
      }
    }
  },
  data() {
      return {
          canvas:null,
          ctx:null,
          totalValue: 100,
          text: {
              size:48,
              family:'Roboto,sans-serif!important'
          }
      }
  },
  methods: {
    initCanvas() {
        this.$refs.canvasDiagramm.width = this.size.width;
        this.$refs.canvasDiagramm.height = this.size.height;
        this.ctx = this.$refs.canvasDiagramm.getContext("2d");
    },
    //метод рисования основного круга
    draw() {
      this.ctx.beginPath();
      this.drawPieSlice(
          this.size.width / 2,
          this.size.height / 2,
          this.size.width / 2,
          0,
          2 * Math.PI,
          this.colors.standard
      );
    },
    //метод рисующий заполненую часть
    drawpart() {
        this.drawPieSlice(
            this.size.width / 2,
            this.size.height / 2,
            this.size.width / 2,
            0,
            2 * Math.PI * this.chartData / this.totalValue,
            this.colors.shaded
        );
    },
    //метод рисования круга центрально, для пустоты
    drawEmpty() {
        this.drawPieSlice(
            this.size.width / 2,
            this.size.height / 2,
            this.size.width / 2 - 4,
            0,
            2 * Math.PI,
            'white'
        );
    },
    //метод рисущий фигуру
    drawPieSlice(centerX, centerY, radius, startAngle, endAngle, color ){
      this.ctx.fillStyle = color;
      this.ctx.beginPath();
      this.ctx.moveTo(centerX,centerY);
      this.ctx.arc(centerX, centerY, radius, startAngle, endAngle);
      this.ctx.closePath();
      this.ctx.fill();
    },
  },
  mounted() {
    this.initCanvas();
    this.draw();
    this.drawpart();
    this.drawEmpty();
  }
}
</script>

<style scoped>

</style>
