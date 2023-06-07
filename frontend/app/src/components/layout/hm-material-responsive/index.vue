<template>
  <v-card
    class="hm-material-responsive__wrapper"
    :class="{
      'state-fullscreen': fullscreen,
    }"
    :flat="fullscreen"
    :style="{
      height: fullscreen ? '100%' : maxHeight,
    }"
  >
    <div
      class="hm-material-responsive"
      :class="{
        'state-fullscreen': fullscreen,
        'state-full-height': fullHeight,
      }"
      :style="{
        maxHeight: fullscreen ? '100%' : maxHeight,
        minHeight: fullscreen ? null : (fullHeight ? maxHeight : minHeight),
        background: colorBackground,
      }"
    >
      <!--    <v-style-head-once name="style-hm-material-responsive">

      </v-style-head-once>-->

      <div class="hm-material-responsive__panel"
           v-if="fullScreenAllowed"
           :class="{'hm-material-responsive__panel-fullscreen': fullscreen}"
           :style="{
             color: colorText,
           }"
      >
        <div
          class="hm-material-responsive__panel-title"
          v-if="fullscreen"
        >
          {{ title }}
        </div>
        <div class="hm-material-responsive__fullscreen-switcher">
          <!--          @click="toggleFullscreen"-->

          <hm-hotkey
            :filter-target="true"
            @pressed="onBtnFullscreenInput(!currentValue)"
            keys="Alt+Enter"
            on-event-name="keydown"
          >
            <btn-fullscreen
              :value="currentValue"
              @input="onBtnFullscreenInput"
              :type="type"
            />
          </hm-hotkey>
        </div>
      </div>

      <div class="hm-material-responsive__content">
        <slot v-if="!iframe" />
        <div v-else class="hm-material-url">
          <iframe :src="iframe" width="100%" height="97%" frameborder="0" />
        </div>
      </div>
      <div class="hm-material-responsive__title" v-if="showTitleBelow && !fullscreen">
        <slot name="title-below">
          {{ title }}
        </slot>
      </div>
    </div>
  </v-card>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

// import VStyleHeadOnce from "@/components/helpers/v-style-head-once";
import BtnFullscreen from "./_btnFullscreen";
import HmHotkey from "@/components/helpers/hm-hotkey"

/**
 * Рамка вокруг материала, позволяет развернуть его на весь экран
 */
export default {
  name: "HmMaterialResponsive",
  components: {
    // VStyleHeadOnce,
    BtnFullscreen,
    HmHotkey,
  },
  mixins: [VueMixinConfigColors],
  props: {
    /** v-model, full screen */
    type: {
      type: String,
      default: '',
    },
    value: {
      type: null,
      default: false,
    },
    maxHeight: {
      type: [Number, String],
      default: "100vh",
    },
    minHeight: {
      type: [Number, String],
      default: "100vh",
    },
    /** minHeight = maxHeight if true */
    fullHeight: {
      type: null,
      default: false,
    },
    title: {
      type: String,
      required: true,
    },
    iframe: {
      type: String,
      required: true,
      default: '',
    },
    showTitleBelow: {
      type: null,
      default: true,
    },
    fullScreenAllowed: {
      type: null,
      default: true,
    },
  },
  data() {
    return {
      dirtyValue: null,
    };
  },
  computed: {
    currentValue() {
      if (!this.fullScreenAllowed) {
        return false;
      }
      return this.dirtyValue != null ? this.dirtyValue : this.value;
    },
    fullscreen() {
      return this.currentValue;
    },
    colorText() {
      return this.fullscreen ? "#FFF" : null;
    },
    colorBackground() {
      if(this.type !== "html") return this.fullscreen ? this.getColor(configColors.fullscreenBackground) : null;
    },
  },
  watch: {
    value() {
      this.dirtyValue = null;
    },
  },
  mounted(){
    console.log(this.iframe);
  },
  methods: {
    changeValue(newValue) {
      this.dirtyValue = newValue;
      this.$emit("input", this.currentValue);
    },
    // toggleFullscreen() {
    //   this.$emit("input", !this.value);
    // },
    onBtnFullscreenInput(newValue) {
      this.changeValue(newValue)
    },
  },
};
</script>

<style lang="scss">
.hm-material-url {
  overflow: auto !important;
  & iframe {
    height: 97% !important;
  }
}
.hm-material-responsive {
  display: flex;
  flex-direction: column;

  padding: 14px 26px;

  &__panel {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding-bottom: 12px;

    &-title {
      flex-grow: 1;
    }
  }

  &__fullscreen-switcher {
    display: flex;
    justify-content: flex-end;
  }

  /*
    &__content-with-title {
      display: flex;
      flex-direction: column;

      flex-basis: 0%;
      flex-grow: 1;
      overflow: auto;
    }
  */

  &__content {
    flex-basis: 0%;
    flex-grow: 1;
    overflow-x: hidden;
    overflow-y: auto;
    display: flex;

    > * {
      flex-basis: 0%;
      flex-grow: 1;
      overflow: visible;
    }

    iframe {
      border: none;
    }
  }

  &__title {
    font-size: 20px;
    line-height: 28px;
    letter-spacing: 0.02rem;
    margin: 26px 0;
    text-align: center;
  }

  /* still inside .hm-material-responsive */
  .hm-material-image {
    align-items: center;
  }

  .hm-material-html {
    overflow: auto;
    flex-direction: column;
    display: block;
  }

  .hm-material-kbase-course {
    overflow: visible;
  }

  .hm-material-audio {
    display: flex;
    align-items: center;
    justify-content: center;

    audio {
      display: block;
      flex-basis: 600px;
      flex-shrink: 1;
      flex-grow: 0;
    }
  }

  .hm-material-card,
  .hm-material-download,
  .hm-material-video {
    display: flex;
    align-items: flex-end;
    justify-content: center;
  }

  .hm-material-download {
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: min-content;

    &__file-icon {
      margin-bottom: 26px;
    }

    &__title {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02rem;
    }

    &__note {
      font-size: 14px;
      margin-top: 8px;
      min-width: 350px;
      max-width: 600px;
    }

    &__button {
      margin-top: 52px;
    }
  }

  .hm-material-html-slider {
    width: 100%;

    .hm-slides {
      height: 100%;
    }
  }
}

.state-full-height.hm-material-responsive {
  .hm-material-html > iframe:only-child {
    width: 100%;

    /** TODO почему появляется scroll при вставке ссылки с youtube?! */
    height: calc(100% - 7px);
  }

  .hm-material-video > video:only-child {
    width: 100%;
    height: 100%;
  }
}

.state-fullscreen {
  &.hm-material-responsive__wrapper {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 100%;
    z-index: 200;
    overflow: hidden;
    box-shadow: none !important;
    border-radius: 0 !important;
  }

  &.hm-material-responsive {
    height: 100%;
    width: 100%;
    padding: 20px 36px 0;

    .hm-material-image {
      align-items: center;
    }

    .hm-material-html {
      iframe:only-child {
        width: 100%;

        /** TODO почему появляется scroll при вставке ссылки с youtube?! */
        height: calc(100% - 7px);
      }
    }
  }
}

@media(max-width: 960px) {
  .hm-material-responsive {
    &__panel-fullscreen {
      flex-direction: column;
      align-items: flex-end;
      .hm-material-responsive__fullscreen-switcher {
        order: 1;
        width: 100%;
        margin-bottom: 10px;
        & > div {
          width: 100%;
          .hm-material-responsive__btn-fullscreen {
            width: 100%;
          }
        }
      }
      .hm-material-responsive__panel-title {
        order: 2;
        align-self: start
      }
    }
  }
}

@media(max-width: 440px) {
  .hm-material-responsive {
    padding: 16px;
    &__wrapper {
      box-sizing: border-box;
      min-width: calc(100% + 32px);
      padding: 0;
      margin: 0 -16px;
    }
    &__panel {
      justify-content: center;
    }
  }
}
</style>
