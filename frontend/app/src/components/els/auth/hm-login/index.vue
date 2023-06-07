<template>
  <div class="hm-login"><slot></slot></div>
</template>
<script>
import { mapActions } from "vuex";
export default {
  created() {
    this.$root.$on("hmFormAuthorization", () => this.initUser());
  },
  methods: {
    ...mapActions("user", ["initUser"])
  }
};
</script>
<style>
.hm-login {
  margin-top: auto;
}
</style>
