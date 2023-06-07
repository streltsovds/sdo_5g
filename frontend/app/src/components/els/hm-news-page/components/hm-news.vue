<template>
    <v-layout class="hm-news"
              wrap
              justify-space-between
              ma-0
    >
      <div class="hm-news__filters-wrapper">
        <v-flex class="hm-news__classifiers">
          <div class="hm-news__classifiers__container">
            <p v-for="(classifier, index) in shownClassifiers"
                @click="changeActiveClassifier(index)"
                :class="activeClassifier === index ? 'active' : ''"
            >
              {{ classifier }}
            </p>
          </div>
        </v-flex>
        <hmPartialsActions style="background: inherit; padding: 0;" :class="hmPartialsActionsClass" :actions="actions">

          <!--
                    Фильтры.
                    Переносятся наверх к табам с помощью position:absolute
                -->
          <div
            class="hm-news__filters"
          >
            <v-layout

              :justify-end="$vuetify.breakpoint.width > 1278"
              align-center
              ma-0
            >
              <v-flex
                class="popularity-sort"
                :style="{
                  'padding-right':
                    ($vuetify.breakpoint.xsOnly ? '0' : '30px') + ' important!'
                }"
                d-flex
                pa-0
                pr-5
              >
                <v-layout
                  justify-start
                  ma-0
                  align-center
                >
                  <p
                    class="ma-0 hm-news__filters__filter-title"
                    style="padding-right: 12px; white-space: nowrap;"
                  >
                    {{ $vuetify.breakpoint.sm || $vuetify.breakpoint.xs  ? 'По популярности' : 'Сортировать по популярности' }}
                  </p>
                  <!-- <v-switch
                    class="ma-0 pa-0 "
                    v-model="sortPopular"
                    hide-details
                    color="#297BDC"
                  /> -->
                  <div class="sort-checkbox">
                    <input id="sort-checkbox" type="checkbox" v-model="sortPopular">
                    <label class="sort-checkbox__button" for="sort-checkbox">
                      <svg v-if="!sortPopular" width="26" height="22" viewBox="0 0 26 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.016 21.7916C11.8293 21.6758 10.2436 20.6925 9.32133 20.0436C2.20707 15.0432 -1.1464 9.23147 0.350643 4.4986C1.20167 1.8078 3.57083 0 6.24564 0C7.94567 0 9.66133 0.657229 10.952 1.80372C11.9407 2.6823 12.6061 4.08006 12.7792 4.468C12.9692 4.0413 13.6299 2.6704 14.6057 1.80372C15.896 0.657569 17.611 0 19.3113 0C21.9865 0 24.3557 1.8078 25.2067 4.49894C26.7037 9.23181 23.3503 15.0432 16.236 20.0436C15.3142 20.6919 13.7302 21.6741 13.5411 21.7913C13.5332 21.7963 13.5277 21.7997 13.5248 21.8014C13.3188 21.9296 13.0536 22 12.7788 22C12.5048 22 12.2392 21.9296 12.0318 21.8014C12.029 21.7997 12.0237 21.7964 12.016 21.7916ZM6.24576 1.0819C4.04457 1.0819 2.09023 2.58608 1.38234 4.82467C0.0352398 9.08459 3.2357 14.4431 9.94433 19.1582C10.8217 19.7753 12.3803 20.7435 12.58 20.8676C12.5929 20.8756 12.6001 20.88 12.6011 20.8807C12.6236 20.8943 12.6865 20.9181 12.7793 20.9181C12.8721 20.9181 12.9343 20.895 12.9564 20.8807C12.9571 20.8803 12.9603 20.8783 12.966 20.8748C13.1052 20.7885 14.7165 19.7893 15.6139 19.1582C22.3225 14.4427 25.523 9.08493 24.1756 4.82501C23.4677 2.58608 21.513 1.0819 19.3118 1.0819C17.8729 1.0819 16.4197 1.63985 15.3249 2.6126C14.398 3.43515 13.7662 4.90849 13.7352 4.9808C13.7345 4.98226 13.7342 4.98315 13.734 4.98345C13.4695 5.72908 13.0217 5.84468 12.779 5.84468C12.5362 5.84468 12.0884 5.72942 11.8133 4.95727C11.8133 4.95727 11.813 4.95643 11.8123 4.95477C11.7826 4.88625 11.1525 3.42889 10.2337 2.6126C9.13818 1.63985 7.68432 1.0819 6.24576 1.0819Z" fill="#FF7474"/>
                      </svg>
                      <svg v-else width="26" height="22" viewBox="0 0 26 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.32133 20.0436C10.2815 20.7192 11.9608 21.7572 12.0318 21.8014C12.2392 21.9296 12.5048 22 12.7788 22C13.0536 22 13.3188 21.9296 13.5248 21.8014C13.5959 21.7572 15.2755 20.7192 16.236 20.0436C23.3503 15.0432 26.7037 9.23181 25.2067 4.49894C24.3557 1.8078 21.9865 0 19.3113 0C17.611 0 15.896 0.657569 14.6057 1.80372C13.6299 2.6704 12.9692 4.0413 12.7792 4.468C12.6061 4.08006 11.9407 2.6823 10.952 1.80372C9.66133 0.657229 7.94567 0 6.24564 0C3.57083 0 1.20167 1.8078 0.350643 4.4986C-1.1464 9.23147 2.20707 15.0432 9.32133 20.0436Z" fill="#FF7474"/>
                      </svg>
                    </label>
                  </div>
                </v-layout>
              </v-flex>
              <v-flex
                class="data-sort"
                d-flex
                pa-0
              >
                <v-layout
                  justify-end
                  ma-0
                >
                  <v-btn
                    style="border-color:  #097ABD !important;"
                    class="ma-0 data-sort__button"
                    @click="closeDialog"
                  >
                    <svg
                      width="24"
                      height="23"
                      viewBox="0 0 24 23"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                      style="margin-right: 6px;"
                    >
                      <path d="M5.77458 10.3917H6.64898C7.11778 10.3917 7.49805 10.0115 7.49805 9.54266V8.66826C7.49805 8.19946 7.11778 7.81946 6.64898 7.81946H5.77458C5.30578 7.81946 4.92578 8.19946 4.92578 8.66826V9.54266C4.92605 10.0115 5.30578 10.3917 5.77458 10.3917Z" fill="#2960A0" />
                      <path d="M11.5636 10.3917H12.4386C12.9074 10.3917 13.2874 10.0115 13.2874 9.54266V8.66826C13.2874 8.19946 12.9074 7.81946 12.4386 7.81946H11.5636C11.0948 7.81946 10.7148 8.19946 10.7148 8.66826V9.54266C10.7151 10.0115 11.0951 10.3917 11.5636 10.3917Z" fill="#2960A0" />
                      <path d="M17.3491 10.3917H18.2235C18.6923 10.3917 19.0723 10.0115 19.0723 9.54266V8.66826C19.0723 8.19946 18.6923 7.81946 18.2235 7.81946H17.3491C16.8803 7.81946 16.5 8.19946 16.5 8.66826V9.54266C16.5003 10.0115 16.8805 10.3917 17.3491 10.3917Z" fill="#2960A0" />
                      <path d="M5.77458 14.7651H6.64898C7.11778 14.7651 7.49805 14.3848 7.49805 13.916V13.0413C7.49805 12.5725 7.11778 12.1925 6.64898 12.1925H5.77458C5.30578 12.1925 4.92578 12.5725 4.92578 13.0413V13.916C4.92605 14.3848 5.30578 14.7651 5.77458 14.7651Z" fill="#2960A0" />
                      <path d="M11.5636 14.7651H12.4386C12.9074 14.7651 13.2874 14.3848 13.2874 13.916V13.0413C13.2874 12.5725 12.9074 12.1925 12.4386 12.1925H11.5636C11.0948 12.1925 10.7148 12.5725 10.7148 13.0413V13.916C10.7151 14.3848 11.0951 14.7651 11.5636 14.7651Z" fill="#2960A0" />
                      <path d="M17.3491 14.7651H18.2235C18.6923 14.7651 19.0723 14.3848 19.0723 13.916V13.0413C19.0723 12.5725 18.6923 12.1925 18.2235 12.1925H17.3491C16.8803 12.1925 16.5 12.5725 16.5 13.0413V13.916C16.5003 14.3848 16.8805 14.7651 17.3491 14.7651Z" fill="#2960A0" />
                      <path d="M5.77458 19.1379H6.64898C7.11778 19.1379 7.49805 18.7576 7.49805 18.2888V17.4139C7.49805 16.9451 7.11778 16.5653 6.64898 16.5653H5.77458C5.30578 16.5653 4.92578 16.9451 4.92578 17.4139V18.2888C4.92605 18.7576 5.30578 19.1379 5.77458 19.1379Z" fill="#2960A0" />
                      <path d="M11.5636 19.1379H12.4386C12.9074 19.1379 13.2874 18.7576 13.2874 18.2888V17.4139C13.2874 16.9451 12.9074 16.5653 12.4386 16.5653H11.5636C11.0948 16.5653 10.7148 16.9451 10.7148 17.4139V18.2888C10.7151 18.7576 11.0951 19.1379 11.5636 19.1379Z" fill="#2960A0" />
                      <path fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M21.0032 0H2.9968C1.34453 0 0 1.34427 0 2.9968V19.4592C0 21.1117 1.34453 22.456 2.9968 22.456H21.0029C22.6555 22.456 24 21.1117 24 19.4592V2.9968C24 1.34427 22.6555 0 21.0032 0ZM23.1498 19.4592C23.1498 20.6435 22.1866 21.6072 21.0018 21.6072H2.99566C1.81139 21.6072 0.847656 20.6438 0.847656 19.4592V5.34988H23.1498V19.4592ZM0.847656 4.5011H23.1495H23.1498V2.99656C23.1498 1.81176 22.1863 0.848297 21.0018 0.848297H2.99566C1.81112 0.848297 0.847656 1.81203 0.847656 2.99656V4.5011Z"
                            fill="#2960A0"
                      />
                    </svg>
                    {{ displayed_date }}
                  </v-btn>
                </v-layout>
              </v-flex>
            </v-layout>
          </div>

        </hmPartialsActions>
      </div>
      <hm-dialog
        size="small"
        semanticAccent="info"
        :status="isCalendarShown"
        @close="closeDialog"
        :buttonClose="true"
      >
        <template v-slot:content>
          <v-layout align-center justify-content-center>
            <v-flex class="text-center">
              <v-date-picker class="elevation-0"
                             :allowed-dates="allowDates"
                             v-show="isCalendarShown"
                             @input="selectDate"
                             :value="selected_date"
                             width="100%"
                             type="month"
                             no-title
                             locale="ru-ru"
                             first-day-of-week="1"
              />
            </v-flex>
          </v-layout>
        </template>
      </hm-dialog>
      <div v-if="mainNews.length || restNews.length" style="width: 100%;">
        <v-flex v-for="newsData in mainNews"
                :key="newsData.announce"
                xs12
                pa-0
        >
          <hm-news-item
            @likeAction="likeAction"
            :news="newsData"
            :downloadable-extensions="downloadableExtensions"
          />
        </v-flex>
        <v-flex v-if="$vuetify.breakpoint.width > 1490" xs12 style="padding: 0">
          <v-layout wrap>
            <v-flex :xs12="!ifLastNews(index)"
                    :lg6="!ifLastNews(index)"
                    v-for="(newsData, index) in restNews"
                    :style="restNewsCardStyle(index)"
                    :key="newsData.id"
            >
              <hm-news-item
                @likeAction="likeAction"
                :news="newsData"
                :downloadable-extensions="downloadableExtensions"
                :rest-news="true"
              />
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex v-else :style="$vuetify.breakpoint.width > 1278 ? '' : 'padding-left: 0;'" xs12>
          <v-layout wrap>
            <v-flex v-for="newsData in restNews" :key="newsData.id" xs12>
              <hm-news-item
                @likeAction="likeAction"
                :news="newsData"
                :downloadable-extensions="downloadableExtensions"
                :rest-news="true"
              />
            </v-flex>
          </v-layout>
        </v-flex>
      </div>
      <hm-empty v-else >Нет данных для отображения</hm-empty>
    </v-layout>

</template>

<script>
import hmPartialsActions from "@/components/layout/hm-partials-actions";
import hmNewsItem from "./hm-news-item";
import moment from "moment";
import HmDialog from "@/components/controls/hm-dialog/HmDialog.vue"
import HmEmpty from "../../../helpers/hm-empty/index";

moment.locale("ru");
export default {
  components: {
    hmNewsItem,
    hmPartialsActions,
    HmDialog,
    HmEmpty
  },
  props: {
    data: Array,
    actions: Array,
    downloadableExtensions: Array,
    withImage: {
      type: Boolean,
      default: false
    },
    classifiers: Array
  },
  data() {
    return {
      selected_date: null,
      isCalendarShown: false,
      sortPopular: false,
      activeClassifier: 0
    };
  },
  computed: {
    shownClassifiers() {
      return this.classifiers.filter((classifier) => {
        let data = this.data
          .filter(x => {
            return (
              this.selected_date ===
              `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`
            );
          })
          .filter(news => {
            if (news.classifiers.includes(classifier) || classifier === "Все новости") return news
          })
        if (data.length > 0) return classifier
      })
    },
    mainNews() {
      let displayedNews = this.displayedNews()
      return displayedNews.filter((news, index) => {
        if (index < 3) return news
      })
    },
    restNews() {
      let displayedNews = this.displayedNews()
      return displayedNews.filter((news, index) => {
        if (index >= 3) return news
      })
    },
    hmPartialsActionsClass(){
      let classes = ['hm-news__filters-container'];
      if(!this.actions.length) classes.push('justify-end');
      return classes.join(' ');
    },
    displayed_date() {
      if (this.$vuetify.breakpoint.xsOnly) {
        let month = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(0, 1).splice(0, 1)[0]
        let year = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(2, 1).splice(0, 1)[0]
        return `${month[0].toUpperCase()}${month.slice(1, month.length)} ${year}`
      } else {
        let month = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(0, 1).splice(0, 1)[0]
        let year = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(2, 1).splice(0, 1)[0]
        return `${month[0].toUpperCase()}${month.slice(1, month.length)} ${year}`
      }
    },
    restrictHeight() {
      return this.displayedNews.length > 8;
    },
    newsMonths() {
      return this.data.map(x => x.date.month() + 1).sort((a, b) => b - a);
    },
    newsYears() {
      return this.data.map(x => x.date.year()).sort((a, b) => b - a);
    },
    allowedDatesList() {
      return [...new Set(this.data.map(x => {
        return `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`;
      }))]
    }
  },
  methods: {
    closeDialog() {
      console.log(this.isCalendarShown)
      this.isCalendarShown = !this.isCalendarShown
    },
    logout() {
      window.hm
        ? window.hm.core.Console.log(...arguments)
        : console.log(...arguments);
    },
    restNewsCardStyle(index) {
      if (index % 2 === 0 && index !== this.restNews.length - 1) {
        return 'padding-right: 20px;'
      } else if (index !== this.restNews.length - 1) {
        return 'padding-left: 20px;'
      } else if (index === this.restNews.length - 1 && this.restNews.length % 2 === 0) return 'padding-left: 20px;'
    },
    changeActiveClassifier(index) {
      this.activeClassifier = index
    },
    displayedNews() {
      if (this.selected_date === null || this.selected_date === undefined) {
        this.selected_date = [...this.allowedDatesList].pop();
      }
      return this.data
        .filter(x => {
          return (
            this.selected_date ===
            `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`
          );
        })
        .filter(news => {
          if (this.activeClassifier === 0) return news
          else if (news.classifiers.includes(this.shownClassifiers[this.activeClassifier])) return news
        })
        .sort(this.sortToDate)
        .sort((a, b) => {
          if (this.sortPopular) {
            return this.sortToLikes(a, b);
          } else {
            return 0;
          }
        });

    },
    ifLastNews(index) {
      if (this.restNews.length % 2 === 0) return false
      else return index === this.restNews.length - 1
    },
    sortToLikes(likeOne, likeTwo) {
      // с бОльшим количеством лайков первее
      const likeOneCount =
        likeOne.like_count === null ? 0 : parseInt(likeOne.like_count, 10);
      const likeTwoCount =
        likeTwo.like_count === null ? 0 : parseInt(likeTwo.like_count, 10);
      return likeTwoCount - likeOneCount;
    },
    sortToDate(newsA, newsB) {
      // сортирует так что новая новость первее
      if (newsA.date.isSame(newsB.date, "second")) {
        return 0;
      } else if (newsA.date.isAfter(newsB.date, "second")) {
        return -1;
      } else if (newsA.date.isBefore(newsB.date, "second")) {
        return 1;
      } else {
        return 0;
      }
    },
    addLeadingZero(val) {
      if (parseInt(val) < 10) {
        return `0${val}`;
      } else {
        return val;
      }
    },
    selectDate(val) {
      this.selected_date = val;

      this.isCalendarShown = false;
    },
    allowDates(val) {
      if (this.allowedDatesList.includes(val)) return val;
    },
    likeAction(event) {
      this.$emit("like", {...event, item_type: "ITEM_TYPE_NEWS"});
    }
  }
};
</script>

<style lang="scss">
    @import "mixins.scss";
    @import "colors.scss";

    .sort-checkbox {
      width: 26px;
      height: 22px;

      input {
        display: none;
      }

      &__button {
        cursor: pointer;
        &:hover {
          opacity: 0.7;
        }
      }
    }

    .hm-news {
        position: relative;
        /*padding-left: 4px;*/
        &__filters-wrapper {
          display: flex;
          flex-direction: column;
          width: 100%;
          padding-bottom: 26px;
        }

        &__classifiers {
          width: 100% !important;
          &__container {
            display: flex;
            overflow-x: auto;
            padding-bottom: 6px;
            margin-bottom: 6px;
            &::-webkit-scrollbar {
              display: none;
            }
            // &::-webkit-scrollbar-thumb:hover {
            //   background: #70889E;
            // }
            // &::-webkit-scrollbar-track {
            //   -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            //   border-radius: 4px;
            // }
            // &::-webkit-scrollbar-thumb {
            //   background-color: #C4C4C4;
            //   border-radius: 4px;
            // }
            p {
              cursor: pointer;
              white-space: nowrap;
              font-family: Roboto, sans-serif;
              font-style: normal;
              font-weight: bold;
              font-size: 30px;
              line-height: 36px;
              letter-spacing: 0.02em;
              color: #9D9D9D;
              margin-right: 36px;
              margin-bottom: 0 !important;

              &:hover {
                color: #1E1E1E;
              }
            }

            .active {
              color: #1E1E1E;
              border-bottom: 3px solid #DA291C;
            }
          }
        }

        &__filters {
          padding: 0 !important;
        }

        &__filters {
            &-container{
                width: 100%;
                justify-content: space-between;
                padding: 0 12px 12px 12px;
                align-items: center;
            }
            .data-sort {

              &__button {
                background: #D4E3FB !important;
                border: 1px solid;
                border-color:  #097ABD !important;
                box-sizing: border-box;
                box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.14) !important;
                border-radius: 4px;
                color: #2960A0;
                &::before {
                  display: none !important;
                }
              }
                @include newsText(15px, bold, $white);
                /*margin-left: -50px;*/
               @media (max-width: 960px) {
                   /*margin-left: 0;*/
                }
                @media (max-width: 600px) {
                    .v-btn__content {
                        margin-top: -3px;
                    }
                }

                img {
                    padding-left: 20px;
                }
                .v-btn__content {
                    text-transform: none;
                }
            }

            .popularity-sort {
                p {
                    @include newsText(16px, normal, $light-gray);
                    @media (max-width: 600px) {
                        font-size: 13px;
                    }
                }
            }
        }
    }

    @media(max-width: 1280px) {
      .hm-news {
        &__filters-wrapper {
          padding-bottom: 20px;
        }
        &__classifiers {
          &__container {
            p {
              font-size: 26px;
              line-height: 30px;
              margin-right: 26px;
            }
          }
        }
      }
    }

    @media(max-width: 1024px) {
      .hm-news {
        &__filters-wrapper {
          padding-bottom: 16px;
        }
        &__classifiers {
          &__container {
            p {
              font-size: 22px;
              line-height: 26px;
              margin-right: 20px;
            }
          }
        }
        &__filters {
          &__filter-title {
            font-size: 14px !important;
          }
          .data-sort {
            &__button {
              height: 40px !important;
              padding: 0 10px !important;
              font-size: 12px !important;
            }
          }
        }
      }
    }

    @media(max-width: 768px) {
      .hm-news {
        & .hm-partials-actions a  {
          padding: 0 !important;
        }
        &__filters-container {
          flex-wrap: wrap;
        }
        &__classifiers {
          &__container {
            p {
              font-size: 16px;
              line-height: 18px;
              margin-right: 16px;
            }
          }
        }
        &__filters-wrapper {
          flex-direction: column;
          align-items: flex-start;
          padding-bottom: 10px;
        }
        &__filters {
          width: 100%;
        }
      }
    }

    .restrictHeight {
        overflow-y: scroll;
        height: 800px;

        &:before {
            content: "";
            width: 100%;
            height: 20px;
            z-index: 1;
            @include scrimGradient(
                    $startColor: black,
                    $direction: to bottom,
                    $ease: ease-in-out,
                    $offsetStart: 0,
                    $offsetEnd: 100
            );
            position: absolute;
            top: 0;
            left: 3px;
            opacity: 0.2;
        }
    }

    @-webkit-keyframes fadeInCalendar {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fadeInCalendar {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeOutCalendar {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    @keyframes fadeOutCalendar {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    .fadeCalendar-enter-active,
    .fadeCalendarIn {
        -webkit-animation-duration: 0.5s;
        animation-duration: 0.5s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeCalendar-leave-active,
    .fadeCalendarOut {
        -webkit-animation-duration: 0.3s;
        animation-duration: 0.3s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeCalendar-enter-active,
    .fadeCalendarIn {
        -webkit-animation-name: "fadeInCalendar";
        animation-name: "fadeInCalendar";
    }

    .fadeCalendar-leave-active,
    .fadeCalendarOut {
        -webkit-animation-name: "fadeOutCalendar";
        animation-name: "fadeOutCalendar";
    }

    .news-item:not(:last-of-type) {
        margin-bottom: 1rem;
    }

    .news-item {
        z-index: 0;

        &.elevation-2 {
            .v-card {
                box-shadow: none !important;
            }
        }

        .news-item__image {
            height: 1px;
            min-height: 100%;
        }
    }

    .v-dialog {
        box-shadow: none !important;
    }
</style>
