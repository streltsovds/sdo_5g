<template>
  <div class="hm-snackbar">
    <v-snackbar
      v-model="isOpen"
      multi-line
      top
      :timeout="timeout"
      :color="_colorMixin.background"
      :style="{ 'margin-top': `${marginTop}px`, 'min-width' : `${$vuetify.breakpoint.xs ? '300px !important' : '500px !important'}` }"
    >
    <div class="hm-snackbar__content">
      <div class="v-snackbar__icon">
        <svg-icon :name="_colorMixin.iconName" :color="_colorMixin.iconColor"/>
      </div>
      <span>{{ text }}</span>
    </div>

<!--      <v-btn icon @click="isOpen = false">-->
<!--        <v-icon>close</v-icon>-->
<!--      </v-btn>-->
    </v-snackbar>
  </div>
</template>
<script>
import STATUS from "./status";
import configColors from "../../../../utilities/configColors";
import VueMixinConfigColors from "../../../../utilities/mixins/VueMixinConfigColors";
import SvgIcon from "../../../icons/svgIcon";

export default {
  components: {SvgIcon},
  mixins: [VueMixinConfigColors],
  props: {
    text: {
      type: String,
      required: true
    },
    timeout: {
      type: Number,
      default: 3000
    },
    type: {
      validator: value => {
        return [
          STATUS.INFO,
          STATUS.SUCCESS,
          STATUS.ERROR,
          STATUS.WARNING,
        ].includes(value);
      }
    }
  },
  data() {
    return {
      isOpen: true,
      marginTop: 0
    };
  },
  computed: {
    _colorMixin() {
      switch (this.type) {
        case STATUS.SUCCESS:
          return {
            background:this.getColor(configColors.successBackground),
            iconColor: this.themeColors.success,
            iconName: 'success'
          };
        case STATUS.INFO:
          return {
            background: this.getColor(configColors.infoBackground),
            iconColor: this.themeColors.info,
            iconName: 'info-inverted'
          };
        case STATUS.ERROR:
          return {
            background: this.getColor(configColors.errorBackground),
            iconColor: this.themeColors.error,
            iconName: 'error'
          };
        case STATUS.WARNING:
          return {
            background: this.getColor(configColors.warningBackground),
            iconColor: this.themeColors.warning,
            iconName: 'warning'
          };
        default:
          return {
            background:this.getColor(configColors.successBackground),
            iconColor: this.themeColors.success,
            iconName: 'success'
          }
      }
      },
  },
  watch: {
    isOpen(v) {
      if (!v) this.$emit("remove");
    }
  },
  mounted() {
    this.calcMarginTop();
    window.addEventListener("resize", this.calcMarginTop);
  },
  methods: {
    calcMarginTop() {
      let mainNav = document.querySelector(".main-nav");
      this.marginTop = mainNav ? mainNav.offsetHeight + 15 : 0;
    }
  },
};
</script>
<style lang="scss">
.hm-snackbar {
  opacity: 1;
  &__content{
    display: flex;
  }
  > div {
    > div {
      box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 1px 4px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.14) !important;
      .v-snack__content {
        display: flex;
        width: 622px;
        padding: 18px 16px 18px 18px !important;
        min-height: 60px !important;
        justify-content: flex-start;
        align-items: flex-start;
        .v-snackbar__icon {
          width: 24px;
          height: 24px;
        }
        span {
          color: #1E1E1E;
          padding-left: 16px;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
        }
      }
    }
  }
}

</style>
