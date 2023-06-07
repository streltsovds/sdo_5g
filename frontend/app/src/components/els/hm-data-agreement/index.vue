<template>
  <div class="hm-data-agreement"></div>
</template>
<script>
import events from "../../../utilities/Event";
export default {
  props: {
    content: {
      type: String,
      default: null
    },
    confirmText: {
      type: String,
      default: "Согласен"
    },
    confirmUrl: {
      type: String,
      default: null
    },
    cancelText: {
      type: String,
      default: "Не согласен"
    },
    cancelUrl: {
      type: String,
      default: null
    }
  },
  created() {
    this.$root.$on(events.MOUNTED_MODAL_CONFIRM, this.showAgreementModal);
  },
  methods: {
    showAgreementModal() {
      this.$confirmModal({
        text: this.content,
        confirmText: this.confirmText,
        cancelText: this.cancelText,
        persistent: false
      })
        .then(() => {
          if (this.confirmUrl) window.location.href = this.confirmUrl;
        })
        .catch(() => {
          if (this.cancelUrl) window.location.href = this.cancelUrl;
        });
    }
  }
};
</script>
