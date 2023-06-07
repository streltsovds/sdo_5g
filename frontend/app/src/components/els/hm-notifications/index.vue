<template>
  <div class="hm-notifications">
    <hm-snackbar
      v-for="notification in notices"
      :key="notification.id"
      :type="notification.type"
      :text="notification.text"
      @remove="remove(notification.id)"
    >
    </hm-snackbar>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import STATUS from "./partials/status";
import HmSnackbar from "./partials/snackbar";

export default {
  components: { HmSnackbar },
  props: {
    notifications: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    notices() {
      return this.$store.state.notifications.items;
    }
  },
  created() {
    let items = this.$store.state.notifications.items;
    if (items && items.length > 0) this.resetNotifications();
    if (this.notifications && this.notifications.length > 0) {
      this.addNotifications();
    }
  },
  methods: {
    ...mapActions("notifications", [
      "removeNotification",
      "resetNotifications",
      "addNotification"
    ]),
    remove(id) {
      this.$nextTick(() => this.removeNotification(id));
    },
    addNotifications() {
      this.notifications.forEach(notification => {
        switch (typeof notification) {
          case "string": {
            this.addNotification({
              text: notification,
              type: STATUS.SUCCESS
            });
            break;
          }
          case "object": {
            if (notification.hasOwnProperty("message")) {
              let message = notification["message"];
              this.addNotification({
                text: message,
                type: notification.hasOwnProperty("type")
                  ? notification["type"]
                  : STATUS.INFO
              });
            }
            break;
          }
        }
      });
    }
  }
};
</script>
<style lang="scss">
.hm-notifications {
  z-index: 10;
  position: fixed;
  font-family: Roboto, sans-serif;
}
</style>
