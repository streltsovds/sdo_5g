<template>
  <div class="hm-test-placeholder__img">
    <img
      :src="src"
      :width="width"
      :height="height"
    />
  </div>
</template>
<script>
export default {
  props: {
    src: {
      type: String,
      default: null
    },
    width: {
      type: String,
      default: null
    },
    height: {
      type: String,
      default: null
    },
  }
};
</script>
