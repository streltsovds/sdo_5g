<template>
  <div class="hm-material-resource hm-material-html">
    <slot name="content" />
  </div>
</template>
<style lang="scss">
.hm-material-html {
  padding-right: 20px;
  img {
    max-width: 100%;
  }
  table {
    overflow-x: auto;
    display: table;
    width: fit-content;
    max-width: 100%;
    tbody{
      width: 100%;
    }
  }
}

</style>
