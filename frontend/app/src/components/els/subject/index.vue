<template>
  <div class="hm-subject">
    <div v-show="!searchSubjects.showItems" key="hm-subjects_additional">
      <hm-subjects-short-info
        order="title"
      ></hm-subjects-short-info>
    </div>
    <div v-show="searchSubjects.showItems">
      <short-info-search
        :dataSearch="search.items"
        :page-params="{page:search.params.page, itemsPerPage:search.params.pageCount}"
        @pageNext="pageNextParent"
        :loading="search.isLoading"
      />
    </div>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import HmSubjectsShortInfo from "./partials/short-info";
import GlobalActions from "../../../store/modules/global/const/actions";
import ShortInfoSearch from "@/components/els/subject/partials/short-info-search";

export default {
  name: "HmSubject",
  components: {
    ShortInfoSearch,
    HmSubjectsShortInfo,
  },
  props: {},
  data() {
    return {
      url: "/subject/search/index/",
      search: {
        isLoading: false,
        items: [],
        params: {
          ajax: true,
          page: 1,
          itemsPerPage: 10,
          pageCount: 1,
        },
      },
      cancelToken: this.$axios.CancelToken,
      axiosSource: null,
      pageback: false // переменная хрнит в себе предыдущию страницу
    };
  },
  computed: {

    searchSubjects() {
      return this.$store.state.subject;
    },
    searchFilters() {
      return this.$store.state.subject.searchFilters;
    },
    currentSearchPage() {
      return this.search.params.page;
    },
  },
  watch: {
    searchFilters() {
      this.pageback = false
      this.search.params.page = 1
      this.makeSearch();
    },
    currentSearchPage() {
      this.makeSearch();
    },
  },
  methods: {
    ...mapActions("subjects", ["resetSearch"]),
    ...mapActions([GlobalActions.setLoadingOn, GlobalActions.setLoadingOff]),

    makeSearch() {
      if (this.axiosSource)
        this.axiosSource.cancel("Subjects request canceled");

      this.search.isLoading = true;
      this[GlobalActions.setLoadingOn](this.$options.name);
      this.axiosSource = this.cancelToken.source();

      let testPage = {
          ajax: this.search.params.ajax,
          page:this.search.params.page
      }
      const params = { ...this.searchFilters, ...testPage };
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

          this.pageback ? this.search.items = this.search.items.concat(data.items) : this.search.items = data.items
          this.search.params.page = data.pagination.pageCurrent;
          this.search.params.pageCount = data.pagination.pageCount;
          this[GlobalActions.setLoadingOff](this.$options.name);
        })
        .catch(e => {
          if (this.$axios.isCancel(e)) {
            console.log("Request canceled", e.message);
          } else {
            this[GlobalActions.setLoadingOff](this.$options.name);
            console.error(e.message);
          }
        })
        .finally(() => (this.search.isLoading = false));
    },
    resetSearchData() {
      this.resetSearch();
      this.search.params.page = 1;
      this.search.params.pageCount = 1;
    },
    // отображение слейдущих данных
    pageNextParent() {
        this.pageback = true;
        this.search.params.page++
    }
  },
};
</script>
<style lang="scss">
.hm-subject {
  min-height: 100%;
  /*background: #ffffff;*/
  /*box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);*/
  /*border-radius: 4px;*/
  /*padding: 26px 27px 26px 25px;*/
}

.hm-subjects_search-pagination {
  margin: auto;
}
</style>
