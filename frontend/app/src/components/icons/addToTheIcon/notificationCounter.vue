<template>
  <div class="notification-counter" v-if="_counterNotification && _counterNotification > 0">
    <span>{{ _counterNotification >= 10 ? '9+' : _counterNotification}}</span>
  </div>
</template>

<script>
export default {
  name: "notificationCounter",
  computed: {
    _counterNotification() {
      return this.$root.view.numberOfNotifications
    }
  }

}
</script>

<style lang="scss">
  .notification-counter {
    position: absolute;
    top: 0;
    right: .4rem;
    width: 16px;
    height: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    background: #EE423D;
    font-size: 12px;
    text-align: center;
  > span {
    font-weight: bold;
    font-size: 9px;
    line-height: 24px;
    text-align: center;
    letter-spacing: 0.16px;
    color: #FFFFFF;
  }
  }
</style>
