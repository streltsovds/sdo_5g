<template>
  <swiper
    v-once
    :class="'hm-swiper ' + swiperBgColor"
    :style="SwiperStyles"
    :options="swiperOptions"
  >
    <swiper-content :items="swiperItems" />
    <!-- If we need pagination -->
    <div
      v-if="swiperOptions.pagination"
      slot="pagination"
      class="swiper-pagination"
    />
    <!-- If we need navigation buttons -->
    <v-btn
      v-if="swiperOptions.navigation"
      slot="button-prev"
      fab
      small
      absolute
      color="accent"
      class="hm-swiper__button-prev"
    >
      <v-icon>chevron_left</v-icon>
    </v-btn>
    <v-btn
      v-if="swiperOptions.navigation"
      slot="button-next"
      fab
      small
      absolute
      color="accent"
      class="hm-swiper__button-next"
    >
      <v-icon>chevron_right</v-icon>
    </v-btn>
    <!-- If we need scrollbar -->
    <div
      slot="scrollbar"
      :style="horizontalScrollMode ? 'cursor: pointer;' : null"
      class="swiper-scrollbar"
    />
  </swiper>
</template>

<script>
import "swiper/dist/css/swiper.css";
import { swiper, swiperSlide } from "vue-awesome-swiper";

import SwiperContent from "./partials/content.js";

export default {
  components: {
    swiper,
    swiperSlide,// eslint-disable-line
    SwiperContent
  },
  props: {
    customConfig: {
      type: Object,
      default: () => ({ empty: true })
    },
    debug: {
      type: Boolean,
      default: () => false
    },
    horizontalScrollMode: {
      type: Boolean,
      default: () => false
    }
  },
  data() {
    return {
      defaultConfig: {
        pagination: {
          el: ".swiper-pagination",
          type: "custom",
          renderCustom: function(swiper, current, total) {
            let start = "<div class='layout justify-center'>";
            const end = "</div>";
            const bullet = (number, isActive) => `<li>
                              <i class="v-icon icon__transition material-icons ${
                                isActive
                                  ? "accent--text"
                                  : "secondary--text text--lighten-5"
                              }">trip_origin</i>
                            </li>`;
            let num = 1;
            while (num <= total) {
              const isActive = current === num;
              start += bullet(num, isActive);
              num++;
            }
            return (start += end);
          }
        },
        navigation: {
          nextEl: ".hm-swiper__button-next",
          prevEl: ".hm-swiper__button-prev",
          disabledClass: "v-btn--disabled",
          hideOnClick: true
        },
        scrollbar: {
          el: ".swiper-scrollbar",
          dragClass: "swiper-scrollbar-drag accent"
        },
        spaceBetween: 58,
        watchOverflow: true,
        slidesPerView: "auto",
        color: "white"
      }
    };
  },
  computed: {
    swiperItems() {
      return this.$slots.default;
    },
    isCustomConfigPresent() {
      return (
        this.customConfig &&
        (this.customConfig.empty === undefined ||
          this.customConfig.empty === null)
      );
    },
    SwiperStyles() {
      let styles = "padding-bottom: 40px;padding-top:40px;";
      if (this.swiperOptions.navigation && this.$vuetify.breakpoint.mdAndUp) {
        styles += "padding-left:54px;padding-right:54px;";
      }
      if (this.isCustomConfigPresent) return "";
      if (this.horizontalScrollMode)
        styles += "padding-left:16px;padding-right:16px;";

      return styles;
    },
    swiperOptions() {
      let options = this.defaultConfig;

      if (this.isCustomConfigPresent && !this.horizontalScrollMode)
        return this.customConfig;

      if (this.horizontalScrollMode) {
        options = {
          ...options,
          scrollbar: {
            ...options.scrollbar,
            // разрешаем таскать за скроллбар
            draggable: true,
            // делаем скроллбар похожим на обычный
            dragSize: "200%"
          },
          spaceBetween: 16,
          // делаем так что слайды не центруются
          freeMode: true
        };

        // если мы с компа включаем скролл колесом мыши
        if (this.$vuetify.breakpoint.mdAndUp) {
          options["mousewheel"] = {
            sensitivity: 0.7
          };
        }
        // пагинация нам не нужна в этом режиме
        delete options.pagination;
        // навигация тоже
        delete options.navigation;
      }

      return options;
    },
    swiperBgColor() {
      return this.swiperOptions.color ? this.swiperOptions.color : "white";
    }
  },
  created() {},
  mounted() {},
  methods: {}
};
</script>
<style lang="scss">
.hm-swiper {
  &__button-next,
  &__button-prev {
    cursor: pointer !important;
    top: 45%;
    transform: translateY(-50%);
  }
  &__button-next {
    right: 6px;
  }
  &__button-prev {
    left: 6px;
  }
  & .swiper-scrollbar {
    height: 7px;
    z-index: 1;
    bottom: 0;
  }
  & .icon__transition {
    transition: color 1s cubic-bezier(0.4, 0, 1, 1);
  }
  & .swiper-slide {
    width: auto;
  }

  /**
   * Хак для странного бага в Firefox
   * когда враппер делает неинтерактивным
   * содержимое первых слайдов
   * потому что их перекрывает ¯\_(ツ)_/¯
   *
   * @TODO возможно стоит вынести это в
   * отдельный класс для добавления на лету.
   */
  & .swiper-wrapper {
    visibility: hidden;
    & .swiper-slide {
      visibility: visible;
    }
  }
}
</style>
