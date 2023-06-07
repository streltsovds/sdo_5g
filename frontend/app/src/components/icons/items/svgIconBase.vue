<template>
  <svg
    class="svg-icon"
    :viewBox="viewBox"
    v-on="$listeners"
    :data-debug-icon-name="name"
    :style="{
      width: addPx(width),
      height: addPx(height),
    }"
    xmlns="http://www.w3.org/2000/svg"
  >
    {{ /* ? Из старого шаблона iconContainerOld: */ }}
    {{ /* ? :aria-labelledby="iconName" */ }}
    {{ /* ?  role="presentation" */ }}
    <title v-if="titleIcon" :id="name" v-text="title" />
    <slot />
  </svg>
</template>
<script>
import addPx from "@/utilities/addPx";
// Partially implements this pattern: https://vuejs.org/v2/cookbook/editable-svg-icons.html

/**
 * Генерирует правильные атрибуты для тэга <svg> иконки
 */
export default {
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "box"
    },
    // название иконки, рус.
    title: {
      type: String,
      default: ""
    },
    titleIcon: {
      type: Boolean,
      default: false
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 18 18"
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 18
    },
    height: {
      type: [Number, String],
      default: 18
    },
  },
  methods: {
    addPx,
  },
};
</script>
