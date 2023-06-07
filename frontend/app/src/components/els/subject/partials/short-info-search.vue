<template>
  <div
    v-if="dataSearch.length > 0"
    class="hm-subjects-short-info hm-subjects_recently-released mb-4 block-courses"
  >
    <div v-if="title !== null" class="headline block-courses__title">
      <span>{{ title }}</span>
    </div>
    <div class="block-courses__list">
      <subject-card-course
        v-for="(item, i) in dataSearch"
        :key="`key - ${i}`"
        :data-card="item"
      />
    </div>
    <hm-load-more-btn v-if="pageParams.page < pageParams.itemsPerPage" @click="showMore" :in-progress="loading" />
  </div>
</template>
<script>
import HmLoadingAlert from "../../../helpers/hm-loading/alert";
import HmDependency from "../../../helpers/hm-dependency/index";
import SubjectCardCourse from "@/components/els/subject/cardCourse/subjectCardCourse";
import SvgIcon from "@/components/icons/svgIcon";
import HmLoadMoreBtn from "@/components/helpers/hm-load-more-btn";

export default {
  components: { SvgIcon, SubjectCardCourse, HmLoadingAlert, HmDependency, HmLoadMoreBtn },
  props: {
    dataSearch:{
        type: Array,
        default: ()=> []
    },
    title: {
      type: String,
      default: null,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    pageParams: {
      type: Object,
      default: () => {
        return {
            page: 1,
            itemsPerPage: 1
        }
      }
    }
  },
  data() {
    return {
      isLoading: this.loading,
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
    btnLoadingClasses(){
      let classes = ['hm-btn--loading','hm-subjects__btn-loading'];
      if(this.isLoading){
        classes.push('hm-btn--loading-process');
      }

      const cls = classes.join(' ');
      return cls;
    }
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      if (this.isLoading) return;

      // this.isLoading = true;
      const params = { ...this.params, order: this.order };
      this.$axios
        .get(this.url, { params })
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error("Ошибка при загрузке учебных курсов");
          return r.data;
        })
        .then(data => {
          this.itemsPerPage = data.pagination.pageCount;
          this.items = data.items;
        })
        .catch(e => console.error(e))
        // .finally(() => (this.isLoading = false));
    },
      /**
       * метод пагинации
       **/
      paginate(page) {
          this.search.params.page = page;
      },
    /**
     * метод загрузки следующей страницы
     */
    showMore() {
      if(this.pageParams.page < this.pageParams.itemsPerPage) {
        try{
          this.isLoading = true;
          this.$emit('pageNext');
        }finally{
          this.isLoading = false;
        }

      }
    },
  },
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
      color: #1e1e1e;
    }
  }
  &__list {
    display: flex;
    flex-wrap: wrap;
  }

  .courses-loading {
    width: 100%;
    height: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    bottom: 0;
    > div {
      width: 220px;
      height: 100%;
      border: 1px solid #1F8EFA;
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      > svg {
        margin-right: 8px;
      }
      > span {
        font-style: normal;
        font-weight: 500;
        font-size: 14px;
        line-height: 24px;
        letter-spacing: 0.16px;
        color: #1E1E1E;
      }
    }
  }
}
</style>
