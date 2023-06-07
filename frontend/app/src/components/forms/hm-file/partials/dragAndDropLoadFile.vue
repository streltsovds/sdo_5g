<template>
  <div>
    <v-card
      class="hm-drag-and-drop-files"
      :class="{ highlight: isActive }"
      @dragenter="isActive = true"
      @dragover="isActive = true"
      @dragleave="isActive = false"
      @drop="
        isActive = false;
        handleDrop($event);
      "
    >
      <v-card-text class="hm-drag-and-drop-files_content">
        <span
          >Загрузите файлы с помощью диалога выбора файлов или перетащив нужные
          файлы в выделенную область</span
        >
        <input
          ref="input"
          class="hm-drag-and-drop-files_file-input"
          type="file"
          multiple
          accept="image/*"
          @change="handleFiles"
        />
        <v-btn color="primary" @click="$refs.input.click()"
          >Выбрать изображения</v-btn
        >
        <v-progress-circular
          v-if="progressValue && progressValue > 0"
          :rotate="360"
          :size="50"
          :width="5"
          :value="progressValue"
          color="primary"
        >
          {{ progressValue }}
        </v-progress-circular>
      </v-card-text>
    </v-card>
    <v-card>
      <v-card-text>
        <div
          v-if="previews && previews.length > 0"
          class="hm-drag-and-drop-files_gallery"
        >
          <img v-for="(preview, key) in previews" :key="key" :src="preview" />
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
export default {
  props: {
    url: {
      type: String,
      default: window.location.pathname
    }
  },
  data() {
    return {
      isActive: false,
      previews: [],
      progressValue: 0,
      uploadProgress: []
    };
  },
  mounted() {
    let dropArea = this.$el;

    ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
      dropArea.addEventListener(eventName, this.preventDefaults, false);
    });
  },
  methods: {
    preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    },
    handleDrop(e) {
      let dt = e.dataTransfer;
      let files = dt.files;
      this.handleFiles(files);
    },
    handleFiles(files) {
      console.log("files: ", files);
      files = [...files];
      this.initializeProgress(files.length);
      files.forEach(this.uploadFile);
      files.forEach(this.previewFile);
    },
    uploadFile(file, i) {
      console.log("uploadFile: ", file);
      let formData = new FormData();
      formData.append("file", file);
      this.progress = 0;
      let config = {
        onUploadProgress: (event, i) => this.progressEvent(event, i)
      };

      this.$axios
        .post("/test/url", formData, config)
        .then(() => {
          console.log("Ready");
        })
        .catch(() => {
          console.error("Error");
          this.progressDone();
        });
    },
    previewFile(file) {
      let reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend = () => {
        //проверять тип файла и если не картинка, то вставлять картинку-заглушку
        this.previews.push(reader.result);
      };
    },
    initializeProgress(countFiles) {
      this.progressValue = 0;
      this.uploadProgress = [];

      for (let i = countFiles; i > 0; i--) {
        this.uploadProgress.push(0);
      }
    },
    progressEvent(event, i) {
      let percent = Math.floor((event.loaded / event.total) * 100) || 100;
      this.updateProgress(i, percent);
    },
    updateProgress(fileNumber, percent) {
      this.uploadProgress[fileNumber] = percent;
      let total =
        this.uploadProgress.reduce((total, current) => total + current, 0) /
        this.uploadProgress.length;
      this.progressValue = total;
    }
  }
};
</script>
<style lang="scss">
.hm-drag-and-drop-files {
  border: 2px dashed #ccc !important;
  box-shadow: none;
  min-height: 200px;
  display: flex;

  &.highlight {
    background-color: rgba(#000, 0.1);
  }

  .hm-drag-and-drop-files_content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }
}

p {
  margin-top: 0;
}

.hm-drag-and-drop-files_gallery {
  margin-top: 10px;
}
.hm-drag-and-drop-files_gallery img {
  width: 150px;
  margin-bottom: 10px;
  margin-right: 10px;
  vertical-align: middle;
}
.button {
  display: inline-block;
  padding: 10px;
  background: #ccc;
  cursor: pointer;
  border-radius: 5px;
  border: 1px solid #ccc;
}
.button:hover {
  background: #ddd;
}
.hm-drag-and-drop-files_file-input {
  display: none;
}
</style>
