<template>
  <hm-modal :is-shown="isShow" class-name="hm-alert" @close="remove">
    <div
      class="hm-alert-content"
      :class="{ removing: removing }"
      @mouseover="cancelTimer"
      @mouseleave="setTimer"
      @click="remove"
    >
      <v-card>
        <v-icon :class="'modal-' + status" :color="color" v-text="status" />
        <v-card-text v-text="alert.text" />
      </v-card>
    </div>
  </hm-modal>
</template>
<script>
import HmModal from "@/components/layout/hm-modal";
import { mapActions } from "vuex";
export default {
  components: { HmModal },
  props: {
    alert: {
      type: Object,
      default: () => {}
    },
    disappearing: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      isShow: true,
      timer: {
        fade: null,
        remove: null
      },
      removing: false
    };
  },
  computed: {
    status() {
      switch (this.alert.type) {
        case "info":
          return "info";
        case "success":
          return "check";
        case "error":
          return "cancel";
        default:
          return "info";
      }
    },
    color() {
      let types = ["info", "success", "error"];
      if (types.includes(this.alert.type)) {
        return this.$vuetify.theme[this.alert.type];
      }
      return this.$vuetify.theme.info;
    }
  },
  methods: {
    ...mapActions("alerts", ["removeAlert"]),
    setTimer() {
      if (!this.disappearing) return;
      this.timer.fade = setTimeout(() => {
        this.removing = true;
      }, 3000);
      this.timer.remove = setTimeout(() => {
        this.remove();
      }, 5000);
    },
    cancelTimer() {
      clearTimeout(this.timer.fade);
      clearTimeout(this.timer.remove);
      this.removing = false;
    },
    remove() {
      if (!this.disappearing) return;
      this.cancelTimer();
      this.removeAlert(this.alert);
    }
  }
};
</script>
<style lang="scss">
.hm-modal.hm-alert {
  .hm-alert-content {
    text-align: center;
    transition: opacity 3s;
    &.removing {
      opacity: 0;
    }
    .v-card {
      padding: 15px;
      i {
        font-size: 45px;
      }
    }
  }
}
</style>
