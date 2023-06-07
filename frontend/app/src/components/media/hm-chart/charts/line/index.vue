<template>
  <div ref="chart">
    <svg
      v-resize="resize"
      :width="mixinOptionsWidth"
      :height="mixinOptionsHeight"
    >
      <g
        class="module-chart_group"
        :style="
          !mixinOptions
            ? 0
            : {
              transform: `translate(${mixinOptions.getMargin().left}px, ${
                mixinOptions.getMargin().top
              }px)`
            }
        "
      >
        <g
          class="module-chart_x-axis"
          ref="xAxis"
          :style="{
            transform: `translate(0, ${mixinOptionsHeightContainer}px)`
          }"
        />
        <g class="module-chart_y-axis" ref="yAxis" />
        <path
          class="module-chart_line"
          v-if="mixinOptions"
          :d="linePath && linePath.length > 0 ? `M${linePath}` : null"
          :stroke="mixinOptions.getLine().stroke.fill"
          :stroke-width="mixinOptions.getLine().stroke.width"
        />
        <path
          class="module-chart_area"
          v-if="showArea && mixinOptions"
          :fill="mixinOptions.getArea().fill"
          :d="linePath && linePath.length > 0 ? `M${areaPath}` : null"
        />
        <g class="module-chart_line-dots">
          <text v-for="dot in dots"
                :key="dot.id"
                :x="dot.cx"
                :y="dot.cy - 15"
          > {{ dot.value }} </text>
          <circle
            class="module-chart_line-dot"
            v-for="dot in dots"
            :key="dot.id"
            :cx="dot.cx"
            :cy="dot.cy"
            :r="dot.r"
            :fill="dot.fill"
            v-on="
              dot.tooltip
                ? {
                  mouseover: $event =>
                    mixinTooltipMouseOver($event, dot.tooltip),
                  mouseout: mixinTooltipMouseOut
                }
                : {}
            "
          />
        </g>
      </g>
    </svg>
    <mixin-tooltip
      v-if="mixinOptions && mixinOptions.tooltipIsShow()"
      :show="mixinTooltip.show"
      :left="mixinTooltip.left"
      :top="mixinTooltip.top"
      :content="mixinTooltip.content"
    />
  </div>
</template>
<script>
import { wrap, options, tooltip } from "./../mixins";
import { scaleBand, scaleLinear, max, axisBottom, axisLeft, select } from "d3";

import TweenLite from "gsap/TweenLite";

export default {
  mixins: [wrap, options, tooltip],
  props: {
    data: {
      type: Array,
      default: () => []
    },
    options: {
      type: Object,
      default: () => {}
    },
    showArea: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isLoaded: false,
      x: null,
      y: null,
      formattedData: [],
      dotsCoordinate: []
    };
  },
  computed: {
    dots() {
      return this.dotsCoordinate
        .filter(d => d.enabled)
        .map(dot => JSON.parse(JSON.stringify(dot)));
    },
    linePath() {
      return this.dotsCoordinate.map(d => [d.cx, d.cy]);
    },
    areaPath() {
      let path = JSON.parse(JSON.stringify(this.linePath));

      if (!this.linePath[this.linePath.length - 1] || !this.linePath[0])
        return null;

      path.push(
        [this.linePath[this.linePath.length - 1][0], this.y(0)],
        [this.linePath[0][0], this.y(0)]
      );

      return path;
    }
  },

  watch: {
    data() {
      this.setFormattedData();
    },

    options: {
      handler: function() {
        this.setMixinOptions(this.options, this.$refs.chart);
      },
      deep: true
    },

    formattedData: {
      handler: function() {
        this.setCoordinate();
      },
      deep: true
    }
  },

  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.setMixinOptions(this.options, this.$refs.chart);

      this.x = scaleBand();
      this.y = scaleLinear();

      this.setRange();

      this.setFormattedData();

      this.isLoaded = true;
    },

    setRange() {
      this.x.range([0, this.mixinOptionsWidthContainer]);
      this.y.range([this.mixinOptionsHeightContainer, 0]);
    },

    setDomain() {
      this.x.domain(
        this.formattedData.filter(d => d.enabled).map(d => d.label)
      );
      this.y.domain([
        0,
        max(this.formattedData.filter(d => d.enabled), d => {
          if(d.value === 0) return 1
          else return d.value;
        })
      ]);
    },

    setFormattedData() {
      this.formattedData = this.data.map((d, i) => {
        let item = JSON.parse(JSON.stringify(d));
        item.id = i;
        item.enabled = d.enabled === undefined ? true : d.enabled;
        return item;
      });
    },

    // copy-past
    setAxis() {
      if (!this.x || !this.y) return;

      let xAxis = axisBottom(this.x);
      let yAxis = axisLeft(this.y);

      if (!this.mixinOptions) return;

      yAxis.ticks(this.mixinOptions.getAxisY().ticksCount, "f");

      let xAxisItem = select(this.$refs.xAxis)
        .call(xAxis)
        .selectAll(".tick text")
        .classed("text-vertical", () => {
          return this.mixinOptions.getAxisX().text.rotate;
        });

      if (this.mixinOptions.getAxisX().text.wrap) {
        xAxisItem.call(this.wrap, this.x.bandwidth());
      }

      select(this.$refs.yAxis).call(yAxis);
    },

    setCoordinate() {
      this.setDomain();
      this.setAxis();

      this.setCoordinateDots();
    },

    setCoordinateDots() {
      let dotsRadius = this.mixinOptions.getLine().dots.radius;
      let dotsFill = this.mixinOptions.getLine().dots.fill;

      this.dotsCoordinate = this.formattedData.map(d => {
        let item = JSON.parse(JSON.stringify(d));
        let prevItem = this.dots && this.dots[d.id] ? this.dots[d.id] : null;

        item.cx = prevItem
          ? prevItem.cx
          : this.x(d.label) + this.x.bandwidth() / 2;
        item.cy = prevItem ? prevItem.cy : this.y(0);
        item.r = dotsRadius;
        item.fill = dotsFill;

        if (this.mixinOptions.tooltipIsShow()) {
          item.tooltip = this.mixinOptions.getTooltip(item);
        }
        return item;
      });

      this.dotsCoordinate.forEach(this.setDotCoordinate);
    },

    setDotCoordinate(d, key) {
      TweenLite.to(this.dotsCoordinate[key], 1, {
        cx: this.x(d.label) + this.x.bandwidth() / 2,
        cy: this.y(d.value)
      });
    },

    resize() {
      if (!this.isLoaded) return;

      this.updateMixinOptionsWidth();
      this.setRange();
      this.setAxis();
      this.setCoordinate();
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  .module-chart_line {
    fill: none;
  }
  .module-chart_line-dots {
    cursor: pointer;
  }
}
</style>
