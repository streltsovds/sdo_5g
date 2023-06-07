<template>
  <v-tooltip
    class="v-tooltip-simple"
    v-bind="{...$attrs, ...$props}"
    bottom
  >
    <template v-slot:activator="{ on: onTooltip }">
      <span
        :class="['v-tooltip-simple__activator', contentClass]"
        :style="contentStyle"
        v-on="onTooltip"
      >
        <slot />
      </span>
    </template>
    <span class="v-tooltip-simple__text">
      {{ _(text) }}
    </span>
  </v-tooltip>
</template>

<script>
export default {
  name: "VTooltipSimple",
  props: {
    text: {
      type: String,
      default: null
    },
    contentClass: {
      type: String,
      default: null,
    },
    contentStyle: {
      type: null,
      default: null,
    },
  },
  computed: {},
};
</script>

<style lang="scss">

</style>
