<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M26 0.832031H0V4.08203H26V0.832031ZM26 7.33073H0V10.5807H26V7.33073ZM0 13.8374H26V17.0874H0V13.8374ZM13 20.3321H0V23.5821H13V20.3321Z" :fill="color"/>
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
      default: "FileText",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Текстовый файл",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 26 27",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
