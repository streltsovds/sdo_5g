<template>
  <div
    class="module-chart_tooltip"
    :style="{ left: coordinateLeft + 'px', top: top + 'px' }"
    :class="{ show: show }"
    v-html="content"
  />
</template>
<script>
export default {
  props: {
    content: {
      type: [String, Number],
      required: true
    },
    left: {
      type: Number,
      required: true
    },
    top: {
      type: Number,
      required: true
    },
    show: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      offsetWidth: null,
      parentOffsetWidth: null,
    }
  },
  mounted() {
    this.offsetWidth = this.$el.offsetWidth;
    this.parentOffsetWidth = this.$el.parentNode.offsetWidth;
  },
  computed: {
    coordinateLeft() {
      if (this.offsetWidth && this.left + this.offsetWidth >= this.parentOffsetWidth) {
        return this.parentOffsetWidth - this.offsetWidth - 200;
      }
      return this.left;
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  .module-chart_tooltip {
    border: 0;
    border-radius: 2px;
    box-shadow: 0px 3px 1px -2px rgba(0, 0, 0, 0.2),
      0px 2px 2px 0px rgba(0, 0, 0, 0.14), 0px 1px 5px 0px rgba(0, 0, 0, 0.12);
    padding: 5px 8px;
    font-size: 12px;
    pointer-events: none;
    position: absolute;
    text-align: center;
    background-color: whitesmoke;
    opacity: 0;
    transition: opacity 0.3s;
    &.show {
      opacity: 1;
    }
  }
}
</style>
