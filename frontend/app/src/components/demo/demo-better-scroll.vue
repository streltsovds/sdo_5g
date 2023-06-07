<template>
  <div class="demo-better-scroll">
    <v-card>
      <h2>Flexbox horizontal</h2>

      <div class="demo-better-scroll-flex__wrapper" ref="demoFlexWrapper" style="overflow-x: hidden; width: 100%">
        <div class="demo-better-scroll-flex__content" style="display: inline-flex; min-width: 100%">
          <span class="demo-better-scroll-flex__item"
                ref="demoFlexItems"
                v-for="i in 5"
                @click="onFlexItemClick(i)"
                :key="i"
                style="min-width: 200px; flex-grow: 1; flex-shrink: 0;"
          >
            TEST TEXT {{ i }} |
          </span>
        </div>
      </div>
    </v-card>
  </div>
</template>
<script>
import BetterScroll from "better-scroll";

export default {
  name: "DemoBetterScrollFlex",
  data() {
    return {
      betterScrollFlex: null,
    };
  },
  mounted() {
    let scrollFlexEl = this.$refs.demoFlexWrapper;

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
    this.betterScrollFlex = new BetterScroll(scrollFlexEl, {
      bounceTime: 300,

      /** Чтобы работал обработчик клика на мобильных телефонах */
      click: true,

      scrollX: true,
      scrollY: false,
      disableMouse: false,
      disableTouch: false,
    })
  },
  methods: {
    onFlexItemClick(i) {
      this.$nextTick(() => {
        let scrollTarget = this.$refs.demoFlexItems[i-1];
        this.betterScrollFlex.scrollToElement(scrollTarget)
      });
    },
  }
}
</script>
<style lang="scss">

.demo-better-scroll {
  > .v-card {
    padding: 16px;
    padding-bottom: 36px;
    h2 {
      margin-bottom: 24px;
    }
  }
}

.demo-better-scroll-flex {
  &__content {
    cursor: move;
  }

  &__item {
    border: 1px solid black;
    height: 120px;
    line-height: 120px;
    text-align: center;

    &:hover {
      background: #aaa;
    }
  }
}
</style>
