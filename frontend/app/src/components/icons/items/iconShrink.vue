<template>
  <svg-icon-base v-bind="$props">
    <path d="M13.2093 5.71104H16.8003C17.1561 5.71104 17.4448 5.99913 17.4448 6.35552C17.4448 6.71127 17.1561 7 16.8003 7H12.7878H11.6445C11.2887 7 11 6.71127 11 6.35552V1.1997C11 0.843303 11.2887 0.555221 11.6445 0.555221C12.0002 0.555221 12.289 0.843303 12.289 1.1997V4.80813L16.9066 0.191091C17.1599 -0.0628338 17.5692 -0.0634783 17.8205 0.187868C18.0744 0.441148 18.0712 0.849103 17.8179 1.10238L13.2093 5.71104Z" v-bind="pathAttrs" />
    <path d="M4.80025 12.289H1.20922C0.853468 12.289 0.564742 12.0009 0.564742 11.6445C0.564742 11.2887 0.853468 11 1.20922 11H5.22174H6.36504C6.7208 11 7.00952 11.2887 7.00952 11.6445V16.8003C7.00952 17.1567 6.7208 17.4448 6.36504 17.4448C6.00929 17.4448 5.72057 17.1567 5.72057 16.8003V13.1919L1.10288 17.8089C0.8496 18.0628 0.440358 18.0635 0.189011 17.8121C-0.0649133 17.5589 -0.0616908 17.1509 0.191589 16.8976L4.80025 12.289Z" v-bind="pathAttrs" />
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
      default: "Shrink",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Сжать",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 19 18",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 19,
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
