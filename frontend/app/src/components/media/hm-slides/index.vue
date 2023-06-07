<template>
  <div class="hm-slides">
    {{ /* eslint-disable vue/no-v-html */ }}

    <!-- Кнопка "Вперёд" -->
    <div class="hm-slides__next"
         @click="flipThrough"
         data-actions="next"
    >
      <svg-icon
        name="arrow"
        widht="21px"
        height="21px"
        color="#FFFFFF"
      />
    </div>

    <!-- Кнопка "Назад" -->
    <div class="hm-slides__back"
         @click="flipThrough"
         data-actions="back"
    >
      <svg-icon
        name="arrow"
        widht="21px"
        height="21px"
        color="#FFFFFF"
      />
    </div>

    <!-- Слайды -->
    <div class="hm-slides__slider"
         ref="allSlider"
         :style="{ width: allWidth, transform: `translateX(-${offsetLeft}%)` }"
    >
      <div class="hm-slides__slide"
           v-for="(slide, i) in slides"
           :key="i"
      >
        <div class="hm-slides__slide__content"
             v-html="slide.html"
        />
      </div>
    </div>

    <!-- Навигация -->
    <div class="hm-slides__navigation">
      <div
        v-for="item in navigationLength"
        :key="item"
        :style="{
          border: '2px solid ' + (item === activeNavigation ? '#4A90E2' : '#333'),
          background: item === activeNavigation ? '#FAF3D8' : '#D4E3FB',
        }"
        @click="selectNav(item)"
      ></div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";

/**
 * Сделан на основе компонента @/components/hm-sidebars/hm-sidebar-catalog/carouselCatalog
 */
export default {
  name: "HmSlides",
  components: { SvgIcon },
  props: {
    slides: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      activeNavigation: 1,
      // navigationLength: 7,
      // allWidth: null,
    };
  },
  computed: {
    offsetLeft() {
      return (
        this.activeNavigation * (100 / this.navigationLength) -
        100 / this.navigationLength
      );
    },
    navigationLength() {
      return this.slides.length;
    },
    allWidth() {
      return this.slides.length * 100 + "%"; // вся длина
    },
  },
  mounted() {
    // console.log(this.dataCarousel)
    // this.navigationLength = this.slides.length;
    // this.allWidth = this.slides.length * 100 + "%"; // вся длина
  },
  methods: {
    /**
     * метод пролистывания slider"а
     */
    flipThrough(e) {
      if (e.currentTarget.dataset.actions === "next") {
        this.activeNavigation < this.navigationLength
          ? this.activeNavigation++
          : (this.activeNavigation = 1);
      } else {
        this.activeNavigation !== 1
          ? this.activeNavigation--
          : (this.activeNavigation = this.navigationLength);
      }
    },
    // метод выбора рекомендуемого курса
    selectNav(count) {
      this.activeNavigation = count;
    },
    // stripTags(s) {
    //   return s.replace(/<\/?[^>]+>/g, "");
    // },
  },
};
</script>

<style lang="scss">
.hm-slides {
  width: 100%;
  min-height: 400px;
  position: relative;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  justify-content: space-between;

  &__next,
  &__back {
    width: 48px;
    height: 48px;
    background: #c4c4c4;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    cursor: pointer;
    z-index: 3;
    top: 45%;
  }
  &__next {
    position: absolute;
    right: 0;
    z-index: 3;
    > svg {
      margin-left: 3px;
    }
  }
  &__back {
    position: absolute;
    left: 0;
    transform: rotate(180deg);
    > svg {
      margin-left: 3px;
    }
  }

  &__slider {
    transition: 0.3s ease-in-out;
    display: flex;
    overflow: hidden;

    flex-grow: 1;
  }

  &__slide {
    width: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;

    &__content {
      flex-grow: 1;
      overflow-y: auto;

      /* место для кнопок переключения */
      margin-left: 72px;
      margin-right: 72px;
      padding: 26px;

      background-color: #fff;

      img {
        max-width: 100%;
      }
    }
  }

  &__navigation {
    margin-top: 16px;
    margin-bottom: 16px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;

    > div {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 10px;
      cursor: pointer;
    }
  }
}
</style>
