<template>
    <svg-icon-base v-bind="$props">
        <circle cx="12" cy="12" r="12" :fill="color"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9988 0C5.37574 0 0 5.37615 0 11.9997C0 18.6239 5.37574 24 11.9988 24C18.6224 24 23.9982 18.6239 23.9982 11.9997C23.9982 5.37615 18.6224 0 11.9988 0ZM11.9998 3.60156C13.9919 3.60156 15.5999 5.20966 15.5999 7.20189C15.5999 9.19353 13.9919 10.8016 11.9998 10.8016C10.0084 10.8016 8.40039 9.19353 8.40039 7.20189C8.40039 5.20966 10.0084 3.60156 11.9998 3.60156ZM11.9983 20.6383C8.9988 20.6383 6.34669 19.1017 4.79883 16.774C4.83489 14.3856 9.59871 13.0781 11.9983 13.0781C14.3866 13.0781 19.1618 14.3856 19.1979 16.774C17.65 19.1017 14.9985 20.6383 11.9983 20.6383Z" fill="#FFFFFF"/>
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
      default: "userCourse",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Закрыть",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 26 26",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 26,
    },
    height: {
      type: [Number, String],
      default: 26,
    },
    color: {
      type: String,
      default: "#DADADA",
    },
  },
};
</script>
