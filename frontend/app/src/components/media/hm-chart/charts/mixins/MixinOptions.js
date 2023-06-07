import OptionsHelper from "./Options";

export const Options = {
  data() {
    return {
      mixinOptions: null,
      mixinOptionsWidth: 0,
      mixinOptionsHeight: 0,
      mixinOptionsMinHeight: 250,
    };
  },
  computed: {
    mixinOptionsWidthContainer() {
      if (!this.mixinOptions) return 0;

      let margin = this.mixinOptions.getMargin();
      return this.mixinOptionsWidth - margin.left - margin.right;
    },
    mixinOptionsHeightContainer() {
      if (!this.mixinOptions) return 0;

      let margin = this.mixinOptions.getMargin();
      return this.mixinOptionsHeight - margin.top - margin.bottom;
    },
  },
  methods: {
    setMixinOptions(options = []) {
      if (!this.$refs.chart)
        return console.error("Error: refs chart undefined");

      if (!options.width || options.width <= 0) {
        options.width = this.$refs.chart.offsetWidth;
      }
      if (!options.height || options.height <= 0) {
        options.height =
          this.$refs.chart.offsetHeight > 50
            ? this.$refs.chart.offsetHeight
            : this.mixinOptionsMinHeight;
      }

      this.mixinOptions = new OptionsHelper(options);
      this.mixinOptionsWidth = this.mixinOptions.getWidth();
      this.mixinOptionsHeight = this.mixinOptions.getHeight();
      this.updateMixinOptionsWidth();
    },
    updateMixinOptionsWidth() {
      let initWidth = this.mixinOptions.getWidth();
      this.mixinOptionsWidth =
        this.$refs.chart.offsetWidth < initWidth
          ? initWidth
          : this.$refs.chart.offsetWidth;
    },
  },
};
