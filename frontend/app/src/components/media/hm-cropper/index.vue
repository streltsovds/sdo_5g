<template>
  <v-layout
    class="hm-cropper"
    justify-center
  >
    <v-dialog
      class="hm-cropper__dialog"
      :value="dialog"
      fullscreen
      hide-overlay
      transition="dialog-bottom-transition"
      style="color: #001"
    >
      <v-card class="hm-cropper__dialog__body">
        <v-toolbar dark color="primary">
          <v-btn
            icon
            dark
            :small="$vuetify.breakpoint.xsOnly"
            @click="$emit('cancel')"
          >
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title
            :class="{ 'subheading ml-0': $vuetify.breakpoint.xsOnly }"
            >Кадрирование</v-toolbar-title
          >
          <v-spacer></v-spacer>
          <v-toolbar-items>
            <v-btn dark text :small="$vuetify.breakpoint.xsOnly" @click="save">
              Сохранить
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <v-card
          :class="addCssClassesPrefixed('hm-cropper__body', {
            '--breakpoint-md-and-down': $vuetify.breakpoint.mdAndDown,
          })"
        >
          <div class="hm-cropper__source-img">
            <vue-cropper
              ref="cropper"
              :src="src"
              :crop="crop"
              :aspect-ratio="ratio"
              :responsive="true"
              :min-container-height="250"
              :scalable="true"
              preview=".hm-cropper__result-img"
              drag-mode="move"
            >
            </vue-cropper>
          </div>
          <div class="hm-cropper__result-img" />
        </v-card>
      </v-card>
    </v-dialog>
  </v-layout>
</template>
<script>
import VueCropper from "vue-cropperjs";
import addCssClassesPrefixed from "@/utilities/addCssClassesPrefixed";

export default {
  name: "HmCropper",
  components: { VueCropper },
  props: {
    src: {
      type: String,
      required: true
    },
    ratio: {
      type: Number,
      default: 1
    },
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      cropImg: null,
      dialog: this.isOpen
    };
  },
  watch: {
    isOpen(v) {
      this.dialog = v;
    },
    src() {
      this.croppperReplace();
    }
  },
  mounted() {
    window.addEventListener("resize", () => this.croppperReplace());
  },
  methods: {
    addCssClassesPrefixed,
    crop() {
      let croppedCanvas = this.$refs.cropper.getCroppedCanvas();
      if (croppedCanvas) this.cropImg = croppedCanvas.toDataURL();
    },
    save() {
      let croppedCanvas = this.$refs.cropper.getCroppedCanvas();
      if (!croppedCanvas) return;
      croppedCanvas.toBlob(file => {
        this.$emit("save", file);
      });
    },
    croppperReplace() {
      this.$refs.cropper.replace(this.src);
    }
  }
};
</script>
<style lang="scss">
$result-img-side: 200px;
$img-margin: 12px;

.hm-cropper__dialog__body {
  height: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
}

.hm-cropper__body {
  height: 100%;
  //height: calc(100% - 56px);
  width: 100%;
  //position: fixed;
  display: flex;

  flex-shrink: 1;
  flex-grow: 0;
  overflow: hidden;
  align-content: stretch;
}

.hm-cropper__source-img,
.hm-cropper__result-img {
  margin: $img-margin;
  border: 1px solid rgba(grey, 0.2);
}
.hm-cropper__result-img {
  width: $result-img-side;
  height: $result-img-side;
  overflow: hidden;
  align-self: center;
  flex-basis: $result-img-side;
  flex-shrink: 0;

  /** темнее */
  border-color: rgba(128, 128, 128, 0.4);
  border-radius: 3px;
}

.hm-cropper__source-img {
  flex-grow: 1;

  > div {
    height: 100%;
  }

  .cropper-container {
    max-height: 100%;
  }

  .cropper-bg {
    //background-image: none;

    /** повторение шашечки. vuetify ломает этот стиль */
    background-repeat: repeat;
  }

  .cropper-modal {
    /** фон шашечки светлее */
    background-color: #fff;
    opacity: 0.6 !important;
  }
}

.hm-cropper__body--breakpoint-md-and-down {
  flex-direction: column;

  .hm-cropper__source-img {
    max-height: calc(100% - #{$result-img-side} - #{$img-margin * 4});
  }
}
</style>
