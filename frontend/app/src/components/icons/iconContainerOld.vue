<template>
  <svg
    :width="width"
    :height="height"
    :viewBox="viewBox"
    :aria-labelledby="iconName"
    xmlns="http://www.w3.org/2000/svg"
    role="presentation"
  >
    <title :id="iconName" v-text="title"></title>
    <g :fill="iconColor"><slot /></g>
  </svg>
</template>
<script>
// Implements this pattern: https://vuejs.org/v2/cookbook/editable-svg-icons.html
//TODO адаптировать другие иконки, лежащие в этой папке, проверить работу
export default {
  props: {
    iconName: {
      type: String,
      default: "box"
    },
    width: {
      type: [Number, String],
      default: 18
    },
    height: {
      type: [Number, String],
      default: 18
    },
    iconColor: {
      type: String,
      default: "currentColor"
    },
    viewBox: {
      type: String,
      default: "0 0 18 18"
    },
    title: {
      type: String,
      default: ""
    }
  }
};
</script>
