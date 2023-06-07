<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.5457 0.631714H4.47164C2.53646 0.631714 0.953125 2.21505 0.953125 4.15023V32.2984C0.953125 34.2336 2.51887 35.8169 4.45405 35.8169H25.5828C27.5179 35.8169 29.1013 34.2336 29.1013 32.2984V11.1873L18.5457 0.631714ZM22.0632 18.2232H7.9891V21.7418H22.0632V18.2232ZM22.0632 25.2624H7.9891V28.7809H22.0632V25.2624ZM4.47271 32.2994H25.5838V12.9476H16.7875V4.1513H4.47271V32.2994Z" :fill="color"/>
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
      default: "FileDoc",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Файл-документ",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 30 36",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
