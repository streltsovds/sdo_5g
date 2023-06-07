<template>
  <hm-modal class-name="hm-modal-confirm" :is-shown="isShow" :persistent="persistent" @close="cancel">
    <v-card>
      <v-card-title v-if="title" class="headline" v-text="title" />
      <v-card-text v-text="text" />
      <v-card-actions v-if="isShow">
        <v-spacer />

        <v-btn
          @click="cancel"
          v-text="cancelText"
          color="green darken-1"
          text="flat"
        ></v-btn>

        <hm-hotkey
          @pressed="confirm"
          keys="Enter|Space"
          filter-target
          on-event-name="keydown"
          prevent-default
          top-priority
        >
          <v-btn
            @click="confirm"
            v-text="confirmText"
            color="green darken-1"
            text="flat"
          ></v-btn>
        </hm-hotkey>

      </v-card-actions>
    </v-card>
  </hm-modal>
</template>
<script>
import HmModal from "./index";
import events from "@/utilities/Event";
import HmHotkey from "@/components/helpers/hm-hotkey";

export default {
  components: {
    HmHotkey,
    HmModal,
  },
  data() {
    return {
      isShow: false,
      title: null,
      text: null,
      confirmText: "Да",
      cancelText: "Нет",
      persistent: false
    };
  },
  mounted() {
    this.$root.$on(events.SHOW_MODAL_CONFIRM, this.open);
    this.$root.$emit(events.MOUNTED_MODAL_CONFIRM);
  },
  beforeDestroy() {
    this.$root.$off(events.SHOW_MODAL_CONFIRM, this.open);
  },
  methods: {
    open({ title, text, confirmText, cancelText, persistent }) {
      this.title = title;
      this.text = text;
      if (confirmText && confirmText.length > 0) this.confirmText = confirmText;
      if (cancelText && cancelText.length > 0) this.cancelText = cancelText;
      if (persistent !== undefined) this.persistent = persistent;
      this.isShow = true;
    },
    close() {
      this.isShow = false;
    },
    cancel() {
      this.close();
      this.$root.$emit(events.CLOSE_MODAL_CONFIRM);
    },
    confirm() {
      this.close();
      this.$root.$emit(events.ACCEPT_MODAL_CONFIRM);
    }
  }
};
</script>
