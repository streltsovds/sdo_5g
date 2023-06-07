<template>
  <div
    ref="chart"
    :class="{ 'module-chart_radar-xs': $vuetify.breakpoint.xsOnly }"
  >
    <svg
      v-if="isLoaded"
      v-resize="resize"
      :width="mixinOptionsWidth"
      :height="mixinOptionsHeight"
    >
      <g
        :transform="
          `translate(${mixinOptions.getMargin().left}, ${
            mixinOptions.getMargin().top
          })`
        "
      >
        <!-- Axis -->
        <g class="module-chart_radar-axis">
          <g class="module-chart_radar-axis" v-if="axis.length">
            <line
              class="module-chart_radar-axis-line"
              v-for="(axisItem, key) in axis"
              :key="key"
              :x1="axisItem.x1"
              :y1="axisItem.y1"
              :x2="axisItem.x2"
              :y2="axisItem.y2"
            />
          </g>
          <g class="module-chart_radar-levels" v-if="levels.length">
            <g
              class="module-chart_radar-level"
              v-for="(level, key) in levels"
              :key="key"
            >
              <line
                class="module-chart_radar-line"
                v-for="(line, key2) in level"
                :key="key2"
                :x1="line.x1"
                :y1="line.y1"
                :x2="line.x2"
                :y2="line.y2"
                :transform="line.transform"
              />
            </g>
          </g>
        </g>
        <!-- Chart -->
        <g
          class="module-chart_radar-chart"
          :class="{ 'module-chart_radar-chart__hover': radarHover !== false }"
        >
          <template v-if="polygons.length">
            <polygon
              class="module-chart_radar-chart-polygon"
              v-for="(polygon, key) in polygons"
              :key="`polygon${key}`"
              :points="polygon.points"
              :stroke="polygon.color"
              :fill="polygon.color"
              :stroke-width="polygon.width"
              :class="{
                'module-chart_radar-chart-item__hover': radarHover === key
              }"
              @mouseover="radarHover = key"
              @mouseout="radarHover = false"
            />
          </template>
          <template v-if="circlesRadars.length">
            <g
              class="module-chart_radar-chart-circles"
              v-for="(circlesRadar, key) in circlesRadars"
              :key="key"
            >
              <circle
                class="module-chart_radar-chart-circle"
                v-for="(circle, key2) in circlesRadar"
                :key="key2"
                :cx="circle.x"
                :cy="circle.y"
                :stroke="circle.color"
                :fill="circle.color"
                :r="circle.radius"
                :class="{
                  'module-chart_radar-chart-item__hover': radarHover === key
                }"
                @mouseover="
                  $event => {
                    mixinTooltipMouseOver($event, circle.text);
                    radarHover = key;
                  }
                "
                @mouseout="
                  mixinTooltipMouseOut();
                  radarHover = false;
                "
              >
                <title>{{ circle.text }}</title>
              </circle>
            </g>
          </template>
        </g>
        <!-- labels -->
        <g class="module-chart_radar-labels">
          <template v-if="axisAllLabel.length">
            <g
              class="module-chart_radar-axis-label"
              v-for="(label, key) in axisAllLabel"
              :key="key"
              :transform="`translate(${label.x}, ${label.y})`"
            >
              <text
                class="module-chart_radar-axis-label-item"
                :transform="label.transform"
                y="0"
                dy="0"
              >
                {{ label.text }}
              </text>
            </g>
          </template>
          <g class="module-chart_radar-axis-y-label" v-if="levelsLabel.length">
            <text
              v-for="(label, key) in levelsLabel"
              :key="key"
              :x="label.x"
              :y="label.y"
              :transform="label.transform"
              dy="1.5em"
            >
              {{ label.text }}
            </text>
          </g>
        </g>
      </g>
    </svg>
    <mixin-chart-legends
      v-if="
        mixinOptions &&
          mixinOptions.options.legend &&
          mixinOptions.options.legend.show === true
      "
      :legends="mixinLegend"
      @toggleEnabled="toggleEnabled($event, formattedData)"
    />
    <mixin-tooltip
      v-if="mixinTooltip.show"
      :show="mixinTooltip.show"
      :left="mixinTooltip.left"
      :top="mixinTooltip.top"
      :content="mixinTooltip.content"
    />
  </div>
</template>
<script>
import { wrap, colors, options, legend, tooltip } from "./../mixins";
import { max, selectAll } from "d3";

export default {
  mixins: [wrap, colors, options, legend, tooltip],
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
  data() {
    return {
      isLoaded: false,
      allAxisTitle: [],
      radius: null,
      radians: 2 * Math.PI,
      levels: [],
      levelsLabel: [],
      countLevels: 0,
      axis: [],
      axisAllLabel: [],
      polygons: [],
      radarHover: false,
      circlesRadars: [],
      formattedData: [],
      radarOptions: {},
      factor: 0
    };
  },
  computed: {
    nameRadars() {
      // получить ключи
      let nameRadars = [];
      if (!this.data.chart && !this.data.chart.length) return [];

      let firstAxisItems = this.data.chart[0];
      for (let nameRadar in firstAxisItems) {
        if (!firstAxisItems.hasOwnProperty(nameRadar)) continue;
        if (nameRadar === "title") continue;
        nameRadars.push(nameRadar);
      }
      return nameRadars;
    },
    enabledFormattedData() {
      return this.formattedData.filter(data => data.enabled);
    },
    axisCount() {
      return this.allAxisTitle.length;
    },
    factorLegend() {
      if (!this.factor) return 0;
      return this.factor - 0.15;
    },
    width() {
      return (
        this.mixinOptionsWidth -
        this.mixinOptions.getMargin().left -
        this.mixinOptions.getMargin().right
      );
    },
    height() {
      return (
        this.mixinOptionsHeight -
        this.mixinOptions.getMargin().top -
        this.mixinOptions.getMargin().bottom
      );
    }
  },
  watch: {
    data() {
      this.setFormattedData();
    },
    enabledFormattedData() {
      this.buildRadars();
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.setFormattedData();
      if (!this.formattedData.length) return;

      this.setMixinOptions(this.options);

      this.radarOptions = this.mixinOptions.getRadar();
      this.factor = this.radarOptions.factor;

      this.setMaxValue();
      this.allAxisTitle = this.formattedData[0].data.map(
        radarItem => radarItem.axis
      );

      this.countLevels = this.mixinOptions.getAxisY().ticksCount;

      this.radius = this.factor * Math.min(this.width / 2, this.height / 2);

      this.buildChart();

      this.isLoaded = true;
    },

    setFormattedData() {
      if (!this.nameRadars.length) return (this.formattedData = []);

      let formattedData = [];
      let colors = this.getColors(this.nameRadars.length);

      this.nameRadars.forEach((nameRadar, key) => {
        let radarChartData = [];
        this.data.chart.forEach(axisItems => {
          if (axisItems.hasOwnProperty(nameRadar)) {
            radarChartData.push({
              axis: axisItems.title,
              value: axisItems[nameRadar]
            });
          }
        });

        formattedData.push({
          id: key,
          label: this.data.legends[nameRadar].legend,
          data: radarChartData,
          color: colors(key),
          enabled: true
        });
      });

      const reducer = (acc, curValue) => parseFloat(curValue.value) + acc;

      this.formattedData = formattedData
        .filter(radar => radar.data.reduce(reducer, 0) > 0) // фильтруем area, в которых нет данных
        .sort((a, b) => {
          // сортируем area в порядке убывания, для корректного выделения при наведении мышкой на polygon
          let sumA = a.data.reduce(reducer, 0);
          let sumB = b.data.reduce(reducer, 0);
          if (sumA < sumB) return 1;
          if (sumA > sumB) return -1;
          return 0;
        });
    },
    setMaxValue() {
      this.radarOptions.maxValue = Math.max(
        this.radarOptions.maxValue,
        max(this.formattedData, i => max(i.data.map(o => o.value)))
      );
    },

    buildChart() {
      this.setLegend(this.formattedData);
      this.setDataForLevels();
      this.setDataForAxis();
      this.buildRadars();
    },

    setDataForLevels() {
      let dataForLevels = [];
      this.levelsLabel = [];

      for (let level = 0; level < this.countLevels - 1; level++) {
        this.levelsLabel.push(this.setDataForLevelLabel(level));
        dataForLevels[level] = [];
        this.allAxisTitle.forEach((axis, key) => {
          dataForLevels[level][key] = {
            x1:
              (this.width / 2) *
              (1 -
                (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
                this.factor *
                Math.sin((key * this.radians) / this.axisCount)),
            y1:
              (this.height / 2) *
              (1 -
                (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
                this.factor *
                Math.cos((key * this.radians) / this.axisCount)),
            x2:
              (this.width / 2) *
              (1 -
                (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
                this.factor *
                Math.sin(((key + 1) * this.radians) / this.axisCount)),
            y2:
              (this.height / 2) *
              (1 -
                (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
                this.factor *
                Math.cos(((key + 1) * this.radians) / this.axisCount))
          };
        });
      }
      this.levels = dataForLevels;
    },

    setDataForLevelLabel(level) {
      return {
        x:
          (this.width / 2) *
          (1 -
            (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
            this.factor *
            Math.sin(0)),
        y:
          (this.height / 2) *
          (1 -
            (parseFloat(Math.max(level, 0)) / this.radarOptions.maxValue) *
            this.factor *
            Math.cos(0)),
        text: level
      };
    },

    setDataForAxis() {
      let axis = [];
      let axisAllLabel = [];
      this.allAxisTitle.forEach((axisTitle, key) => {
        axisAllLabel.push({
          x:
            (this.width / 2) *
            (1 -
              this.factorLegend *
              Math.sin((key * this.radians) / this.axisCount)) -
            60 * Math.sin((key * this.radians) / this.axisCount),
          y:
            (this.height / 2) *
            (1 -
              this.factorLegend *
              Math.cos((key * this.radians) / this.axisCount)) -
            20 * Math.cos((key * this.radians) / this.axisCount),
          transform: `translate(0, -10)`,
          text: axisTitle
        });
        axis.push({
          x1: this.width / 2,
          y1: this.height / 2,
          x2:
            (this.width / 2) *
            (1 - this.factor * Math.sin((key * this.radians) / this.axisCount)),
          y2:
            (this.height / 2) *
            (1 - this.factor * Math.cos((key * this.radians) / this.axisCount))
        });
      });
      this.axis = axis;
      this.axisAllLabel = axisAllLabel;
      this.$nextTick(() => {
        if (
          this.$el.querySelectorAll(".module-chart_radar-axis-label-item tspan")
            .length > 0
        )
          return;
        this.wrap(selectAll(".module-chart_radar-axis-label-item"), 50);
      });
    },

    buildRadars() {
      this.polygons = [];
      this.circlesRadars = [];

      this.enabledFormattedData.forEach((radarItems, key) => {
        let polygonsDataValues = [];
        let circlesRadarsDataValues = [];

        radarItems.data.forEach((radarItem, radarItemIndex) => {
          let coordinate = this.getCoordinateForRadar(
            radarItem,
            radarItemIndex
          );
          polygonsDataValues.push({
            ...coordinate,
            width: this.radarOptions.stroke.width
          });
          circlesRadarsDataValues.push({
            ...coordinate,
            color: radarItems.color,
            radius: this.radarOptions.dots.radius
          });
        });

        polygonsDataValues.push(polygonsDataValues[0]);

        let pointsStr = "";
        polygonsDataValues.forEach(dataValue => {
          pointsStr = `${pointsStr}${dataValue.x},${dataValue.y} `;
        });
        this.polygons.push({
          points: pointsStr,
          color: radarItems.color
        });

        this.circlesRadars[key] = circlesRadarsDataValues;
      });
    },

    getCoordinateForRadar(radarItem, radarItemIndex) {
      return {
        x:
          (this.width / 2) *
          (1 -
            (parseFloat(Math.max(radarItem.value, 0)) /
              this.radarOptions.maxValue) *
            this.factor *
            Math.sin((radarItemIndex * this.radians) / this.axisCount)),
        y:
          (this.height / 2) *
          (1 -
            (parseFloat(Math.max(radarItem.value, 0)) /
              this.radarOptions.maxValue) *
            this.factor *
            Math.cos((radarItemIndex * this.radians) / this.axisCount)),
        text: radarItem.value,
        color: radarItem.color
      };
    },

    resize() {
      if (!this.isLoaded) return;
      this.isLoaded = false;
      this.$nextTick(() => {
        this.buildChart();
      });
      let margin = this.mixinOptions.getMargin();

      this.updateMixinOptionsWidth();
      this.mixinOptionsHeight =
        this.mixinOptionsWidth -
        margin.left -
        margin.right +
        margin.top +
        margin.bottom;

      this.isLoaded = true;
    }
  }
};
</script>
<style lang="scss">
.module-chart_radar-levels {
  .module-chart_radar-line {
    stroke: black;
    /*stroke-opacity: 0.75;*/
    stroke-width: 0.3px;
  }
}
.module-chart_radar-axis-y-label {
  text {
    font-family: Roboto, sans-serif;
    font-size: 10px;
  }
}
.module-chart_radar-axis-line {
  stroke: grey;
  stroke-width: 1px;
}
.module-chart_radar-axis-label {
  background: rgba(white, 0.5);
  text {
    font-family: Roboto, sans-serif;
    font-size: 11px;
    text-anchor: middle;
  }
}
.module-chart_radar-chart-polygon {
  stroke-width: 1px;
  fill-opacity: 0.2;
  transition: fill-opacity 0.3s;
  cursor: pointer;
}
.module-chart_radar-chart__hover {
  polygon {
    fill-opacity: 0.1;
    &:hover {
      fill-opacity: 0.7;
    }
  }
}
.module-chart_radar-chart-item__hover {
  fill-opacity: 0.7 !important;
}
.module-chart_radar-chart-circle {
  stroke-width: 1px;
  fill-opacity: 0.9;
}
.module-chart_radar-xs {
  .module-chart_radar-axis-label {
    text {
      font-size: 9px;
    }
  }
}
</style>
