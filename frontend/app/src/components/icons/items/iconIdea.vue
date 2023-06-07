<template>
  <svg-icon-base v-bind="$props">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M16.5333 8.324C16.5333 3.73413 12.8246 0 8.26667 0C3.70871 0 0 3.73413 0 8.324C0 12.2749 3.32685 16.8477 4.21582 17.9992H4.1957V19.9003C4.1957 22.1608 6.02211 24 8.26667 24C10.5115 24 12.3379 22.1608 12.3379 19.9003V17.9992H12.3175C13.2065 16.8477 16.5333 12.2749 16.5333 8.324ZM11.4945 19.9005C11.4945 21.6933 10.0462 23.1512 8.26667 23.1512C6.48713 23.1512 5.03887 21.693 5.03887 19.9005V18.424H11.4947V19.9005H11.4945ZM0.843424 8.32416C0.843424 12.0834 4.26375 16.681 4.95729 17.5748H11.5758C12.2693 16.6804 15.6899 12.0799 15.6899 8.32416C15.6899 4.20229 12.3599 0.848958 8.26666 0.848958C4.17345 0.848958 0.843424 4.20229 0.843424 8.32416Z"
            v-bind="pathAttrs"/>
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
      default: "Idea",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Идея",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 17 24",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
