<template>
  <svg-icon-base v-bind="$props">
    <path d="M1 1H19" :stroke="color" stroke-width="1.3" stroke-linecap="round"/>
  </svg-icon-base>
</template>

<script>
import VueMixinSvgIcon from "@/components/icons/items/VueMixinSvgIcon";

export default {
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "Minus",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Удалить",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 20 2",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 20,
    },
    height: {
      type: [Number, String],
      default: 2,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
