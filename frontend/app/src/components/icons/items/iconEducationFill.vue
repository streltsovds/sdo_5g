<template>
  <svg-icon-base v-bind="$props" v-on="$listeners">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.8459 6.87827L19.4651 4.2189C20.1766 4.00737 20.18 2.99362 19.4651 2.78109L10.8458 0.121711C10.2936 -0.0405703 9.70636 -0.0405703 9.15414 0.121711L0.534867 2.78109C-0.176569 2.99262 -0.180007 4.00637 0.534867 4.2189L1.8943 4.63834C1.63874 5.08727 1.54415 5.56477 1.51455 6.12574C1.20771 6.29652 1.00002 6.62393 1.00002 6.99999C1.00002 7.37349 1.20493 7.69893 1.5083 7.87062L1.00215 11.7174C0.982461 11.8671 1.09915 12 1.25002 12H2.75002C2.90102 12 3.01758 11.867 2.9979 11.7174L2.49174 7.87065C2.79511 7.69893 3.00002 7.37349 3.00002 6.99999C3.00002 6.63609 2.80558 6.31765 2.51502 6.14271C2.54315 5.66505 2.61983 5.30218 2.88746 4.94474L9.15417 6.87827C9.70639 7.04055 10.2936 7.04055 10.8459 6.87827ZM11.1343 7.8358L11.1407 7.8338L15.8568 6.37849L16.25 8.99999C16.25 10.1046 13.4518 11 10 11C6.54823 11 3.75002 10.1046 3.75002 8.99999L4.14327 6.37849L8.85933 7.8338L8.8658 7.8358L8.87226 7.83771C9.60854 8.05408 10.3915 8.05408 11.1278 7.83771L11.1343 7.8358Z"
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
      default: "EducationFill",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "преподователь",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 20 12",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
