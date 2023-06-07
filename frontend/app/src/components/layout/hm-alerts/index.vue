<template>
  <div class="hm-alerts">
    <hm-alert
      v-for="(alert, i) in this.$store.state.alerts"
      :key="i"
      :alert="alert"
    />
  </div>
</template>
<script>
import HmAlert from "./alert";
export default {
  components: { HmAlert }
};
</script>
