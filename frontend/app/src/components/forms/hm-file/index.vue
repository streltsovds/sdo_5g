<template>
  <div class="hm-form-element hm-file">
    <div class="hm-file__panel">
      <hm-errors :errors="errors"></hm-errors>
      <span class="hm-file__label">

        <!-- Заголовок поля формы -->

        <v-tooltip bottom open-delay="0">
          <template v-slot:activator="{ on: onTooltip }">
            <span v-on="onTooltip">
              <svg-icon
                :color="colorFieldTitleInfoIcon"
                name="info"
                :style="{
                  marginRight: '8px',
                  verticalAlign: 'bottom'
                }"
                title=""
                width="18"
                stroke-width="0.5"
              >
              </svg-icon>
              <span
                v-if="label"
                class="hm-file__label_title"
                :class="{ required }"
                :style="{ color: colorFieldTitleLabel}"
              >
                {{ label }}
              </span>
            </span>
          </template>

          <!-- Всплывающая подсказка с инфо.-й, какие файлы можно загружать -->

          <div class="hm-file_tooltip_content">
            <ul class="hm-file_requirements">
              <li v-if="params.tooltip">
                {{ params.tooltip }}
              </li>
              <li v-if="allowExtensions && allowExtensions !== '*'">
                Разрешённые типы файлов: {{ allowExtensions }}
              </li>
              <li v-if="fileSizeLimitForUser">
                Максимальный размер загружаемого файла: {{ fileSizeLimitForUser }}
              </li>
              <li v-if="fileUploadLimit > 1">
                Допустимое количество файлов: {{ fileUploadLimit }}
              </li>
            </ul>
          </div>

        </v-tooltip>
      </span>

      <!-- Кнопка выбора файла из файловой системы для загрузки -->

      <v-btn
        class="hm-file_btn-upload"
        :disabled="isLoading || (fileUploadLimitReached && !fileUploadReplaceMode)"
        :loading="isLoading"
        @click="open"
        color="primary"
        outlined
      >
        <svg-icon
          name="upload"
          width="16px"
          :color="colorFieldTitleInfoIcon"
          :style="{
            marginRight: '8px'
          }"
          stroke-width="0.3"
        />
        {{ btnUploadText }}
      </v-btn>
      <span>
        {{ btnFileSelectTooltipText }}
      </span>

      <div v-if="description" class="v-messages theme--light">
        <div v-html="description" class="v-messages__message"></div>
      </div>

      <!-- Пример файла (ссылка, кликнув по которой пользователь скачает типовой шаблон) -->

      <div v-if="params.file_sample && typeof params.file_sample === 'string'" class="hm-file_sample">
        <div class="hm-file_sample">
          <a :href="params.file_sample" download>Пример файла</a>
        </div>
      </div>

      <div v-else-if="params.file_sample && typeof params.file_sample === 'object'" class="hm-file_sample">
        <div v-for="(file, key) in params.file_sample" class="hm-file-sample_wrapper">
          <a :href="file" download>{{ key }}</a>
        </div>
      </div>

    </div>

  <!--
    <v-btn
      v-if="params.delete_button && previewUrl"
      color="warning"
      class="hm-file_btn-del"
      @click="delUploadPreview"
    >
      Удалить
    </v-btn>

    <div v-if="previewUrl" class="preview-container">
      <div class="preview-left">
        <img v-if="previewUrl" class="hm-file_preview-url" :src="previewUrl" />
      </div>
      <div class="preview-left">
        <v-btn
          v-if="showBasket"
          slot="activator"
          text
          icon
          color="error"
          @click="delUploadPreview"
          ><v-icon>delete</v-icon>
        </v-btn>
      </div>
    </div>
  -->

    <!-- Карточки загруженных файлов -->

    <div class="hm-file__list"
         v-if="currentUploadedItems.length > 0" >

      <hm-file-uploaded-item
        v-for="uploadedItem in currentUploadedItems"
        :key="uploadedItem.id"
        :uploaded-item="uploadedItem"
        @on-btn-delete="fileDeleteRequest"
        @upload-item-update="onUploadedItemUpdate"
        :status-checkbox="allowConversion"
      />

    </div>

    <!-- Компонент для обрезки изображения -->

    <hm-cropper
      v-if="crop"
      :src="crop.url"
      :ratio="crop.ratio"
      :is-open="crop.isOpen"
      @cancel="cancelCrop"
      @save="saveCrop"
    ></hm-cropper>

    <div class="hm-file_result">

      <!-- Поле для выбора файла. -->

      {{/* Используется для отправки ajax-запроса, после очищается. Не отправляется на сервер. */}}
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="fileUploadLimit > 1"
        @change="handleFiles($event.target.files)"
      />

      <!-- Скрытые поля с данными, которые будут отправлены с формой на сервер -->

      <!--
            <input
              type="hidden"
              :name="name + '_delete'"
              :value="deleteUploaded ? 1 : 0"
            />
      -->

      {{
        /* TODO поддержка нескольких файлов */
      }}
      <input type="hidden" :name="inputUidName" :value="uniqid" />

      <input
        :name="inputConvertToPdfName"
        :value="inputConvertToPdfValue ? 1 : 0"
        type="hidden"
      />
    </div>
  </div>
</template>
<script>
import {mapActions} from "vuex";
import HmErrors from "./../hm-errors";
import HmCropper from "@/components/media/hm-cropper";
import SvgIcon from "@/components/icons/svgIcon"
import { cloneDeep, random, merge } from "lodash"
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import HmFileUploadedItem from "./partials/uploadedItem";

export default {
  name: "HmFile",
  components: { HmFileUploadedItem, HmErrors, HmCropper, SvgIcon },
  mixins: [VueMixinConfigColors],
  props: {
    name: {
      type: String,
      required: true
    },
    allowConversion: {
      type: Boolean,
      default: true
    },
    /** @see HM_View_Helper_VueFile, $params */
    params: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: Object,
      default: () => {}
    },
    /**
     * @see HM_DataType_Form_Element_Vue_UploadedItem
     * @see HM_Resource_ResourceModel::getFileInfo() - пример наполнения
     **/
    uploadedItems: {
      type: Array,
      default: () => {
        return []
      }
    }
  },
  data() {
    return {
      currentUploadedItems: [],
      label: this.params.label,
      description: this.params.description,
      required: this.params.required,
      // previewUrl: this.params.preview_url,
      uploadUrl: this.params.upload_url,
      fileSizeLimit: this.params.file_size_limit || null,
      fileSizeLimitForUser: this.params.file_size_limit_string || null,
      fileUploadLimit: this.params.file_upload_limit || 1,
      crop: false,
      // deleteUploaded: false,
      isLoading: false,
      // showBasket: this.params.preview_url,
      uniqid: this.params.formData.uniqid || null
    };
  },
  computed: {
    inputUidName() {
      return this.fileUploadLimit > 1 ? `${this.name}` : this.name;
    },

    inputConvertToPdfName() {
      return `${this.name}_convertToPdf`;
    },

    inputConvertToPdfValue() {
      for (let uploadedItem of this.currentUploadedItems) {
        if (uploadedItem.file.convertToPdf) {
          return true;
        }
      }
      return false;
    },

    filesCount() {
      return this.currentUploadedItems.length;
      // return this.previewUrl
      //   ? this.currentUploadedItems.length + 1
      //   : this.currentUploadedItems.length;
    },

    accept() {
      return this.params.inputAttrs && this.params.inputAttrs.accept
        ? this.params.inputAttrs.accept
        : "*/*";
    },

    allowExtensions() {
      let extensions = this.params.file_types_extensions;
      return extensions ? extensions.join() : null;
    },
    fileUploadReplaceMode() {
      return this.fileUploadLimit <= 1 && this.filesCount === 1
    },
    fileUploadLimitReached() {
      return this.filesCount >= this.fileUploadLimit;
    },
    btnUploadText() {
      if (this.fileUploadLimit <= 1 && this.fileUploadLimitReached) {
        return "Заменить";
      }
      return "Загрузить";
    },
    btnFileSelectTooltipText() {
      if (this.fileUploadLimit <= 1) {
        return  "";
      }
      if (this.fileUploadLimitReached) {
        return "Уже добавлено максимальное количество файлов";
      }
      if (this.filesCount === 0) {
        return "Файл не выбран";
      }
      return "Выбрать файл"
    },
    // addedFiles() {
    //   // return this.files.filter(item => item.file !== null);
    //
    //   // return this.files.map(
    //   //   origItem => {
    //   //     let item = cloneDeep(origItem)
    //   //     if (item.file) {
    //   //       for (let key of ['name', 'size']) {
    //   //         item[key] = item.file[key];
    //   //       }
    //   //       item.file = '[flattened]';
    //   //     }
    //   //     return item;
    //   //   }
    //   // )
    //
    //   return this.currentUploadedItems.map(
    //     origItem => {
    //       let item = cloneDeep(origItem);
    //       return item;
    //     }
    //   )
    // },
    colorFieldTitleInfoIcon() {
      return this.colors.primaryLight;
    },
    colorFieldTitleLabel() {
      return this.colors.textContrast;
    },
  },
  created() {
    this.resetCrop(this.params.crop);
    if (this.uploadedItems) {
      let uploadedItems = _.isObject(this.uploadedItems) ? Object.values(this.uploadedItems) : this.uploadedItems;
      this.currentUploadedItems = uploadedItems.map(_uploadedItem => {
        let uploadedItem = cloneDeep(_uploadedItem)
        if (!uploadedItem.id) {
          uploadedItem.id = random(10**6, 10**7-1);
        }
        return uploadedItem;
      })
    };
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),

    open() {
      this.$refs.fileInput.click();
    },

    clearFileInput() {
      this.$refs.fileInput.value = "";
    },

    handleFiles(fileList) {
      if (!fileList.length) return;

      for (let i = 0, numFiles = fileList.length; i < numFiles; i++) {
        const file = fileList[i];

        if (!this.isValid(file)) return this.clearFileInput();

        if (this.fileIsImage(file) && this.crop) {
          this.setCrop(file);
        }

        this.uploadFile(file);
      }
    },

    async uploadFile(fileObject) {
      if (!this.uploadUrl) {
        return false;
      }

      if (this.fileUploadReplaceMode) {
        let deleteResult = await this.fileDeleteRequest(this.currentUploadedItems[0]);
        if (!deleteResult) {
          console.error("uploadFile error: can't delete existing file");
          return true
        }
      }

      return await this.loadingLock(async () => {

        let formData = this.createNewFormData();
        formData.append(this.name, fileObject, fileObject.name);

        try {
          let requestResult = await this.$axios.post(this.uploadUrl, formData);
          if (requestResult.status === 200 && requestResult.data) {
            let data = requestResult.data;

            let fileData = {
              id: data.id,
              fileObject: fileObject,
              file: merge({}, data, {
                name: fileObject.name,
                size: fileObject.size,
                previewUrl: fileObject.previewUrl,
                url: this.fileIsImage(fileObject)
                  ?  window.URL.createObjectURL(fileObject)
                  : fileObject.url,
                mimeType: fileObject.mimeType,
              })
            };

            this.currentUploadedItems.push(fileData);
          }

        } catch (err) {
          this.addErrorAlert("Произошла ошибка! Файл не загружен.");
          return false;
        }
      });
    },

    createNewFormData() {
      let formData = new FormData();
      let formDataDefault = this.params.formData;

      if (!formDataDefault) return formData;

      for (let propName in formDataDefault) {
        if (!formDataDefault.hasOwnProperty(propName)) continue;
        formData.append(propName, formDataDefault[propName]);
      }

      return formData;
    },

    isValid(file) {
      let isValidFileSize = this.isValidFileSize(file.size);
      let isValidFileType = this.isValidFileType(file.type);

      if (!isValidFileSize) {
        this.addErrorAlert(
          `Превышен макимально допустимый размер файла: ${
            this.fileSizeLimitForUser
          }`
        );
      }

      if (!isValidFileType) {
        this.addErrorAlert("Неверный тип файла");
      }

      return isValidFileSize && isValidFileType;
    },

    isValidFileSize(fileSize) {
      return this.fileSizeLimit ? fileSize <= this.fileSizeLimit : true;
    },

    isValidFileType(type) {
      return this.accept === "*/*" ? true : this.accept.includes(type);
    },
    fileDelete(file) {
      let fileKey = this.currentUploadedItems.findIndex(item => item.id === file.id);
      if (fileKey !== -1) this.currentUploadedItems.splice(fileKey, 1);
      this.clearFileInput()
    },
    async fileDeleteRequest(file) {
      // from event
      let fileToDelete = file.file ? file.file : file;

      if (!fileToDelete.deleteUrl) {
        this.fileDelete(file);
        return true
      }

      return await this.loadingLock(async () => {
        try {
          // удаление из временного хранилища
          let requestResult = await this.$axios.delete(fileToDelete.deleteUrl);
          if (requestResult.status === 200) {
            this.fileDelete(file);
            return true;
          }
        } catch(err) {
          this.addErrorAlert("Произошла ошибка! Файл не удален.");
          return false;
        }
      });
    },
    async loadingLock(fn) {
      if (this.isLoading) {
        //return false
      }

      this.isLoading = true;

      let result = await fn();

      this.isLoading = false;
      return result
    },
    // delUploadPreview() {
    //   this.deleteUploaded = true;
    //   this.previewUrl = null;
    //   this.showBasket = false;
    // },
    fileIsImage(file) {
      let imageTypes = ["image/jpg", "image/png", "image/gif", "image/jpeg"];
      return imageTypes.includes(file.type);
    },

    resetCrop(cropData) {
      if (!cropData) return (this.crop = false);

      this.crop = {
        ratio: cropData.ratio || null,
        isOpen: false,
        src: "",
        file: null,
        fileObject: null,
        id: null
      };
    },

    setCrop(fileObject) {
      let url = window.URL.createObjectURL(fileObject);
      this.crop.url = url;
      this.crop.file = fileObject;
      this.crop.isOpen = true;
    },

    cancelCrop() {
      this.resetCrop(this.crop);
      this.clearFileInput();
    },

    saveCrop(fileObject) {
      fileObject.name = this.crop.file.name;
      this.uploadFile(fileObject);
      this.resetCrop(this.crop);
    },

    // TODO переделать на vuex
    onUploadedItemUpdate(newUploadedItem) {
      for (let i=0; i < this.currentUploadedItems.length; i++) {
        if (newUploadedItem.id === this.currentUploadedItems[i].id) {
          this.$set(this.currentUploadedItems, i, newUploadedItem);
          return;
        }
      }

      console.error("hm-file:onUploadedItemUpdate: can't find original", newUploadedItem);
    }
  }
};
</script>
<style lang="scss">
.hm-file {
  display: flex;
  flex-wrap: wrap;
  margin-left: 20px;

  > * {
    margin-bottom: 26px;
  }

  &__panel {
    margin-right: 16px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    margin-bottom: 0;

    button {
      max-width: 220px;
    }
  }

  &_info {
    align-self: stretch;
    padding: 0;
  }

  &__list {
    display: flex;
    flex-wrap: wrap;
  }

  .hm-file__label_title {
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02rem;
  }
  .hm-file_result {
    display: none;
  }
  .hm-file_btn-upload {
    margin: 0 0 8px 0;
    box-shadow: none;
    height: 29px !important;
    min-width: 170px !important;

    font-weight: normal;
    font-size: 16px;
    line-height: 24px;
    text-transform: none;
  }
  .hm-file__label {
    display: flex;
    align-items: center;
    position: unset;
    transform: unset;
    margin-bottom: 16px;

    .v-tooltip {
      margin-left: 5px;
    }
    i {
      cursor: pointer;
    }
  }

  /*
  .hm-file_list {
    .v-list__tile {
      height: auto;
    }
    .v-image {
      height: 80px;
      flex: 0 1 80px;
      margin-right: 10px;
    }
  }
  */

  .hm-file_preview-url {
    max-width: 280px;
    display: block;
  }
  .hm-file_sample {
    margin-bottom: 10px;

    .hm-file-sample_wrapper {
      line-height: 14px;
    }

    a {
      font-size: 12px;
      text-decoration: none;
    }

  }
  .preview-container {
    position: relative;
    margin-top: 20px;
    margin-bottom: 10px;
  }
  .preview-left {
    float: left;
  }

  /*
    .preview-clear {
      clear: both;
    }
  */
}

.hm-file_tooltip_content {
  .hm-file_requirements {
    /* list-style: none; */
    margin-top: 4px;
  }
}

</style>
