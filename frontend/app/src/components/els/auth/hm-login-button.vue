<template>
  <div v-if="isShow">
    <v-btn
      text
      :background-color="color"
      @click="openModal"
      style="text-transform: none"
    >
  <!--    <icon-enter :color="color" style="margin-right: 10px"> </icon-enter>-->
      <svg-icon
        name="enter"
        :color="color"
        style="margin-right: 10px"
      >
      </svg-icon>
      <span>Войти</span>
    </v-btn>
    <div class="hm-login-modal" @click.self="openModal" v-if="status">
      <div style="position: relative;">
        <v-btn small fab elevation="0" class="hm-login-modal__button-close" @click="openModal">
          <v-icon class="hm-login-modal__button-close-icon">close</v-icon>
        </v-btn>
        <slot></slot>
      </div>
    </div>
  </div>
</template>
<script>
// import { mapActions } from "vuex";
import svgIcon from "@/components/icons/svgIcon";
export default {
  components: {
    svgIcon
  },
  props: {
    backgroundColor: {
      type: String,
      default: "primary"
    },
    color: {
      type: String,
      default: "#fff"
    }
  },
  data() {
    return {
      status: false,
      isShow: true
    }
  },
  mounted() {
    if(window.location.pathname.includes('/login')) {
      this.isShow = false;
    }
  },
  methods: {
    openModal() {
      this.status = !this.status;
    }
    // ...mapActions("user", ["logoutUser"])
  }
};
</script>
<style lang="scss">
    .hm-login-modal {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
      position: fixed;
      top: 0;
      left: 0;
      padding: 26px;
      &__button-close {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 10;
        background-color: rgba(0, 0, 0, 0) !important;
        &-icon {
          color: rgba(0, 0, 0, 0.87) !important;
        }
      }
    }
    .hm-app__btn-exit {
        .v-btn__content {
          span {
            font-weight: 300;
          }
        }
    }
</style>
