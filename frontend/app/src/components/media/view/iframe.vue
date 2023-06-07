<template>
  <v-layout
    ref="layout"
    class="material-iframe"
    row
    align-center
    justify-center
    fill-height
  >
    <v-flex> <iframe :src="url" :height="height" width="100%" /> </v-flex>
  </v-layout>
</template>
<script>
/**
 * TODO <hm-material-iframe/> - удалить использования и компонент
 **/
export default {
  props: {
    url: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      height: 0
    };
  },
  mounted() {
    this.height = this.$refs.layout.offsetHeight;
  }
};
</script>
<style lang="scss">
.material-resource.material-iframe {
  margin: 0 !important;
  .flex {
    padding: 0 !important;
    justify-content: center;
    align-content: center;
    iframe {
      border: none;
    }
  }
}
</style>
