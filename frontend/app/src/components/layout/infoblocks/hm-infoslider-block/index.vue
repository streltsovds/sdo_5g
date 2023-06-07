<template>
    <div class="hm-infoslider-block" v-if="slides.length > 0">
        <v-carousel dark>
          <template v-slot:prev="{ on, attrs }">
              <button v-ripple="{ class: `grey lighten-1` }" class="hm-infoslider-block__button-prev" v-bind="attrs" v-on="on">
                <v-icon class="hm-infoslider-block__button-icon">mdi-chevron-left</v-icon>
              </button>
            </template>
            <template v-slot:next="{ on, attrs }">
              <button v-ripple="{ class: `grey lighten-1` }" class="hm-infoslider-block__button-next" v-bind="attrs" v-on="on">
                <v-icon class="hm-infoslider-block__button-icon">mdi-chevron-right</v-icon>
              </button>
            </template>
            <v-carousel-item
                    v-for="(slide, key) in slides"
                    :key="key"
            >
                <v-parallax :src="slide.image">
                    <v-sheet
                      :color="slide.image ? 'rgba(0,0,0,0)' : slide.color"
                      height="100%"
                    >
                        <v-layout row wrap class="hm-infoslider-block__slide">
                            <div class="hm-infoslider-block__slide-description">
                                <p class="hm-infoslider-block__slide-description__title" v-html="slide.name"></p>
                                <p
                                  class="hm-infoslider-block__slide-description__text" v-html="slide.description"

                                >
                                </p>
                            </div>
                            <v-btn v-ripple="{ class: `grey lighten-1` }" :href="slide.url" class="hm-infoslider-block__slide__more-button"><span class="hm-infoslider-block__slide__more-button-span">Подробнее</span></v-btn>
                        </v-layout>
                    </v-sheet>
                </v-parallax>
            </v-carousel-item>
        </v-carousel>
    </div>
    <hm-empty v-else
    >
      {{ _('Нет данных для отображения') }}
    </hm-empty>
</template>

<script>
    import moment from "moment";
    import HmEmpty from "@/components/helpers/hm-empty";
    export default {
      components: {HmEmpty},
        props: {
            slides: {
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
            }
        },
        computed: {
        },
        mounted() {
          const carouselControls = document.querySelector('.v-carousel__controls');
          const wrapper = carouselControls.querySelector('.v-item-group');
          const scrollHorizontally = (e) => {
              e = window.event || e;
              var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
              wrapper.scrollLeft -= (delta*10); // Multiplied by 10
              e.preventDefault();
          };
          wrapper.addEventListener("mousewheel", scrollHorizontally, false);
        },
        methods: {
            reformatDate(date) {
                return moment(date).format('DD MMMM YYYY')
            },
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "mixins";
    .hm-infoslider-block {
       max-height: none !important;
       overflow: hidden;

       &__button-icon {
         width: 100%;
         position: relative;
         z-index: 100;
       }

       &__button-next,
       &__button-prev {
          width: 24px;
          height: 46px;
          border-radius: 6px 0 0 6px !important;

          &:hover {
            background-color: rgba(255, 255, 255, 0.1);
          }
       }
       &__button-next {
          border-radius: 6px 0 0 6px !important;
       }
       &__button-prev {
          border-radius: 0 6px 6px 0 !important;
       }

       & .v-parallax__image-container img {
          position: static !important;
          transform: translate(0, 0) !important;
          object-fit: cover !important;
          object-position: center !important;
          margin: 0 !important;
          width: 100% !important;
          height: 100% !important;
       }
       & .v-carousel__controls .v-item-group {
         max-width: 240px;
         display: flex;
         overflow: auto;
         &::-webkit-scrollbar {
           width: 0;
         }
       }

       & .v-window__prev {
          margin-left: 1px !important;
          left: 15%;
          border-radius: 0 6px 6px 0 !important;

          & .v-btn--icon.v-size--default {
            width: 24px;
            height: 46px;
            &::before {
              content: none;
            }
          }
       }
       & .v-window__next {
          margin-right: 1px !important;
          right: 15%;
          border-radius: 6px 0 0 6px !important;

          & .v-btn--icon.v-size--default {
            width: 24px;
            height: 46px;
            &::before {
              content: none;
            }
          }
       }


        .v-parallax {

            &__content {
                padding: 0 !important;
            }
        }

        .v-window__prev,
        .v-window__next {
            background: rgba(0, 0, 0, 0.3) !important;
            .mdi {
                &::before {
                    font-size: 31px !important;
                }
            }
        }

        .mdi-chevron-left,
        .mdi-chevron-right {
            font-size: 46px !important;
        }

        .v-carousel {
            height: 500px;
        }

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
            width: 70%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0 !important;
            padding: 26px 45px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            height: 372px;

            // @media (max-width: $grid-breakpoint-sm-end) {
            //     height: 100px;
            // }

            // @media (max-width: 1460px) {
            //     width: 70%;
            // }

            &__more-button {
                box-shadow: none !important;
                border-radius: 4px !important;
                right: 46px;
                bottom: 65px;
                position: absolute !important;
                background-color: rgba(0, 0, 0, 0) !important;
                border: 1px solid #FFFFFF !important;
                width: 150px;
                height: 36px !important;

                &-span {
                  position: relative;
                  z-index: 100;
                }

                &:hover {
                    position: absolute !important;
                }

                .v-btn__content {
                    font-style: normal;
                    font-weight: 400;
                    letter-spacing: 0.06em;
                    font-size: 16px;
                    color: #FFFFFF;
                    text-transform: none;
                }

                // @media (max-width: $grid-breakpoint-sm-end) {
                //     width: 100px;
                //     height: 23px !important;
                //     right: 3px;
                //     bottom: 4px;
                //     border-radius: 2px !important;

                //     .v-btn__content {
                //         font-size: 10px;
                //     }
                // }
            }

            &-description {
                font-style: normal;
                color: #FFFFFF;
                width: 100%;
                position: relative;

                &__title {
                    margin: 0 !important;
                    font-weight: 300;
                    font-size: 34px;
                    letter-spacing: 0.3px;

                    line-height: 1.1;
                }

                &__date {
                    margin: 0 !important;
                    font-weight: 300;
                    font-size: 18px;
                    letter-spacing: 0.2px;
                    color: #DADADA;
                    position: absolute;
                    top: 20px;
                    right: 0;
                }

                &__text {
                    max-height: 195px;
                    overflow: auto;
                    margin: 0 !important;
                    font-weight: 300;
                    font-size: 20px;
                    letter-spacing: 0.2px;
                    padding-top: 42px;
                }
            }
        }

        .v-carousel {
            box-shadow: none;
        }

        .v-carousel__controls {
            bottom: 100px;
            height: 30px;
            background: none;
            width: initial;
            left: 50% !important;
            transform: translateX(-50%);
            .v-btn {
                margin: 0 4px;
                width: 16px;
                height: 16px;
                .v-icon {
                    height: 16px;
                    width: 16px;
                    font-size: 12px !important;
                }
            }
            .v-ripple__container {
                display: none !important;
            }
            .v-carousel__controls__item.v-item--active {
                .v-icon {
                  display: none;
                }
                .v-btn__content {
                  width: 10px;
                  height: 10px;
                  border-radius: 50%;
                  background: #D4E3FB;
                  opacity: 1;
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  transform: translate(-50%, -50%);
                  z-index: 100;
                }

                &::before {
                  opacity: 1;
                  background: #1F8EFA;
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  width: 13px;
                  z-index: 90;
                  height: 13px;
                  transform: translate(-50%, -50%);
                }
            }
        }

        .v-window__prev, .v-window__next {
            background: rgba(0, 0, 0, 0.3);
            // border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
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

        &_text {
            max-height: calc(100% - 70px);
            overflow-y: auto;
        }

        &_actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
    }

    @media(max-width: 1280px) {
      .hm-infoslider-block {
        &__slide {
          width: 80%;
        }
        & .v-window__prev {
          left: 10%;
        }
        & .v-window__next {
          right: 10%;
        }
      }
    }

    @media(max-width: 1024px) {
      .hm-infoslider-block {
        &__slide {
          width: 90%;
          &-description__text {
            font-size: 16px;
            line-height: 24px;
          }
        }
        & .v-window__prev {
          left: 5%;
        }
        & .v-window__next {
          right: 5%;
        }
      }
    }

    @media(max-width: 768px) {
      .hm-infoslider-block {
        &__slide {
          width: calc(100% - 32px);
          height: calc(100% - 32px);
          padding: 16px 13px;
          &-description__text {
            font-size: 14px;
            line-height: 20px;
            padding-top: 16px;
          }
          &-description__title {
            font-style: normal;
            font-weight: normal;
            font-size: 18px;
            line-height: 22px;
            letter-spacing: 0.02em;
          }
          &__more-button {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100% !important;
            border: none !important;
            &-span {
              display: none;
            }
          }
        }
        & .v-window__prev {
          display: none;
        }
        & .v-window__next {
          display: none;
        }
        & .v-carousel__controls {
          bottom: 19px;
        }
      }
    }
</style>
