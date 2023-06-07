<template>
    <svg-icon-base v-bind="$props">
        <path d="M15.1988 0.361849C14.3182 -0.236555 13.0622 -0.0714075 12.4002 0.7213L8 6.00112L3.59976 0.721095C2.93778 -0.0732439 1.6818 -0.238595 0.801208 0.361644C-0.0834619 0.957398 -0.263116 2.08591 0.40067 2.88126L5.50027 9.00069L0.400898 15.1199C-0.263114 15.9153 -0.0834603 17.0438 0.801436 17.6395C1.16097 17.883 1.58053 18 1.99852 18C2.60793 18 3.20738 17.7515 3.59998 17.2803L8 12.0003L12.4002 17.2803C12.7926 17.7515 13.3925 18 14.0017 18C14.4197 18 14.8397 17.883 15.1988 17.6395C16.0835 17.0438 16.2631 15.9153 15.5993 15.1199L10.4997 9.00069L15.5991 2.88147C16.2631 2.08611 16.0835 0.957603 15.1988 0.361849Z" :fill="color"/>
    </svg-icon-base>
</template>

<script>
import svgIconBase from "./svgIconBase";

export default {
  components: {
    svgIconBase,
  },
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "cross",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "отрицательно",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 16 18",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 16,
    },
    height: {
      type: [Number, String],
      default: 18,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
