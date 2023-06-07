<template>
  <v-progress-linear
    v-if="isShown"
    background-opacity="0"
    class="hm-global-loader"
    color="success"
    :indeterminate="true"
  ></v-progress-linear>
</template>
<script>
export default {
  name: "HmGlobalLoader",
  computed: {
    isShown() {
      // console.log('HmGlobalLoader: isShown recount');
      return this.$store.getters.isLoading;
    },
  },
};
</script>
<style lang="scss">
.v-progress-linear.hm-global-loader {
  position: absolute !important;
}
.hm-global-loader {
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  margin: 0;

  .v-progress-linear__indeterminate--active {
    .long {
      animation-duration: 10s;
    }
    .short {
      animation-duration: 10s;
    }
  }
}
</style>
