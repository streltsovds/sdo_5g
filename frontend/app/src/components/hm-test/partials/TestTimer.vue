<template>
  <div
    class="hm-test-timer"
    :class="{
      'hm-test-timer--breakpoint-sm-and-down': $vuetify.breakpoint.smAndDown
    }"
    ref="templateRoot"
  >
    <v-progress-linear
      class="hm-test-timer__progress ma-0"
      v-model="progress"
      :color="colorMovingBar"
      :height="36"
      :background-color="colorTimerBg"
    ></v-progress-linear>

    <v-fade-transition :duration="isIE ? 0 : 250" mode="out-in">
      <vue-countdown
        v-if="timer > 0 && progress !== 100"
        class="hm-test-timer__timer"
        :time="timer"
        @progress="onCountdownProgress"
        @end="onCountdownEnd"
      >
        <template
          slot-scope="props"
        >
          <!--
            Цвет текста меняется, когда до него "доходит" progress bar

            Реализовано через дублирование текста
              и наложения текста одного поверх другого.

            Текст, который поверх,
              имеет такую же ширину, как и текст снизу, но обрезается

            https://stackoverflow.com/a/21910775/1760643
          -->

          <!-- Текст под -->
          <div class="hm-test-timer__text-under"
               :style="{ color: colorMovingBar,}"
          >
            <countdown-element v-if="props.hours" :time="props.hours" />
            <countdown-element :time="props.minutes" />
            <countdown-element :time="props.seconds" no-delimiter />
          </div>


          <div class="hm-test-timer__text-over-cut"
               :style="{
                 overflow: 'hidden',
                 width: progress + '%'
               }"
          >

            <!-- Текст над -->
            <div class="hm-test-timer__text-over"
                 style="position: absolute; top: 0; left: 0"
                 :style="{
                   color: colorTimeText,
                   width: addPx(currentTemplateRootWidth),
                 }"
            >
              <countdown-element v-if="props.hours" :time="props.hours" />
              <countdown-element :time="props.minutes" />
              <countdown-element :time="props.seconds" no-delimiter />
            </div>
          </div>
        </template>
      </vue-countdown>
      <span
        v-else
        class="hm-test-timer__timeover"
        :style="{ 'font-size': $vuetify.breakpoint.xsOnly ? '16px' : null }"
      >
        Время вышло
      </span>
    </v-fade-transition>
  </div>
</template>

<script>
import VueCountdown from "@chenfengyuan/vue-countdown";
import CountdownElement from "./CountdownElement";
import Color from "color"
import addPx from "@/utilities/addPx";

export default {
  components: {
    VueCountdown,
    CountdownElement
  },
  props: {
    time: {
      type: Number,
      default: 60,
    },

    /**
     * Перестать менять значение progress для отладки
     * (изменять через DevTools)
     */
    debugStop: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currentTemplateRootWidth: 0,
      timeLeft: this.timer,
      progress: 0,
      color:"text-time-red"
    };
  },
  computed: {
    timer() {
      return this.time * 1000;
    },
    isIE() {
      return this.$uaparser.getBrowser().name === `IE`;
    },
    colorTimerBg() {
      if(this.progress === 100) {
          return 'none'
      }
      else if (this.progress >= 95) {
        return "error";
      }
      if (this.progress >= 70) {
        return "#FFC850";
      }
      // return `info`;
      return '#ffffff'
    },
    colorMovingBar() {
      return '#05C985';
    },
    colorTimeText() {
      return "#ffffff";
      // if(this.progress >= 56) return 'text-time-white'
      // return 'text-time-black'
    },
    // colorTimeTextShadow() {
    //   return Color(this.colorTimerBg.darken())
    // }
    cssTimeTextShadow() {
      return this.cssTextShadow(this.colorMovingBar, 5)
    }
  },
  watch: {
    timeLeft(val) {
      this.progress = ((this.timer / 1000 - val) / (this.timer / 1000)) * 100;
    }
  },
  mounted() {
    if (this.time <= 0) {
      this.progress = 100;
    }
  },
  methods: {
    addPx,
    onCountdownProgress({ totalSeconds }) {
      let templateRootEl = this.$refs.templateRoot;
      this.currentTemplateRootWidth = templateRootEl.clientWidth;

      if (this.debugStop) {
        return;
      }

      this.timeLeft = totalSeconds;
      this.$log.debug(`hm:test:timer | tick: ${totalSeconds}`);
      this.$nextTick(() => {
        this.$emit(`tick`, totalSeconds);
      });
    },
    onCountdownEnd() {
      if (this.debugStop) {
        return;
      }

      this.$nextTick(() => {
        this.progress = 100;
      });
      this.$nextTick(() => {
        this.$log.debug(`hm:test:timer | end`);
        this.$emit(`end`);
      });
    }
  }
};
</script>

<style lang="scss">
.hm-test-timer {
  position: relative;
  z-index: 1;

/*
  border-top-right-radius: 30px;
  border-bottom-right-radius: 30px;
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
*/
  border-radius: 4px 30px 30px 4px ;
  border: 1px solid transparent;
  width: 100%;
  overflow: hidden;
  &__timeover {
    font-weight: normal;
    font-size: 20px;
    line-height: 36px;
    position: absolute;
    color: white;
    top: 0;
    z-index: 3;
    left: 50%;
    transform: translateX(-50%);
  }
  &__timer {
    font-weight: bold;
    font-size: 26px;
    line-height: 26px;
    position: absolute;
    //top: 5px;
    z-index: 3;
    //left: 50%;
    //transform: translateX(-50%);
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
  }
  &__progress {
    .v-progress-linear__background {
      border-radius: 4px 0 0 4px;
    }
    .v-progress-linear__bar {
      &__determinate {
        border-radius: 4px 0 0 4px;
      }
    }
  }
}

.hm-test-timer--breakpoint-sm-and-down {

  .hm-test-timer__progress {
    height: 36px;
  }

  .hm-test-timer__timeover {
    line-height: 36px;
  }

  .hm-test-timer__timer {
    line-height: 22px;
  }
}

.hm-test-timer__text-under,
.hm-test-timer__text-over {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.hm-test-timer__text-over-cut {
  height: 100%;
  position: absolute;
  left: 0;
  top: 0;
}
</style>
