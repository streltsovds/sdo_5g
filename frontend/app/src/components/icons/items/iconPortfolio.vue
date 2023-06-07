<template>
  <svg-icon-base v-bind="$props">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M14.6663 5.91797V8.0013H1.33301V5.91797C1.33301 5.2276 1.89264 4.66797 2.58301 4.66797H4.66634V3.41797C4.66634 2.7276 5.22598 2.16797 5.91634 2.16797H10.083C10.7734 2.16797 11.333 2.7276 11.333 3.41797V4.66797H13.4163C14.1067 4.66797 14.6663 5.2276 14.6663 5.91797ZM9.66634 8.83463H14.6663V12.5846C14.6663 13.275 14.1067 13.8346 13.4163 13.8346H2.58301C1.89264 13.8346 1.33301 13.275 1.33301 12.5846V8.83463H6.33301V9.35547C6.33301 9.52805 6.47293 9.66797 6.64551 9.66797H9.35384C9.52642 9.66797 9.66634 9.52805 9.66634 9.35547V8.83463ZM6.33301 3.83464H9.66634V4.66797H6.33301V3.83464Z" :fill="color"/>
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
      default: "Portfolio",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Портфель",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 16 16",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
