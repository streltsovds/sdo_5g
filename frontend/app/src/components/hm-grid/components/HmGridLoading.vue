<template>
  <v-alert
    class="v-sheet elevation-4 hm-grid__loading-indicator"
    type="info"
  >
    <v-progress-circular indeterminate></v-progress-circular>
    <span class="ml-3 subheading">Загрузка данных...</span>
  </v-alert>
</template>

<script>
export default {};
</script>

<style lang="scss">
.hm-grid__loading-indicator {
  // так красивее :)
  max-width: 800px;
  & .v-icon {
    display: none;
  }
}
</style>
