<template>
  <svg-icon-base v-bind="$props">
    <path d="M2.1451 18.7485H5.63193C5.97753 18.7485 6.2577 19.0286 6.2577 19.3742C6.2577 19.7198 5.97753 20 5.63193 20H1.73589H0.62577C0.280167 20 0 19.7198 0 19.3742V14.3681C0 14.0225 0.280167 13.7423 0.62577 13.7423C0.971373 13.7423 1.25154 14.0225 1.25154 14.3681V17.8721L5.73497 13.3886C5.98125 13.1424 6.37834 13.1415 6.62272 13.3859C6.8688 13.632 6.86585 14.0277 6.61994 14.2736L2.1451 18.7485ZM14.3756 1.25154C14.03 1.25154 13.7499 0.971373 13.7499 0.62577C13.7499 0.280167 14.03 0 14.3756 0H18.2717H19.3818C19.7274 0 20.0076 0.280167 20.0076 0.62577V5.63193C20.0076 5.97753 19.7274 6.2577 19.3818 6.2577C19.0362 6.2577 18.756 5.97753 18.756 5.63193V2.12792L14.2726 6.61135C14.0263 6.85763 13.6292 6.85851 13.3848 6.61413C13.1388 6.36805 13.1417 5.97229 13.3876 5.72638L17.8625 1.25154H14.3756Z" v-bind="pathAttrs" />
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
      default: "Expand",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Расширить",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 21 20",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 21,
    },
    height: {
      type: [Number, String],
      default: 20,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
