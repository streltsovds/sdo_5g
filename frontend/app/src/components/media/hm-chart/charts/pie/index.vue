<template>
  <div ref="chart">
    <svg
      v-resize="resize"
      :width="mixinOptionsWidth"
      :height="mixinOptionsHeight"
    >
      <g
        class="module-chart_pie"
        :style="
          !mixinOptions
            ? 0
            : {
              transform: `translate(${mixinOptionsWidth /
                2}px, ${mixinOptionsHeight / 2}px)`
            }
        "
      >
        <g class="module-chart_slices" ref="slices">
          <g
            class="module-chart_pie-slice"
            v-for="slice in slices"
            :key="slice.data.id"
          >
            <path
              :d="arc(slice)"
              :fill="slice.data.color"
              v-on="
                slice.tooltip
                  ? {
                    mouseover: $event =>
                      mixinTooltipMouseOver($event, slice.tooltip),
                    mouseout: mixinTooltipMouseOut
                  }
                  : {}
              "
            />
            <text
              class="module-chart_pie-text"
              v-if="slice.text"
              :transform="slice.text.transform"
              text-anchor="middle"
            >
              {{ slice.text.value }}
            </text>
          </g>
        </g>
        <g class="module-chart_lines" ref="lines" v-if="lines.length">
          <polyline
            class="module-chart_line"
            v-for="line in lines"
            :key="line.id"
            :points="line.points"
            :class="{ hide: !line.data.enabled }"
          />
        </g>
        <g class="module-chart_labels" ref="labels" v-if="labels.length">
          <g
            class="module-chart_label"
            v-for="label in labels"
            :key="label.data.id"
            :class="{ hide: !label.data.enabled }"
            :transform="label.transform"
          >
            <text class="module-chart_label-title" v-if="label.title">
              {{ label.title }}
            </text>
            <text class="module-chart_label-value" v-if="label.value">
              {{ label.value }}
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
      v-if="mixinOptions && mixinOptions.tooltipIsShow()"
      :show="mixinTooltip.show"
      :left="mixinTooltip.left"
      :top="mixinTooltip.top"
      :content="mixinTooltip.content"
    />
  </div>
</template>
<script>
import { colors, options, legend, tooltip } from "./../mixins";
import { arc, pie } from "d3";
import TweenLite from "gsap/TweenLite";
export default {
  mixins: [colors, options, legend, tooltip],
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
      radius: 0,
      outerRadius: 0,
      innerRadius: 0,
      arc: null,
      outerArc: null,
      formattedData: [],
      slicesData: [],
      pieFunc: null,
      lines: [],
      labels: []
    };
  },
  computed: {
    pieData() {
      return this.pieFunc(this.formattedData);
    },
    slices() {
      return this.slicesData.map(slice => JSON.parse(JSON.stringify(slice)));
    }
  },
  watch: {
    data() {
      this.setFormattedData();
    },
    formattedData: {
      handler: function() {
        this.setLegend(this.formattedData);
        this.draw();
      },
      deep: true
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    setFormattedData() {
      const colorsMode = this.data.length === 2 ? 'bin' : 'sequential';

      this.colors = this.getColors(this.data.length, colorsMode);

      const sum = this.data.reduce(
        (accumulator, item) => accumulator + item.value,
        0
      );

      this.formattedData = this.data.map((d, i) => {
        let item = JSON.parse(JSON.stringify(d));
        item.id = i;
        item.value = Math.round(((item.value) * 100) / 100);
        item.enabled = d.enabled === undefined ? true : d.enabled;
        item.color = this.colors(i);
        return item;
      });
    },

    init() {
      this.setMixinOptions(this.options, this.$refs.chart);

      this.pieFunc = pie()
        .value(d => {
          return d.enabled ? d.value : null;
        })
        .sort(null);

      this.setFormattedData();

      this.radius =
        Math.min(this.mixinOptionsWidth, this.mixinOptionsHeight) / 2;

      this.outerRadius = this.radius * 0.3;
      this.innerRadius = this.radius * 0.7;

      this.arc = arc()
        .innerRadius(this.innerRadius)
        .outerRadius(this.outerRadius);

      this.outerArc = arc()
        .innerRadius(this.radius * 0.8)
        .outerRadius(this.radius * 0.8);

      this.draw();

      this.isLoaded = true;
    },
    draw() {
      this.setCoordinateSlices();

      if (this.mixinOptions.outerLabelIsShow()) {
        this.setCoordinateLines();
        this.setCoordinateLabels();
      }
    },
    setCoordinateSlices() {
      this.slicesData = this.pieData.map(item => {
        let slice = JSON.parse(JSON.stringify(item));

        let prevSliceParams =
          this.slices && this.slices[item.data.id]
            ? this.slices[item.data.id]
            : null;

        slice.startAngle = prevSliceParams ? prevSliceParams.startAngle : 0;
        slice.endAngle = prevSliceParams ? prevSliceParams.endAngle : 0;
        slice.padAngle = prevSliceParams ? prevSliceParams.padAngle : 0;

        if (this.mixinOptions.innerLabelIsShow()) {
          slice.text = [];
          slice.text.value = this.mixinOptions.getInnerLabel().content(item);
          slice.innerRadius = this.innerRadius;
          slice.outerRadius = this.outerRadius;

          slice.text.transform = `translate(${this.arc.centroid(slice)})`;
        }

        if (this.mixinOptions.tooltipIsShow()) {
          item.label = item.data.label;
          slice.tooltip = this.mixinOptions.getTooltip(item);
        }

        return slice;
      });

      this.slicesData.forEach(this.setCoordinateSlice);
    },

    setCoordinateSlice(item, key) {
      let currentSlice = this.pieData[key];

      TweenLite.to(this.slicesData[key], 1, {
        startAngle: currentSlice.startAngle,
        endAngle: currentSlice.endAngle,
        padAngle: currentSlice.padAngle
      });
    },

    setCoordinateLines() {
      this.lines = this.pieData.map(d => {
        let line = JSON.parse(JSON.stringify(d));
        let pos = this.outerArc.centroid(d);
        pos[0] =
          this.radius *
          0.8 *
          (d.startAngle + (d.endAngle - d.startAngle) / 2 < Math.PI ? 1 : -1);
        line.points = [this.arc.centroid(d), this.outerArc.centroid(d), pos];
        return line;
      });
    },

    setCoordinateLabels() {
      let outerLabel = this.mixinOptions.getOuterLabel();

      this.labels = this.pieData.map(d => {
        let item = JSON.parse(JSON.stringify(d));
        let pos = this.outerArc.centroid(d);
        pos[0] =
          this.radius *
          0.8 *
          (d.startAngle + (d.endAngle - d.startAngle) / 2 < Math.PI ? 1 : -1);
        item.transform = `translate(${pos[0]}, ${pos[1] - 5})`;

        if (outerLabel.title && outerLabel.title.show) {
          item.title = d.data.label;
        }
        if (outerLabel.value && outerLabel.value.show) {
          item.value = this.mixinOptions.getOuterLabelContent(d);
        }
        return item;
      });
    },

    resize() {
      if (!this.isLoaded) return;

      this.updateMixinOptionsWidth();
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  .module-chart_pie {
    .module-chart_lines {
      fill: none;
      stroke: grey;
      opacity: 0.5;
      .module-chart_line {
        &.hide {
          opacity: 0;
        }
      }
    }

    .module-chart_label {
      &.hide {
        opacity: 0;
      }
    }
    .module-chart_label-value {
      transform: translate(0, 20px);
    }
  }
}
</style>
