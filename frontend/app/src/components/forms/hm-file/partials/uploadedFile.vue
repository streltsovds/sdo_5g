<template>
  <v-list-item
    class="hm-file__uploaded-file"
  >
    <v-list-item-avatar
      class="hm-file__uploaded-file__img"
      tile
    >
      <v-img
        v-if="file.type == 'image'"
        :src="file.previewUrl || file.url"
        :alt="file.name"
        contain
        eager
      />
      <file-icon
        hm-news-banner-block
        v-else
        :type="file.type"
      />
    </v-list-item-avatar>

    <v-list-item-content class="hm-file__uploaded-file__info">
      <v-list-item-title
        class="hm-file__uploaded-file__name"
        :title="file.name"
        :style="{
          color: colorFileName,
        }"
      >
        {{ file.name }}
      </v-list-item-title>

      <!--
              <v-list-item-subtitle>
                {{ file.type }}
              </v-list-item-subtitle>
            -->

      <v-list-item-subtitle
        class="hm-file__uploaded-file__size-and-actions"
        v-if="file.size"
        :style="{
          color: colorFileSize,
        }"
      >
        <span class="hm-file__uploaded-file__size">
          {{ fileSizeInMb(file.size) }}
        </span>

        <v-list-item-action
          class="hm-file__uploaded-file__actions"
          v-if="showActions"
        >
          <v-tooltip-simple text="Удалить">
            <v-btn class="hm-file__uploaded-file__delete"
                   @click="onBtnDeleteClick"
                   text
                   small
                   color="error"
            >
              <svg-icon
                :color="themeColors.error"
                :height="20"
                name="delete"
                title=""
              />
            </v-btn>
          </v-tooltip-simple>
        </v-list-item-action>
      </v-list-item-subtitle>

      <v-list-item-subtitle
        class="hm-file__uploaded-file__convert-to-pdf"
        v-if="file.convertableToPdf && statusCheckbox"
      >
<!--        :true-value="true"-->
<!--        :false-value="false"-->
        <v-checkbox
          v-model="file.convertToPdf"
          :label="_('Сконвертировать в PDF для просмотра в браузере')"
          @change="onConvertToPdfClick"
          hide-details
        />
      </v-list-item-subtitle>

    </v-list-item-content>
  </v-list-item>
</template>
<script>
import FileIcon from "@/components/icons/file-icon/index"
import SvgIcon from "@/components/icons/svgIcon"
import VTooltipSimple from "@/components/helpers/v-tooltip-simple"
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import {merge} from "lodash";

export default {
  name: 'HmFileUploadedFile',
  components: {FileIcon, SvgIcon, VTooltipSimple},
  mixins: [ VueMixinConfigColors ],
  props: {
    file: {
      type: Object,
      default: () => {},
    },
    showActions: {
      type: Boolean,
      default: true,
    },
    statusCheckbox: {
      type: Boolean,
      default: true,
    }
  },
  computed: {
    colorFileName() {
      return this.colors.textDark;
    },
    colorFileSize() {
      return this.colors.textDark;
    },
  },
  methods: {
    onBtnDeleteClick() {
      this.$emit('on-btn-delete', this.file)
    },
    fileSizeInMb(value) {
      if (!value) return 0;
      let result,
        aMultiples = ["Кб", "Мб", "Гб", "Тб"];
      for (
        let nMultiple = 0, nApprox = value / 1024;
        nApprox > 1;
        nApprox /= 1024, nMultiple++
      ) {
        let fractionPartLength = nMultiple
        result = `${nApprox.toFixed(fractionPartLength)} ${aMultiples[nMultiple]}`;
      }
      return result;
    },
    // передача информации в родительский компонент
    emitFileUpdateFull(newFileInfo) {
      this.$emit('file-update', newFileInfo);
    },
    emitFileUpdate(fileInfoChanges) {
      this.emitFileUpdateFull(
        merge({}, this.file, fileInfoChanges)
      )
    },
    onConvertToPdfClick() {
      this.emitFileUpdate({
        convertToPdf: !this.file.convertToPdf,
      });
    },
  },
}
</script>
<style lang="scss">
.hm-file__uploaded-file {
  min-width: 260px;
  align-self: stretch;
  padding: 0;
  flex-basis: auto;
  flex-grow: 0;

  &__img {
    max-width: 150px;
    max-height: 100px;
    height: unset !important;
    width: unset !important;
    min-width: unset !important;
    margin-top: 0;
    margin-bottom: 0;
  }

  &__info {
    padding: 0;
    align-self: stretch;

    flex-direction: column;
    flex-wrap: nowrap;
    align-items: stretch;

    /* для ripple при клике по галочке */
    overflow: visible;

    flex-basis: auto;
    flex-grow: 0;
  }

  &__name {
    letter-spacing: 0.02rem;
    font-weight: 500;
    font-size: 14px;
    line-height: 21px !important;
    align-self: stretch;
  }

  &__size-and-actions {
    /*display: flex;*/
    /*justify-content: space-between;*/
    /*flex-direction: row-reverse;*/
    display: flex;
    align-items: center;

    position: relative;
    overflow: visible;
  }

  &__size,
  &__actions {
    position: relative;
    display: inline-block;
  }

  &__size {
    left: 0;

    font-size: 14px;
    line-height: 21px;

    letter-spacing: 0.02rem;
  }

  &__actions {
    right: 0;
    margin: 0;

    button {
      padding: 0 !important;
      min-width: 40px !important;
      margin-right: -17px;
    }
  }

  &__convert-to-pdf {
    overflow: visible;

    .v-input--checkbox {
      margin-top: 0;
      margin-left: -3px;
      margin-bottom: -5px;
      padding-top: 0;

      label {
        font-size: 14px;
      }
    }
  }
}

</style>
