import MixinTooltip from "./../partials/tooltip";

export const Tooltip = {
  components: { MixinTooltip },
  data() {
    return {
      mixinTooltip: {
        show: false,
        left: 0,
        top: 0,
        content: ""
      }
    };
  },
  methods: {
    mixinTooltipMouseOver(event, content) {
      this.mixinTooltip.left = event.layerX;
      this.mixinTooltip.top = event.layerY - 28;
      this.mixinTooltip.content = content;
      this.mixinTooltip.show = true;
    },
    mixinTooltipMouseOut() {
      this.mixinTooltip.show = false;
    }
  }
};
