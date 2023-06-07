<template>
  <div class="hm-kbase">
    <hm-empty v-if="!dataCard && componentStartSearch || dataCard.length === 0 && componentStartSearch" :sub-label="'Скорректируйте параметры поиска или перейдите в режим ' + JSON.parse(notFoundText)" empty-type="full" />
    <div
      class="hm-kbase__nostart"
      v-else-if="!dataCard && !componentStartSearch || dataCard.length === 0 && !componentStartSearch"
      :style="{background: [themeColors.contentColor]}"
    >
      <div class="hm-kbase__nostart-info">
        <svg-icon name="info" />
        <span>Воспользуйтесь <a @click="focusSidebar" href="#">формой поиска</a> по базе или откройте её в режиме
          <span v-html="JSON.parse(notFoundText)" />
        </span>
      </div>
      <div class="hm-kbase__nostart-resource">
        <div class="hm-kbase__nostart-resource__total">
          <span>{{ _('Всего информационных ресурсов') }}</span>
          <div class="total">
            {{ startDataComp.resourcesCount.resourcesTotalCount }}
          </div>
        </div>
        <div class="hm-kbase__nostart-resource__lastmont">
          <span>{{ _('Новых ресурсов за последний месяц') }}</span>
          <div class="lastmont">
            {{ startDataComp.resourcesCount.resourcesForLastMonthCount }}
          </div>
        </div>
        <div class="hm-kbase__nostart-resource__relations">
          <span>{{ _('Связей между ресурсами') }}</span>
          <div class="resourcecount">
            {{ startDataComp.resourcesCount.resourcesRelationsCount }}
          </div>
        </div>
      </div>
      <div class="hm-kbase__nostart-static">
        <div class="hm-kbase__nostart-static__chart">
          <span class="hm-kbase__nostart-static__chart-title">Наполнение Базы знаний</span>
          <div class="hm-kbase__nostart-static__chart-settings">
            <v-select
              v-model="chartActive"
              :items="getChartArray"
              :label="_('Классификация')"
            />
          </div>
          <hm-chart :data="getDataChart"
                    :options="optionChart"
                    type="pie"
                    data-label="type"
                    data-value="value"
          />
        </div>
        <div class="hm-kbase__nostart-static__topraiting">
          <span>{{ _('Наибольший рейтинг') }}</span>
          <div class="hm-kbase__nostart-static__topraiting-container">
            <div v-for="(el, i) in startDataComp.topRatingResources" :key="i">
              <file-icon class="icon" :small="true" :type="TypeIcon(el.filetype)" />
              <div class="text">
                <a :href="el.url">
                  <span>{{ textTitle(el) }}</span>
                </a>
              </div>
              <div class="rating">
                <hm-rating :total="5" :count="el.rating ? Math.floor(el.rating) : 0" />
              </div>
            </div>
          </div>
          <div class="hm-kbase__nostart-static__topraiting-all">
            <div>{{ _('Количество голосов:') }}</div>
            <div>{{ startDataComp.resourcesAssessmentCount }}</div>
          </div>
        </div>
      </div>
      <div class="hm-kbase__nostart-lastadded">
        <span>{{ _('Последние добавления в базу знаний') }}</span>
        <div>
          <div v-for="(el, key) in startDataComp.lastResources"
               :key="key"
               :id="`k-base-card-${el.resource_id}`"
               class="hm-kbase__nostart-lastadded-card"
               :style="{marginRight: sidebarOpened ? '52px' : '35px'}"
          >
            <hm-kbase-card :data-card="el" />
          </div>
        </div>
      </div>
    </div>
    <div class="hm-kbase__container" v-else>
      <div v-for="(el, key) in dataCard"
           :key="key"
           :id="`k-base-card-${el.resource_id}`"
           :style="{marginRight: sidebarOpened ? '35px' : '35px'}"
      >
        <hm-kbase-card :data-card="el" />
      </div>

    </div>
    <div class="hm-kbase__load-btn">
      <hm-load-more-btn v-if="addCard || isLoading" @click="addNewCard" :in-progress="isLoading" />
    </div>

  </div>
</template>
<script>
import 'swiper/dist/css/swiper.css'
import { color } from './colorClassifiers'
import hmChart from '@/components/media/hm-chart/index'
import typeIcons from '@/components/icons/file-icon/types'
import { mapActions } from "vuex";
import FileIcon from "@/components/icons/file-icon/index";
import HmEmpty from "@/components/helpers/hm-empty/index";
import GlobalActions from "../../../store/modules/global/const/actions";
import { swiper, swiperSlide } from "vue-awesome-swiper";
import SvgIcon from "../../icons/svgIcon";
import hmRating from '@/components/media/hm-rating/index';
import hexDec from '@/utilities/hexDec';
import HmKbaseIcon from "@/components/els/kbase/icon/index";
import HmLoadMoreBtn from "@/components/helpers/hm-load-more-btn";
import HmKbaseCard from "@/components/els/kbase/card/kBaseCard";
import isEqual from "lodash/isEqual";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

export default {
  name: "HmKbase",
  mixins: [VueMixinConfigColors],
  components: {HmKbaseIcon, SvgIcon, HmEmpty, swiper, swiperSlide, FileIcon, hmRating, hmChart, HmLoadMoreBtn,HmKbaseCard },
  props: {
    notFoundText: {
      type: String,
      default: ''
    },
  },
  data() {
    return {
      chartActive: '',
      isLoading: false,
      optionChart: {
        label: {
          outer: {
            show: true,
            value: {
              content:"[[label]]",
              show:true
            }
          }
        }
      },
      url: "/kbase/search/index/",
      flagAdd: false,
      componentStartSearch: false,
      search: {
        isLoading: false,
        items: [],
        params: {
          ajax: true,
          page: 1,
          itemsPerPage: 12,
          pageCount: 1
        }
      },
      cancelToken: this.$axios.CancelToken,
      axiosSource: null,
      testColorClassifiers: 'rgba(132, 63, 160, 0.7);',
      ClassifiersOptions: {
        slidesPerView: 'auto',
        centeredSlides: false,
        spaceBetween: 12,
      },
      sliderOption: {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      startDataComp: {
        notFoundText: null,
        resourcesCount: null,
        classifiersStatistics: null,
        lastResources: null,
        topRatingResources: null,
        resourcesAssessmentCount: null
      }
    };
  },
  computed: {
    breakPointsIcon() {
      return this.$vuetify.breakpoint.width <= 516;
    },
    // подготовка данных для граффика
    getDataChart() {
      for(let el in this.startDataComp.classifiersStatistics) {
        if(this.startDataComp.classifiersStatistics[el].title === this.chartActive) {
          let dataChart = [];
          this.startDataComp.classifiersStatistics[el].items.forEach(elArr => {
            dataChart.push({
              value:elArr.count,
              type:elArr.classifier_name.length > 20 ? elArr.classifier_name.substr(0,17) + '...' : elArr.classifier_name
            })
          });
          return dataChart;
        }
      }
      return [];
    },
    sidebarOpened() {
      return this.$root.appComputedAppCssClasses['hm-sidebar-opened']
    },
    getChartArray() {
      let newArray = [];
      for(let i in this.startDataComp.classifiersStatistics) {
        if(this.startDataComp.classifiersStatistics[i].items.length > 0) newArray.push(this.startDataComp.classifiersStatistics[i].title)
      }
      return newArray;
    },
    searchKbase() {
      return this.$store.state.kbase;
    },
    dataCard() {
      return this.search.items;
    },
    searchFilters() {
      return this.$store.state.kbase.searchFilters;
    },
    currentSearchPage() {
      return this.search.params.page;
    },
    addCard() {
      return this.search.params.page < this.search.params.pageCount && (this.dataCard && this.dataCard.length > 0);
    }
  },
  watch: {
    dataCard(newVal, val){
      this.isLoading = false;
    },
    searchFilters() {
      if(!this.flagAdd) {
        this.makeSearch();
      }
    },
    currentSearchPage() {
      if(!this.flagAdd) {
        this.makeSearch();
      }
    }
  },
  methods: {
    focusSidebar() {
      this.$store.dispatch("sidebars/changeSidebarState", {
        name: "search",
        options: {
          opened: true
        }
      });
      document.getElementById('searchInput').focus();
    },
    initComp() {
      for(let el in this.startDataComp) {
        this.startDataComp[el] = JSON.parse(this.$root.view[el]);
      }
      this.chartActive = this.getChartArray[0];
    },
    ...mapActions("kbase", ["resetSearch"]),
    ...mapActions([
      GlobalActions.setLoadingOn,
      GlobalActions.setLoadingOff
    ]),
    paginate(page) {
      this.search.params.page = page;
    },
    makeSearch() {
      this.componentStartSearch = true;
      this.flagReset = false;
      if (this.axiosSource) this.axiosSource.cancel("Kbase request canceled");

      this.search.isLoading = true;
      this[GlobalActions.setLoadingOn](this.$options.name);
      this.axiosSource = this.cancelToken.source();

      this.search.params.page = 1;
      const params = { ...this.searchFilters, ...this.search.params };
      const cancelToken = this.axiosSource.token;

      this.$axios
        .get(this.url, { params, cancelToken })
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error(
              "Ошибка при загрузке недавно добавленных материалов"
            );
          return r.data;
        })
        .then(data => {
          this.search.items = [];
          if(params.classifiers.length > 0 || params.search_query) {
            this.search.items = data.items;
          }
          this.search.params.page = data.pagination.pageCurrent;
          this.search.params.pageCount = data.pagination.pageCount;
          this[GlobalActions.setLoadingOff](this.$options.name);
          this.flagReset = true;
        })
        .catch(e => {
          if (this.$axios.isCancel(e)) {
          } else {
            this[GlobalActions.setLoadingOff](this.$options.name);
          }
        })
        .finally(() => (this.search.isLoading = false));
    },
    resetSearchData() {
      this.resetSearch();
      this.search.params.page = 1;
      this.search.params.pageCount = 1;
    },
    addNewCard() {
      this.isLoading = true;
      this.search.isLoading = true;
      this.flagAdd = true;
      this.search.params.page++;
      const params = { ...this.searchFilters, ...this.search.params };
      const cancelToken = this.axiosSource.token;
      this.search.isLoading = true;
      this.$axios
        .get(this.url, { params, cancelToken})
        .then(r=> {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error(
              "Ошибка при загрузке недавно добавленных материалов"
            );
          return r.data;
        })
        .then(res=> {
          let newItems = this.search.items;
          this.search.items = [];
          this.search.items = this.search.items.concat(res.items, newItems);
          this.search.params.page = res.pagination.pageCurrent;
          this.search.params.pageCount = res.pagination.pageCount;
          this[GlobalActions.setLoadingOff](this.$options.name);
        })
        .catch(err=> console.log(err))
        .finally(() => {
          this.flagAdd = false;
        })
    },
    styleIcon(el) {
      return el.imageUrl && el.imageUrl !== ''  ? {backgroundImage: `url(${el.imageUrl})`} : '';
    },
    TypeIcon(el) {
      return el !== '' ? el : 'default';
    },
    textTitle(el) {
      return el.title.length > 70 ? el.title.slice(0,67) + '...' : el.title;
    },
    alphaCol(el) {
      if(typeIcons[el]) {
        return hexDec(typeIcons[el].color);
      }
    },
    styleClassifiers(color) {
      let textColor = '';
      switch (color) {
        case '#DAD3FD':
          textColor = 'rgba(132, 63, 160, 0.7)';
          break;
        case '#D4FAE4':
          textColor = '#99D9BD';
          break;
        case '#D4E3FB':
          textColor = '#00ACED';
          break;
      }
      return {
        background: hexDec(color, .3),
        border: `1px solid ${ hexDec(color, .8)}`,
        color:  textColor
      }
    }
  },
  mounted() {
    this.initComp();
  }
};
</script>
<style lang="scss">
  .hm-kbase {
    &__load-btn{
      margin-top: 30px;
    }
    &__nostart {
      width: 100%;
      display: flex;
      flex-direction: column;
      background: #FFFFFF;
      box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
      border-radius: 4px;
      padding: 26px 26px 34px 26px;
      &-static {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        min-height: 319px;
        margin-top: 52px;
        justify-content: space-between;
        > div {
          width: 45%;
          height: 100%;
        }
        &__chart {
          width: 100%;
          &-title {
            display: none;
          }
          &-settings {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            > span {
              margin-bottom: 25px;
              font-weight: 500;
              font-size: 20px;
              line-height: 24px;
              letter-spacing: 0.02em;
              color: #1E1E1E;
            }
            > div {
              /*max-width: 160px;*/
              max-width: 300px;
            }
          }
        }
        &__topraiting {
          display: flex;
          flex-direction: column;
          > span {
            font-weight: 500;
            font-size: 20px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
          &-container {
            > div {
              margin-top: 16px;
              display: flex;
              flex-wrap: nowrap;
              align-items: flex-start;
              justify-content: flex-start;
              min-height: 24px;
              height: auto;
              &:not(:last-child) {
                margin-bottom: 16px;
              }
              > .icon {
                width: 20px;
              }
              > .text {
                width: calc(100% - 144px);
                margin-left: 12px;
                padding-right: 16px;
                background-color: inherit !important;
              }
              > .rating {
                width: 120px;
              }
            }

          }
          &-all {
            margin-top: 26px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            > div:last-child {
              font-weight: 500;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #3E4E6C;
              width: 120px;
              display: flex;
              justify-content: flex-end;
              align-items: center;
            }
            > div:first-child {
              font-weight: 500;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #1E1E1E;
              width: calc(100% - 120px);
            }
          }
        }
      }
      &-info {
        width: 100%;
        min-height: 60px;
        background: #D1EEFB;
        border-radius: 4px;
        display: flex;
        align-items: center;
        padding: 16px;
        > svg {
          margin: 0 16px 0 18px;
        }
        > span {
          > i {
            font-style: none;
            text-decoration: none;
            text-transform: none;
          }
        }
      }
      &-resource {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        width: 100%;
        > div {
          > span {
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
          > div {
            margin-top: 12px;
            border: 2.04918px solid;
            border-radius: 13px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
          }
          .total {
            border-color: rgba(132, 63, 160, 0.2);
            background: rgba(218, 211, 253, 0.2);
            color: #843FA0;
          }
          .lastmont {
            border-color: rgba(5, 201, 133, 0.2);
            background: rgba(212, 250, 228, 0.2);
            color: #05C985;
          }
          .resourcecount {
            border-color: rgba(74, 144, 226, 0.2);
            background: rgba(212, 227, 251, 0.4);
            color: #4A90E2;
          }
        }
      }
      &-lastadded {
        display: flex;
        flex-direction: column;
        margin-top: 52px;
        > span {
          margin-bottom: 25px;
          font-weight: 500;
          font-size: 20px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
        > div{
          display: flex;
          flex-wrap: wrap;
          .kbase-card {
            margin-bottom: 36px;
            width: 330px;
            height: 140px;
            display: flex;
            background: #FFFFFF;
            box-shadow: 5px 5px 25px rgba(179, 179, 179, 0.25);
            border-radius: 4px;
            &-icon {
              width: 113px;
              height: 100%;
              display: flex;
              justify-content: center;
              align-items: center;
              border-radius: 4px 0 0 4px;
              &-photo {
                background-size: cover;
                width: 100%;
                height: 100%;
                background-repeat: no-repeat;
                background-position: center;
                border-radius: 4px 0 0 4px;
              }
            }
            &-data {
              width: calc(100% - 113px);
              height: 100%;
              display: flex;
              flex-direction: column;
              justify-content: flex-start;
              align-items: flex-start;
              padding: 10px 12px 12px 12px;
              box-sizing: border-box;
              &__title {
                width: 100%;
                height: 39px;
                overflow: hidden;
                display: flex;
                > span, > a {
                  text-decoration: none;
                  font-weight: 500;
                  /*:TODO где конфиги???*/
                  font-size: 14px;
                  line-height: 21px;
                  letter-spacing: 0.02em;
                  color: #4A4A4A;

                }
              }
              &__classifiers {
                width: 100%;
                height: 26px;
                display: flex;
                overflow: hidden;
                cursor: grab;
                > div {
                  .swiper-wrapper {
                    height: 26px;
                    > div {
                      width: auto !important;
                      border-radius: 4px;
                      box-sizing: border-box;
                      > span {
                        padding: 4px 12px;
                        font-weight: normal;
                        font-size: 12px;
                        line-height: 18px;
                        letter-spacing: 0.15px;
                        color: inherit;
                        white-space: nowrap;
                      }
                    }
                  }
                }
              }
              &__tags {
                margin-top: 10px;
                overflow: hidden;
                width: 100%;
                .swiper-wrapper {
                  height: 26px;
                  .swiper-slide {
                    width: unset;
                  }
                  > div {
                    background: rgba(230, 230, 230, 0.5);
                    border-radius: 30px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                    cursor: grab;
                    > span {
                      padding: 4px 12px;
                      font-weight: normal;
                      font-size: 12px;
                      line-height: 18px;
                      letter-spacing: 0.15px;
                      color: #3E4E6C;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    &__container {
      display: flex;
      flex-wrap: wrap;
      .kbase-card {
        margin-top: 36px;
        width: 330px;
        height: 140px;
        display: flex;
        background: #FFFFFF;
        box-shadow: 5px 5px 25px rgba(179, 179, 179, 0.25);
        border-radius: 4px;
        &-icon {
          width: 113px;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
          border-radius: 4px 0 0 4px;
          &-photo {
            background-size: cover;
            width: 100%;
            height: 100%;
            background-repeat: no-repeat;
            background-position: center;
          }
        }
        &-data {
          width: calc(100% - 113px);
          height: 100%;
          display: flex;
          flex-direction: column;
          justify-content: flex-start;
          align-items: flex-start;
          padding: 10px 12px 12px 12px;
          box-sizing: border-box;
          &__title {
            width: 100%;
            /*height: 39px;*/
            margin-bottom: 16px;
            overflow: hidden;
            display: flex;
            > span, > a{
              font-weight: 500;
              text-decoration: none;
              /*:TODO где конфиги???*/
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #4A4A4A;

            }
          }
          &__classifiers {
            width: 100%;
            height: 26px;
            display: flex;
            overflow: hidden;
            cursor: grab;
            > div {
              .swiper-wrapper {
                height: 26px;
                > div {
                  width: auto !important;
                  border-radius: 4px;
                  box-sizing: border-box;
                  display: flex;
                  align-items: center;
                  > span {
                    padding: 0 12px;
                    font-weight: normal;
                    font-size: 12px;
                    line-height: 18px;
                    letter-spacing: 0.15px;
                    color: inherit;
                    white-space: nowrap;
                  }
                }
              }
            }
          }
          &__tags {
            margin-top: 10px;
            overflow: hidden;
            width: 100%;
            .swiper-wrapper {
              height: 26px;
              > div {
                background: rgba(230, 230, 230, 0.5);
                border-radius: 30px;
                display: flex;
                justify-content: flex-start;
                align-items: center;
                cursor: grab;
                > span {
                  padding: 4px 12px;
                  font-weight: normal;
                  font-size: 12px;
                  line-height: 18px;
                  letter-spacing: 0.15px;
                  color: #3E4E6C;
                }
              }
            }
          }
        }
      }
    }
    &__cont__actions {
      width: 100%;
      display: flex;
      justify-content: center;
      .hm-kbase__add {
        border: 1px solid #1F8EFA;
        border-radius: 4px;
        height: 29px;
        width: 191px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        > svg {
          margin-right: 12px;
        }
        > span {
          color: #1E1E1E;
        }
        &:hover {
          background: #1f8efa0d;
        }
      }
    }
  }
  .hm-kbase_search-pagination {
    margin: auto;
  }
  .hm-kbase__nostart-resource {
    padding-top: 12px;
    > div {
      &:not(:last-child) {
        padding-right: 16px;
      }

      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex: 1 0 21%;

      padding-top: 24px;
    }
  }

  @media(max-width: 599px) {
    .hm-kbase__nostart-resource {
      > div {
        flex: 1 0 100%;
      }
    }
  }

  @media screen and (max-width: 881px) {
    .hm-kbase__nostart-resource {
      height: auto;
      justify-content: center;

      > div:not(:first-child) {
        padding-top: 24px;
      }
    }
    .hm-kbase__nostart-static {
      height: auto;
      flex-direction: column;
      justify-content: center;
      > div {
        width: 100%;
        &:last-child {
          margin-top: 24px;
        }
      }
    }
  }

  @media screen and (max-width: 740px) {
    .hm-kbase__nostart-static {
      &__chart {
        &-settings {
          flex-direction: column;
        }
      }
    }

  }

  @media screen and (max-width: 516px) {
    .hm-kbase {
      &__container {
        .kbase-card {
          height: 100px;
        }
      }
    }
    .hm-kbase__nostart-info {
      > span {
        font-size: 12px;
      }
      > svg {
        margin: 0 16px 0 0;
      }
    }
    .hm-kbase__nostart-resource {
      width: 100%;
      > div {
       padding-right: 0;
       width: 100%;
         > div {
           width: 100%;
           font-size: 22px;
         }
       > span {
         font-size: 12px;
         line-height: 14px;
       }
      }
    }

    .hm-kbase__nostart-static__topraiting-container {
      > div {
        .text {
          font-size: 12px;
          width: calc(100% - 124px);
          overflow: hidden;
          white-space: nowrap;
          text-overflow: ellipsis;
        }
        .rating {
          width: 100px;
        }
      }
    }
    .hm-kbase__nostart-static__topraiting-all {
      > div:last-child {
        width: 34px;
      }
      > div:first-child {
        width: calc(100% - 34px);
        font-size: 12px;
        line-height: 14px;
      }
    }
    .hm-kbase__nostart-lastadded > div .kbase-card {
      margin-right: 0!important;
    }

    .hm-kbase__nostart-lastadded > span {
      font-size: 14px;
    }
    .hm-kbase__nostart-lastadded > div .kbase-card {
      height: 100px;
    }
    .hm-kbase__nostart-lastadded > div .kbase-card-icon {
      width: 90px;
    }

  }
@media(max-width: 768px) {
  .hm-kbase {
    &__container {
      width: 100%;
      & > div {
        width: 100%;
        margin-right: 0 !important;
      }
      .kbase-card {
        width: 100%;
        margin-top: 0;
        margin-bottom: 16px;
        border: 1px solid #DADADA;

        &-data__title {
          margin-bottom: 0;
        }

        .kbase-card-icon .file-icon__background {
          width: 54px !important;
          height: 64px !important;
        }
        .kbase-card-icon .file-icon__icon {
          width: 22px !important;
          height: 22px !important;
          left: 12px !important;
          bottom: 12px !important;
        }
      }
    }
    &__nostart {
      padding: 16px;
      padding-bottom: 26px;
      &-resource {
        & > div > span {
          font-weight: normal;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
        }
        & > div > div {
          font-size: 30px;
          line-height: 40px;
        }
      }
      &-static {
        margin-top: 36px;
        &__chart-title {
          display: block;
          font-style: normal;
          font-weight: 500;
          font-size: 16px;
          line-height: 19px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
          margin-bottom: 14px;
        }
        &__chart-settings > div {
          max-width: 168px;
          margin-left: auto;

        }
        &__topraiting > span {
          font-weight: 500;
          font-size: 15.5486px;
          line-height: 19px;
          letter-spacing: 0.02em;
        }
        &__topraiting-all > div:first-child {
          font-weight: 300;
        }
        &__topraiting-all > div:last-child {
          font-weight: bold;
          font-size: 16px;
          line-height: 14px;
        }
      }
      &-lastadded {
        margin-top: 36px;
        & > span {
          font-size: 16px;
          line-height: 20px;
          letter-spacing: 0.02em;
          margin-bottom: 16px;
        }
        &-card {
          margin-right: 0 !important;
          width: 100%;
        }
        & > div .kbase-card {
          width: 100%;
          margin-bottom: 16px;
          box-shadow: none;
          border: 1px solid #DADADA;
          & .kbase-card-icon .file-icon__background {
            width: 54px !important;
            height: 64px !important;
          }
          & .kbase-card-icon .file-icon__icon {
            width: 22px !important;
            height: 22px !important;
            left: 12px !important;
            bottom: 12px !important;
          }
          &-data {
            width: calc(100% - 90px);
          }
        }
      }
    }
  }
}
@media(max-width: 440px) {
  .hm-kbase {
    width: calc(100% + 32px);
    margin: 0 -16px;

    &__container {
      padding: 0 16px;
    }
  }
}
</style>
