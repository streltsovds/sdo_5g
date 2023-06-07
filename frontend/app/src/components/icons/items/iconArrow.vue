<template>
  <svg-icon-base v-bind="$props">
      <path d="M0.282012 8.82123C0.07826 9.02499 0.0782596 9.35533 0.282011 9.55908L0.467639 9.74471C0.671391 9.94846 1.00174 9.94846 1.20549 9.74471L6.00178 4.94842L1.20549 0.152129C1.00174 -0.0516232 0.671391 -0.0516233 0.467639 0.152128L0.282011 0.337756C0.0782593 0.541508 0.0782594 0.871855 0.282011 1.07561L4.15483 4.94842L0.282012 8.82123Z"
            v-bind="pathAttrs" />
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
      default: "Arrow",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "стрелка",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 7 10",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
