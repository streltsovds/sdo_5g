<template>
  <div class="hm-subjects-short-info hm-subjects_recently-released mb-4 block-courses"
       v-if="items.length > 0"
  >
    <div v-if="title !== null" class="headline block-courses__title">
      <span>{{ title }}</span>
    </div>
    <div class="block-courses__list">
      <subject-card-course
        v-for="(item, i ) in items"
        :key="`key - ${i}`"
        :data-card="item"
        class="subjectCardCourse_catalog"
      />
    </div>
    <hm-load-more-btn v-if="params.page < itemsPerPage" :in-progress="isLoading" @click="showMore" />
  </div>
  <div class="block-courses__no-result" v-else>
    <!--span>По Вашему запросу ничего не найдено</span-->
  </div>
</template>
<script>
import HmLoadingAlert from "../../../helpers/hm-loading/alert";
import HmDependency from "../../../helpers/hm-dependency/index";
import HmLoadMoreBtn from "../../../helpers/hm-load-more-btn";
import SubjectCardCourse from "@/components/els/subject/cardCourse/subjectCardCourse";
import SvgIcon from "@/components/icons/svgIcon";
export default {
  components: {SvgIcon, SubjectCardCourse, HmLoadingAlert, HmDependency, HmLoadMoreBtn },
  props: {
    url: {
      type: String,
      default: "/subject/search/index/"
    },
    order: {
      type: String,
      default: "last"
    },
    title: {
      type: String,
      default: null
    },
  },
  data() {
    return {
      isLoading: false,
      items: [], //массив результатов
      params: {
        ajax: true,
        page: 1, // актвная страница
      },
      itemsPerPage: null, // всего страниц
    };
  },
  computed: {
    linkShowAll() {
      return `${this.url}?order=${this.order}`;
    },
    retClassifiers() {
      return this.$store.getters['subject/GET_CLASSIFIERS']
    },
    searchQuery() {
      return this.$store.getters['subject/GET_SEARCH_QUERY']
    },
    btnLoadingClasses(){
      let classes = ['hm-btn--loading','hm-subjects__btn-loading'];
      if(this.isLoading){
        classes.push('hm-btn--loading-process');
      }

      const cls = classes.join(' ');
      return cls;
    }
  },
  watch: {
    params(data) {
      console.log(data)
    }
  },
  created() {
    this.init();
  },
  methods: {
    // http://5g.loc/subject/search/index/?ajax=true&page=1&order=title
    // http://5g.loc/subject/search/index/?classifiers[]=669&classifiers[]=670&ajax=true&page=1&itemsPerPage=10&pageCount=2
    // {ajax: true, page: 1, order: "title"}
    init() {
      if (this.isLoading) return;
      this.isLoading = true;
      let classifiers =  {classifiers: this.retClassifiers}
      let searchQuery =  {search_query: this.searchQuery}
      const params = {...searchQuery, ...classifiers, ...this.params, order: this.order };

      this.$axios
        .get(this.url, { params })
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error(
              "Ошибка при загрузке учебных курсов"
            );
          return r.data;
        })
        .then(data => {
          this.itemsPerPage = data.pagination.pageCount
          this.items = data.items
        })
        .catch(e => console.error(e))
        .finally(() => (this.isLoading = false));
    },
    /**
     * метод загрузки следующей страницы
     */
    showMore() {
      if (this.params.page < this.itemsPerPage) {
        this.params.page ++
        const params = { ...this.params, order: this.order};
        this.isLoading = true;
        this.$axios
          .get(this.url, {params})
          .then(res=> {
            if(res.status !== 200 || !res.data || !res.data.items)
              throw new Error ("ошибка при загрузке учебных курсов")
            this.items = [...this.items, ...res.data.items]
          })
          .catch(err => console.log(err))
          .finally(()=> this.isLoading = false)
      }
    }
  }
};
</script>
<style lang="scss">
.block-courses {
  display: flex;
  flex-direction: column;
  &__title {
    padding-left: 3px;
    margin-bottom: 26px;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    > span {
      font-weight: normal;
      font-size: 20px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
  &__list {
    display: flex;
    flex-wrap: wrap;
  }

  .courses-loading {
  }
}
.block-courses__no-result {
  > span {
    font-weight: normal;
    font-size: 24px;
    line-height: 32px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
  }
}
@media(max-width: 1024px) {
  .hm-subjects-short-info {
    .subjectCardCourse_catalog {
      width: calc(50% - 13px);
      max-width: none;
      margin-right: 26px;
      &:nth-child(2n) {
        margin-right: 0;
      }
    }
  }
}
@media(max-width: 860px) {
  .hm-subjects-short-info {
    .subjectCardCourse_catalog {
      padding: 20px 16px;
      padding-bottom: 26px;
      width: calc(50% - 8px);
      max-width: none;
      height: auto;
      margin-right: 16px;
      & .subject-card-course {
        &__image {
          border-radius: 0;
          min-height: 170px;
          &-default {
            height: 170px;
          }
          &-date {
            height: 45px;
            &-icon {
              width: 53px;
            }
            &-dates {
              width: calc(100% - 53px);
              & > div {
                width: calc(50% - 10px);
                margin-left: 10px;
              }
              & > div > span {
                font-size: 13px;
              }
            }
          }
        }
        &__info {
          padding: 20px 0;
          padding-bottom: 12px;
          &-text::before {
            top: -20px;
            left: -16px;
            width: calc(100% + 32px);
            height: calc(100% + 46px);
            border-radius: 0;
          }
          &-wrap {
            min-height: 170px;
          }
          &-name > a > span {
            font-weight: 500;
            font-size: 18px;
            line-height: 22px;
          }
        }
        &__info-button {
          position: static;
          width: 100%;
          height: 36px;
          & span {
            font-size: 16px;
            line-height: 20px;
          }
        }
        &__footer {
          padding: 0;
        }
      }
    }
  }
}
@media(max-width: 700px) {
  .hm-subjects-short-info {
    .block-courses__list {
      flex-wrap: nowrap;
      flex-direction: column;
      align-items: center;
    }
    .subjectCardCourse_catalog {
      width: 100%;
      max-width: 440px;
      margin: 0;
      margin-bottom: 26px;
    }
  }
}
@media(max-width: 440px) {
  .hm-subjects-short-info {
    .subjectCardCourse_catalog {
      margin: 0 -16px;
      margin-bottom: 26px;
      width: calc(100% + 32px);
      &:nth-child(2n) {
        margin-right: -16px;
      }
    }
    & .hm-load-more-btn div {
      width: 100%;
    }
  }
}
</style>
