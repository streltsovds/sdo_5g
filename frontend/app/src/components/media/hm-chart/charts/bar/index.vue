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
            }"
      >
        <g
          class="module-chart_x-axis"
          ref="xAxis"
          :style="{
            transform: `translate(0, ${mixinOptionsHeightContainer}px)`
          }"
        />
        <g class="module-chart_y-axis" ref="yAxis" />
        <g class="module-chart_bars">
          <template v-for="d in bars">
            <g v-if="Array.isArray(d)" :key="d.id">
              <rect
                class="module-chart_bar"
                v-for="dItem in d"
                :key="dItem.id"
                :x="10"
                :y="0"
                :width="d.width"
                :height="d.height"
                :fill="d.color"
                v-on="
                  d.tooltip
                    ? {
                      mouseover: $event =>
                        mixinTooltipMouseOver($event, d.tooltip),
                      mouseout: mixinTooltipMouseOut
                    }
                    : {}
                "
              />
            </g>
            <rect
              class="module-chart_bar"
              v-if="!Array.isArray(d)"
              :key="d.id"
              :x="d.x"
              :y="d.y"
              :width="d.width"
              :height="d.height"
              :fill="d.color"
              v-on="
                d.tooltip
                  ? {
                    mouseover: $event =>
                      mixinTooltipMouseOver($event, d.tooltip),
                    mouseout: mixinTooltipMouseOut
                  }
                  : {}"
            />


          </template>
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
      v-if="mixinOptions && mixinTooltip.show"
      :show="mixinTooltip.show"
      :left="mixinTooltip.left"
      :top="mixinTooltip.top"
      :content="mixinTooltip.content"
    />
  </div>
</template>
<script>
import { wrap, colors, options, legend, tooltip } from "./../mixins";
import { scaleBand, scaleLinear, max, axisBottom, axisLeft, select } from "d3";
import TweenLite from "gsap/TweenLite";

export default {
  mixins: [wrap, colors, options, legend, tooltip],
  props: {
    data: {
      type: Array,
      default: () => []
    },
    options: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      isLoaded: false,
      x: null,
      y: null,
      colors: null,
      barsCoordinate: [],
      formattedData: []
    };
  },
  computed: {
    bars() {
      return this.barsCoordinate
        .filter(d => d.enabled)
        .map(bar => JSON.parse(JSON.stringify(bar)));
    }
  },

  watch: {
    data() {
      this.setFormattedData();
    },
    formattedData: {
      handler: function() {
        this.setBarsCoordinate();
      },
      deep: true
    },
    barsCoordinate() {
      this.setLegend(this.barsCoordinate);
    }
  },

  mounted() {
    this.init();
  },

  methods: {
    init() {
      this.setMixinOptions(this.options);

      this.x = scaleBand()
        .padding(0.1)
        .round(true);

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
          let maxValue = Array.isArray(d.value) ? Math.max(...d.value) : d.value;
          if(maxValue === 0) return 1
          else return maxValue;
        })
      ]);

    },

    setFormattedData() {
      this.colors = this.getColors(this.data.length);

      this.formattedData = this.data.map((d, i) => {
        let item = JSON.parse(JSON.stringify(d));
        item.isSomeCols = Array.isArray(item.value);
        item.id = i;
        item.enabled = d.enabled === undefined ? true : d.enabled;
        item.color = d.hasOwnProperty('color') ? d.color : this.colors(i);
        return item;
      });
    },

    setAxis() {
      if (!this.x || !this.y) return;

      let xAxis = axisBottom(this.x);
      let yAxis = axisLeft(this.y);

      if (!this.mixinOptions) return;

      yAxis.ticks(this.mixinOptions.getAxisY().ticksCount, "f");

      select(this.$refs.xAxis)
        .call(xAxis)
        .selectAll(".tick text")
        .classed("text-vertical", () => {
          return this.mixinOptions.getAxisX().text.rotate;
        })
        .call(this.wrap, this.x.bandwidth());

      select(this.$refs.yAxis).call(yAxis);
    },

    setBarsCoordinate() {
      this.setDomain();
      this.setAxis();

      this.barsCoordinate = this.formattedData.map(d => {
        let data = JSON.parse(JSON.stringify(d));
        let prevBar = this.bars && this.bars[d.id] ? this.bars[d.id] : null;
        let isSomeCols = data.isSomeCols;
        let newData = isSomeCols? [] : {...data};

        if(isSomeCols){
          data.value.forEach((item, index) => {
            newData[index] = {
              ...data,
              x : prevBar ? prevBar.x : this.x(d.label),
              width :  prevBar ? prevBar.width : this.x.bandwidth(),
              y : prevBar ? prevBar.y : this.y(0),
              height : prevBar ? prevBar.height : 0,
              tooltip: this.mixinOptions.getTooltip(item)
            }
          })
        }else{
          newData.x = prevBar ? prevBar.x : this.x(d.label);
          newData.width = prevBar ? prevBar.width : this.x.bandwidth();
          newData.y = prevBar ? prevBar.y : this.y(0);
          newData.height = prevBar ? prevBar.height : 0;

          if (this.mixinOptions.tooltipIsShow()) {
            newData.tooltip = this.mixinOptions.getTooltip(data);
          }
        }



        return newData;
      });

      this.barsCoordinate.forEach(this.setBarCoordinate);
    },

    setBarCoordinate(d, key) {
      let maxValue = Array.isArray(d.value) ? Math.max(...d.value) : d.value;
      TweenLite.to(this.barsCoordinate[key], 1, {
        x: this.x(d.label),
        width: this.x.bandwidth(),
        y: this.y(maxValue),
        height: this.mixinOptionsHeightContainer - this.y(maxValue)
      });
    },

    resize() {
      if (!this.isLoaded) return;

      this.updateMixinOptionsWidth();
      this.setRange();
      this.setAxis();
      this.setBarsCoordinate();
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  .module-chart_bars {
    &-move {
      transition: transform 1s;
    }
  }
  .module-chart_bar {
    opacity: 0.8;
    cursor: pointer;
    transition: opacity 0.3s;
    &:hover {
      opacity: 1;
    }
    &.hide {
      opacity: 0;
      transition: opacity 0.3s;
    }
    &.delay {
      transition-delay: 0.8s;
    }
  }
}
</style>
