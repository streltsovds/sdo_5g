<template>
  <v-dialog
    v-model="status"
    persistent
    :contentClass="classDialog"
  >
    <v-card style="height: 100%">
      <v-card-title class="hm-dialog__header">
        <div v-if="semanticAccent === 'info' && title" class="hm-dialog__title-wrapper">
          <icon-info color="#4A90E2" class="hm-dialog__icon" />
          <p class="hm-dialog__title"> {{ title }} </p>
        </div>
        <div v-else-if="semanticAccent === 'warning'" class="hm-dialog__title-wrapper">
          <icon-warning class="hm-dialog__icon" />
          <p v-if="title" class="hm-dialog__title"> {{ title }} </p>
          <p v-else class="hm-dialog__title">Предупреждение</p>
        </div>
        <div v-else-if="semanticAccent === 'error'" class="hm-dialog__title-wrapper">
          <icon-warning color="red" class="hm-dialog__icon" />
          <p v-if="title" class="hm-dialog__title"> {{ title }} </p>
          <p v-else class="hm-dialog__title">Ошибка</p>
        </div>
        <div v-else-if="semanticAccent === 'none'" class="hm-dialog__title-wrapper">
          <p v-if="title" class="hm-dialog__title"> {{ title }} </p>
        </div>
        <v-btn
          v-if="buttonClose"
          icon
          @click="closeDialog"
          class="hm-dialog__button-close"
        >
          <v-icon dark>
            mdi-close
          </v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="hm-dialog__content">
        <slot name="content"></slot>
      </v-card-text>
      <v-card-actions class="hm-dialog__buttons-wrapper">
        <slot name="buttons"></slot>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import iconWarning from "@/components/icons/items/iconWarning.vue";
import iconInfo from "@/components/icons/items/iconInfo.vue";
export default {
  components: {
    iconWarning,
    iconInfo
  },
  props: {
    status: {
      type: Boolean,
      required: false,
    },
    size: {
      type: String,
      default: "small",
    },
    title: {
      type: String,
      required: false,
    },
    semanticAccent: {
      type: String,
      required: "info",
    },
    buttonClose: {
      type: Boolean,
      required: false,
    }
  },
  computed: {
    classDialog: function () {
      if(this.size === "small") {
        return "hm-dialog hm-dialog_size_small"
      } else if(this.size === "medium") {
        return "hm-dialog hm-dialog_size_medium"
      } else if(this.size === "large") {
        return "hm-dialog hm-dialog_size_large"
      } else {
        return ""
      }
    }
  },
  methods: {
   closeDialog() {
    this.$emit("close");
   }
  }
}
</script>

<style lang="scss">
  .hm-dialog {
    display: flex;
    max-height: 80vh !important;

    &_size {
      &_small {
        max-width: 400px;
        // height: 300px;
      }
      &_medium {
        max-width: 600px;
        // height: 450px;
      }
      &_large {
        max-width: 800px;
        // height: 500px;
      }
    }
    &__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: nowrap !important;
      padding-right: 16px !important;
    }
    &__title-wrapper {
      display: flex;
      align-items: center;
      width: calc(100% - 36px);
    }
    &__icon {
      margin-right: 10px;
    }
    &__title {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin: 0 !important;
      width: calc(100% - 34px);
    }
    &__button-close {
      margin-left: auto !important;
    }
    &__content {
      max-height: calc(80vh - 122px);
      overflow: auto;
    }
    &__buttons-wrapper {
      display: flex;
      align-items: center;
      justify-content: flex-end !important;
      padding-bottom: 16px !important;
    }
  }
</style>
