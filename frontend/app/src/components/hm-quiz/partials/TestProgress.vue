<template>
  <div class="hm-test-progress" style="width: 100%; padding: 0 !important;">

    <v-stepper
      class="hm-test-progress__stepper elevation-0"
      ref="progressToolbar"
      :value="currentId"
      style="overflow: initial"
    >
      <v-stepper-header
        class="hm-test-progress__header nowrap"
        ref="progressToolbarHeader"
      >
        <div class="hm-test-progress__header-content">
<!--          class="layout row ma-0 pa-0 block-test"-->
          <div
            class="block-test"
            v-for="(item, i) in progress"
            :class="{'block-test-last': i === progress.length - 1}"
            :key="i"
            :ref="item.current ? `scrollTarget` : null"
            :style="{
              minWidth:
              // flexBasis:
                item.name && parseInt(item.name, 10) !== i + 1 ? `200px` : `100px`,
              // width: 100 / progress.length +'%',
            }"
          >
            <div
              class="hm-test-progress__task-block__wrapper"
              :step="i + 1"
              @click="onProgressItemClick(item, i)"
              :style="{
                // cursor: isMovementRestricted ? `default` : `pointer`,
              }"
            >
              <div
                class="hm-test-progress__task-block"
                :class="{
                      firstBlock: i === 0,
                      lastBlock: i === progress.length-1,
                      activeTest: item.current,
                      standartTest: !item.current
                }"
                :width="7"
                :size="38"
                :data-debug-progress="item.itemProgress"
                :data-debug-items-count="progress.length"
                :data-debug-item-num="i + 1"
              >
                <div class="hm-test-progress__task-block__progress"
                     :style="{
                       background: colorProgress,
                       width: item.itemProgress+'%'
                     }"
                />
                <span
                  class="hm-test-progress__task-block__title"
                  v-if="typeof item.name === 'string'"
                  :style="{color: colorBlockTitle}"
                >
                  {{ item.name.length > 25 ? item.name.slice(0,30)+'..' : item.name  }}
                </span>
                <span
                  class="hm-test-progress__task-block__title hm-test-progress__task-block__title--default"
                  v-else
                  :style="{color: colorBlockTitle}"
                >
                  {{ _('Без названия') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </v-stepper-header>
    </v-stepper>
  </div>
</template>

<script>
import BetterScroll from 'better-scroll'
import reduce from 'lodash/reduce'
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

export default {
  mixins: [VueMixinConfigColors],
  props: ["progress", "isMovementRestricted"], // eslint-disable-line
  data() {
    return {
      betterScroll: null,
      // rating: this.progress.findIndex(x => x.current) + 1,
      // widthProgress:{}
    };
  },
  computed: {
    colorBlockTitle() {
      return this.getColor(configColors.textLight, '#70889E');
    },
    colorProgress() {
      return this.themeColors.success || '#05C985';
    },
    // rating() {
    //   return this.progress.findIndex(step => step.current) + 1;
    // },
    currentId() {
      return reduce(this.progress, (result, step) => {
        return (step.current) ? step.itemId : result;
      }, 0)
    },
    // scrollTargetoffsetLeft() {
    //   return this.$refs.scrollTarget[0].$el.offsetLeft;
    // }
  },
  watch: {
    // progress(val) {
    //   this.rating = val.findIndex(x => x.current) + 1;
    // }
    currentId(newValue) {
      this.scrollToNewChosenItem()
    },
  },
  mounted() {
    //для стилей активного, если активный являеться последним элементом
    // if(this.progress.length === this.rating) document.getElementsByClassName('activeTest')[0].style.width = `100%`

    let scrollEl = this.$refs.progressToolbarHeader;

    this.$nextTick(() => {

      /**
       * BetterScroll нужно, чтобы у элемента "a", к которому он применяется,
       * был один дочерний элемент "b" и чтобы ширина "b" была больше ширины "a".
       * Внутри элемента "b" может быть сколько угодно дочерних элементов
       *
       * если Flexbox, то нужно обязательно делать стили
       * "a": { display: block }
       * "b": { display: inline-flex }
       *
       * https://better-scroll.github.io/docs/en-US/guide/base-scroll-options.html
       **/
      this.betterScroll = new BetterScroll(scrollEl, {
        bounceTime: 300,

        /** Чтобы работал переход к этапу на мобильных телефонах */
        click: true,

        scrollX: true,
        scrollY: false,
        disableMouse: false,
        disableTouch: false,
      })

      // debug
      window.hmTestProgressBetterScroll = this.betterScroll;

      this.scrollToNewChosenItem();
    });
  },
  updated() {
    //для стилей активного, если активный являеться последним элементом
    // if(this.progress.length === this.rating) document.getElementsByClassName('activeTest')[0].style.width = `100%`
  },
  methods: {
    scrollToNewChosenItem() {
      this.$nextTick().then(() => {

        let scrollTarget = this.$refs.scrollTarget[0];
        this.betterScroll.scrollToElement(scrollTarget)
      });
    },
    onProgressItemClick(item, index) {
      if (this.isMovementRestricted) {
        return false;
      }
      if (!item.current) {
        this.$log.debug(`hm:test:progress | clicked ${index}`);
        this.$nextTick(() => {
          this.$emit(`progress-click`, index);
        });
      }
    }
  }
};
</script>

<style lang="scss">
.hm-test-progress {
  .transparent {
    // border-radius: 4px 30px 30px 4px;
    border-radius: 0;
  }

  .hm-test-progress__header {
    flex-wrap: nowrap;
    //overflow-x: hidden;

    //display: flex;

    /** чтобы inline-flex мог иметь большую ширину, чтобы можно было использовать BetterScroll */
    display: block;

    //justify-content: center;
    //align-items: center;
    //height: 50px;
    height: auto;
    width: 100%;
    //border-radius: 4px 30px 30px 4px;
    //box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
    box-shadow: none;

    .layout {
      height: 100%;
      position: relative;
    }

    .hm-test-progress__header-content {
      display: inline-flex !important;
      min-width: 100%;

      // border-radius: 0 30px 30px 0;
      border-radius: 0;

      // box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);

      cursor: move;
    }

    .v-stepper__label {
      display: flex;
    }
    .v-stepper__step__step {
      margin-right: 14px;
    }
    .block-test {
      flex-shrink: 0;
      flex-grow: 1;
    }

    .block-test + .block-test {
      border-left: 1px solid #FFFFFF;
    }

    .block-test-last {
      // border-radius: 0 30px 30px 0;
      border-radius: 0;
    }

    .hm-test-progress__task-block {
      width: 100%;
      height: 36px;
      display: flex;
      justify-content: center;
      align-items: center;

      border-radius: 0;

      &__wrapper {
        width: 100%;
        display: flex;
      }
    }
  }
}

.activeTest {
  background: #4A90E2;
  border: 1px solid #4a90e2;
  z-index: 1000;
  > span {
    color: #ffffff !important;
  }
}
.standartTest {
  background: #D4E3FB;
  z-index: 50;
}

.firstBlock {
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
}
.lastBlock {
  border-top-right-radius: 30px;
  border-bottom-right-radius: 30px;
}


.hm-test-progress__stepper,
.hm-test-progress__stepper.v-stepper { /** Переопределяем theme--light.v-stepper */
  background-color: transparent !important;
}

.hm-test-progress__task-block {
  position: relative;

  &__progress {
    bottom: 0;
    height: 3px;
    left: 0;
    position: absolute;
    z-index: 100;
  }

  &__title {
    color: #70889E;
    font-size: 16px;
    font-weight: 400;
    z-index: 1000;
  }
}


</style>
