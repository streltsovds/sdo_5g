<template>
  <span class="hm-countdown-element" style="font-size: 20px; line-height: 24px; font-weight: 500">
    <v-slide-y-transition :duration="isIE ? 0 : 150" leave-absolute>
      <span :key="showedTime" :style="{color}">{{ showedTime }}</span>
    </v-slide-y-transition>
<!--    class="hm-countdown-element__delimiter primary&#45;&#45;text darken-3"-->
<!--          :class="color"-->
    <span
      v-if="!noDelimiter"
      class="hm-countdown-element__delimiter"
      :style="{color}"
      >:</span
    >
  </span>
</template>

<script>
export default {
  props: {
    time: Number,// eslint-disable-line
    noDelimiter: Boolean,
    color:String
  },
  computed: {
    showedTime() {
      if (this.time < 10) {
        return `0${this.time}`;
      }
      return this.time;
    },
    isIE() {
      return this.$uaparser.getBrowser().name === `IE`;
    }
  }
};
</script>

<style lang="scss">
.hm-countdown-element {
  position: relative;
  &:not(:last-child) {
    margin-right: 14px;
  }
  &__delimiter {
    top: -1px;
    margin-left: 4px;
    position: absolute;
    animation: animSeparation 600ms linear infinite alternate;
  }
  .text-time-white {
    color: #ffffff !important;
  }
  .text-time-black {
    color: #1e1e1e !important;
  }
}

@keyframes animSeparation{
  0% {
    opacity: 1;
  }
  100% {
    opacity: .7;
  }
}
</style>
