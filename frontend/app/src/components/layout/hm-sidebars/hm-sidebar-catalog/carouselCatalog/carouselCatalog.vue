<template>
  <div id="carouselCatalog">
    <div class="carousel-catalog">

      <div class="carousel-catalog__buttons">
        <div class="carousel-catalog-back"
            @click="flipThrough"
            data-actions="back"
        >
          <svg-icon
            name="Arrow"
            style="width: 6px; height: 10px"
            color="#FFFFFF"
          />
        </div>
                <div class="carousel-catalog-next"
            @click="flipThrough"
            data-actions="next"
        >
          <svg-icon
            name="Arrow"
            style="width: 6px; height: 10px"
            color="#FFFFFF"
          />
        </div>
      </div>

      <div class="carousel-catalog__slider"
           ref="allSlider"
           :style="{ width: allWidth, transform: `translateX(-${offsetLeft}%)` }"
      >
        <div class="carousel-catalog__slider-block"
             v-for="(item, i) in dataCarousel"
             :key="i"
        >
          <div class="carousel-catalog__slider-block-icon">
            <div class="carousel-catalog__slider-block-icon__image">
              <a :href="item.viewUrl">
                <div class="carousel-catalog__slider-block-icon__image-img"
                     v-if="item.image && item.image !== ''"
                     :style="{ backgroundImage: `url(${item.image})` }"
                />

                <div class="carousel-catalog__slider-block-icon__image-icon"
                     v-else
                     :style="{
                       backgroundImage: `url('/images/icons/academic-hat.svg')`,
                       backgroundRepeat: 'no-repeat',
                       backgroundPosition: 'center',
                     }"
                />
              </a>
            </div>
          </div>
          <div class="carousel-catalog__slider-block-info">
            {{ /* eslint-disable-line */ }}
            <div class="carousel-catalog__slider-block-info__title"
                 :class="item.description ? 'text-ellipsis' : ''"
            >
              <a v-if="item.description" :href="item.viewUrl">
                <span>{{ item.name.length > 34 ? item.name.substr(0, 31)+ '...' : item.name }}</span>
              </a>
              <a v-else :href="item.viewUrl">
                <span>{{ item.name }}</span>
              </a>
            </div>
            <div class="carousel-catalog__slider-block-info__description">
              {{ /* TODO нужно ли здесь вырезание тэгов? Может быть просто задавать через атрибут v-html / компонент hm-dependency ? */ }}
              <span>{{ stripTags(item.description) }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="carousel-catalog__slider-navigation">
        <div
          v-for="item in navigationLength"
          :key="item"
          :style="{
            border: item === activeNavigation ? '1.5px solid #4A90E2' : '',
            background: item === activeNavigation ? '#FAF3D8' : '#D4E3FB',
          }"
          @click="selectNav(item)"
        ></div>
      </div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "CarouselCatalog",
  components: { SvgIcon },
  props: {
    dataCarousel: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      activeNavigation: 1,
      navigationLength: 7,
      allWidth: null,
    };
  },
  computed: {
    offsetLeft() {
      return (
        this.activeNavigation * (100 / this.navigationLength) -
        100 / this.navigationLength
      );
    },
  },
  mounted() {
    // console.log(this.dataCarousel)
    this.navigationLength = this.dataCarousel.length;
    this.allWidth = this.dataCarousel.length * 100 + "%"; // вся длина
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
    stripTags(s) {
      return s.replace(/<\/?[^>]+>/g, "");
    },
  },
};
</script>

<style lang="scss">
#carouselCatalog {
  width: 100%;
  .carousel-catalog {
    width: 100%;
    height: 350px;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;

    &__buttons {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      margin-bottom: 5px;
      position: absolute;
      top: 63px;
      z-index: 100;
      padding: 0 8px;
    }

    &-next,
    &-back {
      width: 24px;
      height: 24px;
      background: #FFFFFF;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      cursor: pointer;
      & * {
        fill: #000000;
      }
      // z-index: 100;
      // top: 45px;
      &:hover {
        background: #2574CF;
        & * {
          fill: #ffffff;
        }
      }
      &:active {
        background: #3e4e6c;
      }
    }
    &-next {
      // position: absolute;
      // right: 0;
      > svg {
        margin-left: 3px;
      }
    }
    &-back {
      // position: absolute;
      // left: 0;
      transform: rotate(180deg);
      > svg {
        margin-left: 3px;
      }
    }

    &__slider {
      transition: 0.3s ease-in-out;
      display: flex;
      overflow: hidden;
      &-block {
        width: 100%;
        &-icon {
          width: 100%;
          height: 150px;
          position: relative;
          display: flex;
          justify-content: center;
          align-items: center;
          border-radius: 2px;
          & * {
            border-radius: 2px;
          }

          &__image {
            width: 100%;
            height: 100%;
            &-img {
              background-repeat: no-repeat;
              background-size: cover;
              width: 100%;
              height: 100%;
            }
            &-icon {
              background: rgba(74, 144, 226, 0.5);
              width: 100%;
              height: 100%;
              display: flex;
              justify-content: center;
              align-items: center;
            }
          }
        }
        &-info {
          width: 100%;
          margin-top: 8px;
          &__title {
            width: 100%;
            margin-bottom: 8px;
            > a {
              text-decoration: none;
              cursor: pointer;
              > span {
                font-weight: 500;
                font-size: 14px;
                line-height: 21px;
                letter-spacing: 0.02em;
                color: #000000;
              }
            }
          }

          &__description {
            width: 100%;
            height: 90px;
            overflow: hidden;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            > span {
              font-size: 12px;
              line-height: 18px;
              letter-spacing: 0.15px;
              color: #3e4e6c;
            }
          }
        }
      }

      &-navigation {
        margin-top: 15px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        > div {
          width: 7px;
          height: 7px;
          border-radius: 50%;
          margin-right: 7px;
          cursor: pointer;
        }
      }
    }

    .text-ellipsis {
      height: 21px;
      overflow: hidden;
    }

  }
}
</style>
