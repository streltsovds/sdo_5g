<template>
  <v-dialog
    :value="isShown"
    :max-width="maxWidth"
    :content-class="contentClass"
    :persistent="persistent"
    :fullscreen="fullscreen"
    @input="onShownChange"
  >
    <div v-if="closeBtn" class="hm-modal_close">
      <v-btn text icon color="#2D2D2D" @click="$emit('close')">
        <v-icon size="20">close</v-icon>
      </v-btn>
    </div>
    <div class="hm-modal_content"><slot /></div>
  </v-dialog>
</template>
<script>
export const HM_MODAL_IS_SHOWN_WINDOW_CSS_CLASS = 'hm-modal-is-shown';

export default {
  name: 'HmModal',
  props: {
    isShown: {
      type: Boolean,
      default: false
    },
    className: {
      type: String,
      default: ""
    },
    persistent: {
      type: Boolean,
      default: false
    },
    closeBtn: {
      type: Boolean,
      default: false
    },
    fullscreen: {
      type: Boolean,
      default: false
    },
    maxWidth: {
      type: Number,
      default: 350,
    },
  },
  data() {
    return {
    };
  },
  computed: {
    contentClass() {
      return `hm-modal ${this.className}`;
    }
  },
  watch: {
    isShown(newValue) {
      if (newValue) {
        document.documentElement.classList.add(HM_MODAL_IS_SHOWN_WINDOW_CSS_CLASS)
      } else {
        document.documentElement.classList.remove(HM_MODAL_IS_SHOWN_WINDOW_CSS_CLASS)
      }
    },
  },
  methods: {
    // обработка закрытия диалога кликом по свободному пространству
    onShownChange(newShown) {
      if (!newShown) {
        this.$emit('close')
      }
    }
  }
};
</script>
<style lang="scss">
  .hm-modal {
    position: relative;
  }
  .hm-card-modal__text .v-list {
    background: inherit !important;
  }
  .hm-modal_close {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 500;
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    > button {
      width: 100% !important;
      height: 100% !important;
      margin: 0;
    }
  }
</style>
