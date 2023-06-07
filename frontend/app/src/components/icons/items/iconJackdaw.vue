<template>
  <svg-icon-base v-bind="$props" v-on="$listeners">
    <path d="M13.4417 0.46619L5.89362 8.53647L2.61807 4.53388C2.13514 3.94271 1.22464 3.8255 0.582711 4.26684C-0.060701 4.70818 -0.191362 5.54419 0.291404 6.13265L4.65491 11.4659C4.91903 11.7901 5.32963 11.9853 5.77136 11.9985C5.78702 12 5.80267 12 5.81832 12C6.24161 12 6.64644 11.8307 6.92193 11.5339L15.6489 2.2006C16.1731 1.64189 16.102 0.800898 15.4913 0.321795C14.8833 -0.157458 13.9645 -0.0949283 13.4417 0.46619Z"
          v-bind="pathAttrs"
    />

  </svg-icon-base>
</template>

<script>
import VueMixinSvgIcon from "./VueMixinSvgIcon";

export default {
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "Jackdaw",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "галка",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 16 12",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
