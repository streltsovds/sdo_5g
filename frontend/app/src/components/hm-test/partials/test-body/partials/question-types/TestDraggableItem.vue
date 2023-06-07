<template>
  <div class="hm-test-draggable_item-wrapper">
    <span v-if="index" class="hm-test-draggable_item-count">{{index}}</span>
    <div
      class="hm-test-draggable_item"
      :style="{
        cursor: isMobile ? 'pointer' : 'move'
      }"
    >
      <span v-if="!isMobile" class="drag-area">
        <v-icon>drag_indicator</v-icon>
      </span>

      <span
        v-if="variantsAsHtml"
        class="variant-with-html-markup hm-test-draggable_item__content"
        v-html="text"
      ></span>
      <span v-else class="hm-test-draggable_item__content">
        {{ text }}
      </span>

      <span v-if="!isMobile" class="drag-area">
        <v-icon>drag_indicator</v-icon>
      </span>
      <v-divider v-if="isMobile" vertical></v-divider>
      <span v-if="isMobile" class="drag-area">
        <v-icon>touch_app</v-icon>
      </span>
    </div>
  </div>
</template>

<script>
    export default {
        props: ["variantsAsHtml", "text", "isMobile", "index"]
    };
</script>

<style lang="scss">
  /* перетаскиваемая строка */
  .hm-test-draggable_item {
    align-items: stretch;
    justify-content: space-between;
    width: 100%;
    min-height: 50px;
    display: flex;
    background: #FFFFFF;
    border: 1px solid #DADADA;
    box-sizing: border-box;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.14);
    border-radius: 4px;
    &-wrapper {
      display: flex;
      align-items: center;
      width: 100%;

      + .hm-test-draggable_item-wrapper {
        margin-top: 26px;
      }
    }
    &-count {
      font-weight: bold;
      font-size: 18px;
      margin-right: 20px;
    }

    &.sortable-chosen {
      opacity: 1 !important;
      background-color: #c3c3c3 !important;
    }

    + .hm-test-draggable_item {
      margin-top: 26px;
    }
    &.sortable-chosen {
      opacity: 1;
      background-color: white;
    }
    .hm-test-draggable_item__content {
      display: flex;
      align-items: center;
      flex-grow: 1;
      /*border-right: 1px solid #a0a0a0;*/
      /*border-left: 1px solid #a0a0a0;*/
      margin-top: 4px;
      margin-bottom: 4px;
      padding-left: 8px;
      padding-right: 4px;
    }

    /* значок, за который можно перетаскивать */
    .drag-area {
      cursor: move;
      display: flex;
      margin-top: 5px;
      margin-bottom: 4px;
      padding-left: 8px;
      padding-right: 8px;
      align-items: center;
    }

    .v-divider.theme--light {
      border-color: #a0a0a0;
      margin-top: 4px;
      margin-bottom: 4px;
    }
  }
</style>
