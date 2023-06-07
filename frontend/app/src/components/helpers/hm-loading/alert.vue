<template>
  <v-alert
    class="v-sheet elevation-4 hm-loading-indicator"
    value="true"
    type="info"
  >
    <v-progress-circular indeterminate></v-progress-circular>
    <span class="ml-3 subheading">Загрузка данных...</span>
  </v-alert>
</template>

<style lang="scss">
.hm-loading-indicator {
  max-width: 800px;
  .v-icon {
    display: none;
  }
}
</style>
