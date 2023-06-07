<template>
  <div v-if="legends" class="module-chart_legends">
    <ul>
      <li
        v-for="legend in legends"
        :key="legend.id"
        :class="{ hidden: !legend.enabled }"
        @click="toggleEnabled(legend.id)"
      >
        <span
          :style="{ backgroundColor: legend.color, borderColor: legend.color }"
        />
        {{ legend.label }}
      </li>
    </ul>
  </div>
</template>
<script>
export default {
  props: {
    legends: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    toggleEnabled(key) {
      this.$emit("toggleEnabled", key);
    }
  }
};
</script>
<style lang="scss">
.module-chart_legends {
  position: relative;

  margin: 15px;
  padding-top: 15px;
  &:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.12);
  }
  ul {
    list-style: none;
    padding-left: 0;
  }
  li {
    padding-left: 40px;
    position: relative;
    cursor: pointer;
    &.hidden {
      span {
        &:before {
          content: "";
          position: absolute;
          width: 35px;
        }
      }
    }
    span {
      position: absolute;
      left: 0;
      top: 5px;
      width: 30px;
      display: inline-block;
      vertical-align: middle;
      border-top: 15px solid lightgrey;
      &:before {
        content: "";
        position: absolute;
        width: 0;
        height: 1px;
        background: rgba(#000, 0.5);
        top: -8px;
        left: -3px;
        transition: width 0.3s;
      }
    }
  }
}
</style>
