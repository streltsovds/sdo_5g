<template>
  <hm-empty class="hm-news-banner-block--empty"
            v-if="isEmpty(slides)"
  >
    {{ _('Нет данных для отображения') }}
  </hm-empty>
  <div class="hm-news-banner-block" v-else>
    <v-tabs class="hm-news-banner-block__tab--active"
            v-model="classifier"
            v-if="classifiers.length > 0"
            slider-color="#DA291C"
    >
      <v-tab
        v-for="tab in filteredClassifiers"
        :key="tab.id"
        @click="currentClassifierId = tab.id"
      >
        {{ tab.name }}
      </v-tab>
    </v-tabs>
    <v-carousel :show-arrows="!$vuetify.breakpoint.smAndDown" dark>
      <v-carousel-item
        v-for="(slide, key) in filteredSlides"
        :key="key"
        :src="slide.image"
      >
        <div class="hm-news-banner-block__slide">
          <div class="hm-news-banner-block__slide-description">
            <div class="hm-news-banner-block__slide-description__left">
              <div class="hm-news-banner-block__slide-description__left-title">
                <div class="hm-news-banner-block__slide-description__left-title-text">
                  <span> {{ lengthTitleText(slide.name,85 ) }} </span>
                </div>
                <div class="hm-news-banner-block__slide-description__left-title-date"
                     v-if="$vuetify.breakpoint.mdAndUp"
                >
                  <span> {{ reformatDate(slide.created) }}</span>
                </div>
              </div>
              <div class="hm-news-banner-block__slide-description__left-desc">
                <span>{{ lengthTitleText(slide.description, 75) }}</span>
              </div>
            </div>
            <div class="hm-news-banner-block__slide-description__right">

              <v-btn class="hm-news-banner-block__slide__more-button" @click="openDialog(slide)">
                Подробнее
              </v-btn>
            </div>
          </div>
        </div>
        <div class="hm-news-banner-block__slide-after"></div>
      </v-carousel-item>
    </v-carousel>
    <hm-item-dialog v-if="dialog.item"
                    :is-open="dialog.isOpen"
                    :item="dialog.item"
                    @close="closeDialog"
    />
  </div>
</template>

<script>
import HmItemDialog from "./dialog";
import moment from "moment";
import HmEmpty from "@/components/helpers/hm-empty";
import isEmpty from "lodash/isEmpty";

export default {
  name: 'HmNewsBannerBlock',
  components: { HmItemDialog, HmEmpty},
  props: {
    slides: {
      type: Array,
      default: () => []
    },
    classifiers: {
      type: Array,
      default: () => []
    },
    downloadableExtensions: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      currentClassifierId: undefined,
      classifier: null,
      dialog: {
        isOpen: false,
        item: null,
      },
    }
  },
  computed: {
    filteredClassifiers() {
      console.log(this.commonClassifier);

      let classifiersUsed = this.classifiers.filter(
        classifier => this.slides.filter(
          (slide) => (slide.classifier_id == classifier.id)
        ).length > 0
      );

      console.log(
        'HmNewsBannerBlock.filteredClassifiers():',
        'classifiersUsed:',
        classifiersUsed
      );

      return Array.prototype.concat(
        this.commonClassifier,
        classifiersUsed
      );
    },
    commonClassifier() {
      return this.classifiers.filter(
        classifier => !classifier.hasOwnProperty('id')
      );
    },
    filteredSlides() {
      if (this.activeClassifierId === undefined) return this.slides;

      return this.slides.filter((slide) => (slide.classifier_id == this.activeClassifierId));
    },
    activeClassifierId() {
      let activeClassifier = this.classifiers.filter(classifier => classifier.id === this.currentClassifierId)[0];
      return (
        activeClassifier === undefined ||
        activeClassifier.id === undefined
      ) ? undefined : activeClassifier.id;
    }
  },
  methods: {
    isEmpty,
    reformatDate(date) {
      return moment(date).format('DD MMMM YYYY')
    },
    openDialog(item) {
      this.dialog.item = item;
      this.dialog.isOpen = true;
    },
    closeDialog() {
      this.dialog = {
        isOpen: false,
        item: null
      }
    },
    lengthTitleText(str,numb) {
      return str.length > numb ? `${str.substr(0,numb)}...` : str
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "mixins";

    .hm-news-banner-block {
        max-height: 556px !important;
        & .v-carousel__controls .v-item-group {
          max-width: 240px;
          display: flex;
          overflow: auto;
          &::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
          }
        }
        & .v-carousel__controls .v-btn {
          margin: 0 4px !important;
          width: 16px !important;
          height: 16px !important;
        }
        & .v-carousel__controls .v-btn .v-icon {
          height: 16px !important;
          width: 16px !important;
          font-size: 12px !important;
        }
        & .v-carousel__controls .v-carousel__controls__item.v-item--active::before {
          opacity: 1;
          background: #1F8EFA;
          position: absolute;
          top: 50% !important;
          left: 50% !important;
          width: 13px !important;
          z-index: 90;
          height: 13px !important;
          transform: translate(-50%, -50%);
        }
        & .v-carousel__controls .v-carousel__controls__item.v-item--active .v-btn__content {
          width: 10px !important;
          height: 10px !important;
          border-radius: 50%;
          background: #D4E3FB;
          opacity: 1;
          position: absolute;
          top: 50% !important;
          left: 50% !important;
          transform: translate(-50%, -50%);
          z-index: 100;
        }
        & .v-carousel__controls .v-carousel__controls__item.v-item--active .v-icon {
          display: none;
          height: 16px !important;
          width: 16px !important;
          font-size: 12px !important;
        }
        .v-window {
          height: 407px !important;
          &__container {
            height: 100%;
            .v-window-item {
              height: 100%;
              > div {
                height: 100% !important;
              }
            }
          }
        }

        .v-window__prev,
        .v-window__next {
          margin: 0!important;
            /*padding: 4px;*/
        }

        .mdi-chevron-left,
        .mdi-chevron-right {
            font-size: 46px !important;
        }

        .v-carousel {
            height: 500px;

            @media (max-width: $grid-breakpoint-sm-end) {
                height: 200px;

                .v-carousel__item .v-image__image--cover {
                    /*background-size: contain;*/
                }
            }
        }

        /*padding-left: 12px;*/
        /*padding-right: 12px;*/

        .v-tabs {
            .v-tabs__wrapper {
                padding-bottom: 20px;
            }

            .v-tab {
                margin-right: 26px;
                text-transform: none;
                font-style: normal;
                font-weight: bold !important;
                font-size: 20px;
                letter-spacing: 0.3px;
                color: #000000;
                padding: 0;

                &--active {
                    color: #125BB5;
                }
            }
        }

        &__slide {
            position: absolute;
            bottom: -12px;
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            padding: 12px 2% 26px 2%;
            &-after {
              display: none;
            }
            &-description {
              display: flex;
              flex-wrap: nowrap;
              &__left {
                width: calc(100% - 160px);
                display: flex;
                flex-direction: column;
                padding-right: 10px;
                &::-webkit-scrollbar {
                  width: 4px;
                  height: 4px;
                }
                &::-webkit-scrollbar-thumb {
                  background-color: #706e6e;
                  border-radius: 4px;
                }
                &::-webkit-scrollbar-thumb:hover {
                  background: #70889E;
                }
                &::-webkit-scrollbar-track {
                  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
                  border-radius: 4px;
                }
                &-title {
                  // height: 68px;
                  display: flex;
                  flex-wrap: nowrap;
                  align-items: flex-start;
                  width: 100%;
                  /*margin-bottom: 16px;*/
                  &-date {
                    width: 150px;
                    display: flex;
                    margin-left: 5px;
                    justify-content: flex-end;
                    align-items: center;
                    > span {
                      font-weight: 500;
                      font-size: 1rem;
                      line-height: 2.4rem;

                      letter-spacing: 0.2px;
                      color: #E6E6E6;
                    }
                  }
                  &-text {
                    width: calc(100% - 155px);
                    margin-bottom: 10px;
                    > span {
                      font-weight: normal;
                      font-size: 1.7rem;
                      line-height: 2rem;
                      letter-spacing: 0.02em;
                      color: #FFFFFF;
                      overflow: hidden;
                      -webkit-line-clamp: 2;
                      display: -webkit-box;
                      -webkit-box-orient: vertical;
                    }
                  }
                }
                &-desc {
                  width: 100%;
                  > span {
                    font-weight: normal;
                    font-size: 1.2rem;
                    line-height: 24px;
                    letter-spacing: 0.02em;
                    color: #FFFFFF;
                    display: inline-block;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    width: 100%;

                  }
                }
                @media only screen and (max-width: 1350px) {
                  &-title {
                    &-date {
                      > span {
                        font-size: 1rem;
                      }
                    }
                    &-text {
                      > span {
                        font-size: 1.5rem;
                      }
                    }
                  }
                  &-desc {
                    > span {
                      font-size: 1.1rem;
                    }
                  }
                }
                @media only screen and (max-width: 1175px) {
                  &-title {
                    &-date {
                      > span {
                        font-size: .8rem;
                      }
                    }
                    &-text {
                      > span {
                        font-size: 1.3rem;
                      }
                    }
                  }
                  &-desc {
                    > span {
                      font-size: 1rem;
                    }
                  }
                }
              }
              &__right {
                width: 140px;
                height: 104px;
                margin-left: 20px;
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
                > button {
                  background-color: transparent !important;
                  border-radius: 4px !important;
                  box-shadow: none !important;
                  border: 1.04836px solid #FFFFFF !important;
                  padding: 0 24px !important;
                  > span {
                    font-weight: 500;
                    font-size: 16px;
                    line-height: 24px;
                    letter-spacing: 0.02em;
                    color: #FFFFFF;
                    text-transform: capitalize;
                  }
                }
              }
              // @media only screen and (max-width: 1230px) {
              //   &__left {
              //     width: 82%;
              //   }
              //   &__right {
              //     width: 14%;
              //   }
              // }
            }
        }

        .v-carousel {
            box-shadow: none;
        }

        .v-carousel__controls {
            bottom: 130px;
            height: 25px;
            background: rgba(0, 0, 0, 0.5) !important;
            > div {
              margin-top: 0;
              > button {
                width: 10.5px !important;
                height: 10.5px !important;
                color:rgba(198, 206, 216, 0.5) !important;
                > span {
                  width: 10.5px !important;
                  height: 10.5px !important;
                  > i {
                    width: 10.5px !important;
                    height: 10.5px!important;
                    &:before {

                    }
                  }
                }
              }
            }


            @media (max-width: $grid-breakpoint-sm-end) {
                bottom: 130px;

                .v-btn {
                    margin: 0 !important;
                }
            }
            .v-ripple__container {
                display: none !important;
            }
            .v-carousel__controls__item--active {
                .v-icon {
                    color: rgba(255, 255, 255, 0.8);
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    width: 17px;
                    height: 16px;
                    margin-top: 1px;
                    border-radius: 50%;
                }
            }
        }

        .v-window__prev, .v-window__next {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            top: 76%;
            transform: translateY(-78%);
            > button {
              height: 40px !important;
              width: 40px !important;
              display: flex;
              justify-content: center;
              align-items: center;
            }
        }

        .v-window__prev {
            left: unset;
            right: calc(2% + 137px - 40px);

        }

        .v-window__next {
            right: 2%;
        }

        &_layout {
            height: auto;
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            bottom: 50px;
            margin: 0;
        }

        &_slide-card {
            height: 100%;
            color: transparent;

            &__has-image {
                color: inherit;
                background-color: rgba(0, 0, 0, 0.5);
                position: relative;
                padding-bottom: 52px;
            }
        }

        // &_text {
        //     max-height: calc(100% - 70px);
        //     overflow-y: auto;
        // }

        &_actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
    }
    // .hm-news-banner-block__slide-description__left-title-text {
    //   overflow: auto;
    //   height: 100%;
    // }
    // .hm-news-banner-block__slide-description__left-desc {
    //   overflow: auto;
    // }
    @media(max-width: 959px) {
      .hm-news-banner-block__slide-description__left-title-text {
        width: 100%;
      }
      .hm-news-banner-block {
        &__slide {
          &-description__left {
            padding: 0;
          }
        }
      }
    }
    @media(max-width: 768px) {
      .hm-news-banner-block__slide-description__right > button {
        border: none !important;
      }
      .hm-news-banner-block {
        margin: 16px;
        margin-top: 0;
        & .v-tabs-bar {
          height: 25px !important;
        }
        & .v-slide-group__wrapper .v-tab {
          min-width: auto !important;
          font-size: 16px !important;
          line-height: 18px !important;
          margin-right: 16px;
        }
        & .v-carousel {
          margin-top: 16px;
          height: 220px !important;
          border-radius: 4px;
        }
        &__slide {
          height: 75px;
          bottom: 0;
          padding: 12px;
          padding-bottom: 0;
          padding-top: 0;
          margin-bottom: 10px;
          overflow: auto;
          &-after {
            width: 100%;
            height: 10px;
            background: rgba(0, 0, 0, 0.5);
            position: absolute;
            bottom: 0;
            display: block;
          }
          &::-webkit-scrollbar {
            width: 0;
          }
          &-description {
            position: relative;
          }
          &-description__left {
            width: 100%;
            &-title {
              height: auto;
              width: 100%;
              font-size: 17px;
              line-height: 20px;
              &-text {
                height: auto;
                width: 100%;
                overflow: hidden;
                & span {
                  width: 100%;
                  font-size: 1.0625rem;
                  line-height: 1.1rem;
                }
              }
            }
            &-desc {
              & span {
                font-size: 0.875rem;
                line-height: 1rem;
              }
              &::-webkit-scrollbar {
                width: 0;
              }
            }
          }
          &-description__right {
            width: 0;
            margin: 0;
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
          }
          &__more-button {
            width: 100% !important;
            height: 100% !important;

            & span {
              display: none;
            }
          }
        }
        & .v-carousel__controls {
          bottom: 85px;
        }
      }
    }
</style>
