<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.4428 9.22139C18.4428 11.6089 17.53 13.7883 16.0356 15.4273L23.874 23.266C24.042 23.434 24.042 23.706 23.874 23.874C23.706 24.042 23.434 24.042 23.266 23.874L15.4274 16.0353C13.7882 17.5297 11.6095 18.4425 9.22128 18.4425C4.13678 18.4425 0 14.3057 0 9.22112C0 4.13656 4.13678 -0.000266655 9.22128 1.28918e-08C14.3061 1.28918e-08 18.4428 4.13682 18.4428 9.22139ZM0.860026 9.22129C0.860026 13.8317 4.61067 17.5827 9.22158 17.5827C13.832 17.5827 17.5831 13.8314 17.5831 9.22129C17.5831 4.61033 13.8322 0.859375 9.22158 0.859375C4.61067 0.859375 0.860026 4.61033 0.860026 9.22129Z" v-bind="pathAttrs"/>
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
      default: "Search",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Поиск",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 24",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
