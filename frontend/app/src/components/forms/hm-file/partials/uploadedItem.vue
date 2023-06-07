<template>
  <div class="hm-file__uploaded-item">
    <hm-file-uploaded-file
      v-if="uploadedItem.originalFile"
      :file="uploadedItem.originalFile"
      @on-btn-delete="onBtnDeleteClick"
    />

    <div class="hm-file__uploaded-item__end-file-with-convert-icon">
      <svg-icon
        class="hm-file__uploaded-item__convertIcon"
        v-if="uploadedItem.originalFile"
        name="shuffle"
        width="44"
        height="auto"
        :color="colorConvertIcon"
      />

      <hm-file-uploaded-file
        :file="uploadedItem.file"
        :show-actions="!uploadedItem.originalFile"
        @on-btn-delete="onBtnDeleteClick"
        @file-update="onFileUpdate"
        :status-checkbox="statusCheckbox"
      />
    </div>
  </div>
</template>
<script>
import HmFileUploadedFile from "./uploadedFile";
import svgIcon from "@/components/icons/svgIcon";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import {merge} from "lodash";

/**
 * Загруженный на сервер элемент. Может парно отображать и исходный файл, и сгенерированный из него pdf
 */
export default {
  name: "HmFileUploadedItem",
  components: { HmFileUploadedFile, svgIcon },
  mixins: [ VueMixinConfigColors ],
  props: {
    uploadedItem: {
      type: Object,
      default: () => {
      },
    },
    statusCheckbox: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    colorConvertIcon() {
      return this.colors.textLight;
    },
  },
  methods: {
    onBtnDeleteClick() {
      this.$emit("on-btn-delete", this.uploadedItem);
    },
    // TODO переписать на vuex
    emitUploadedItemUpdateFull(newUploadedItem) {
      this.$emit('uploaded-item-update', newUploadedItem);
    },
    emitUploadedItemUpdate(uploadedItemChanges) {
      this.emitUploadedItemUpdateFull(
        merge({}, this.uploadedItem, uploadedItemChanges)
      )
    },
    onFileUpdate(newFileInfo) {
      this.emitUploadedItemUpdate({
        file: newFileInfo,
      })
    }
  },

}
</script>
<style lang="scss">
.hm-file__uploaded-item {
  display: flex;
  align-items: center;
  padding: 0;
  margin-left: 32px;
  margin-bottom: 32px;
  flex-wrap: wrap;

  &__end-file-with-convert-icon {
    display: flex;
  }

  &__convertIcon {
    margin: 0 52px;
  }
}
</style>
