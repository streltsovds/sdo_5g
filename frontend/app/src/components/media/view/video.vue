<template>
  <div class="hm-material-resource hm-material-video">
    <video :src="src" controls></video>
  </div>
</template>
<script>
export default {
  props: {
    src: {
      type: String,
      required: true
    }
  }
};
</script>
<style lang="scss">
.material-resource {
  &.material-video {
    video {
      width: 100%;
      max-width: 100%;
    }
  }
}
</style>
