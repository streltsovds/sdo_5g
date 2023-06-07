import svgIconBase from "./svgIconBase";

/**
 * Повторяющийся код в иконках
 */
export default {
  components: {
    svgIconBase,
  },
  props: {
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 24,
    },
    height: {
      type: [Number, String],
      default: 24,
    },
    color: {
      type: String,
      default: "#000",
    },
    strokeWidth: {
      type: Number | String,
      default: null,
    },
    /** показ title */
    titleIcon: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    strokeWidthFixed() {
      let strokeWidth = this.strokeWidth || 0;

      strokeWidth = parseFloat(strokeWidth);

      if (strokeWidth < 0.00001) {
        strokeWidth = null;
      }

      return strokeWidth;
    },
    svgAttrs() {
      return {
        name: this.name,
        width: this.width,
        height: this.height,
        viewBox: this.viewBox,
        title: this.title,
        titleIcon: this.titleIcon
      };
    },
    pathAttrs() {
      return {
        fill: this.color,
        stroke: this.strokeWidthFixed ? this.color : null,
        "stroke-width": this.strokeWidthFixed,
      };
    },
    shapeAttrs() {
      return {
        stroke: this.color,
        "stroke-width": this.strokeWidthFixed + 1,
      };
    },
  },
};
