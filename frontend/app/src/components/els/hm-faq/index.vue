<template>
  <v-layout class="hm-faq">
    <v-list v-if="items.length">
      <div v-for="faq in items">
        <div class="hm-faq__list-item">
          <div class="hm-faq__question headline">
              {{faq.question}}
            </div>
            <div  class="hm-faq__answer">
              {{faq.answer}}
            </div>
        </div>
        <v-divider></v-divider>
      </div>
    </v-list>
    <hm-grid-no-data v-else slot="no-data" subLabel=""/>

    <!-- @todo: вызвать hm-pagination, сейчас ругается на регистрацию компонента, куда бы я его не прописал -->
    <div v-if="pagesCount > 1">
      <v-pagination
          class="hm-faq__pagination"
          :length="pagesCount"
          v-model="currentPage"
          total-visible="8"
      ></v-pagination>
    </div>
  </v-layout>
</template>

<script>

import HmGridNoData from "@/components/hm-grid/components/HmGridNoData";
export default {
  name: "HmFaq",
  components: {HmGridNoData},
  props: {
    items: {
      type: Array,
      required: true
    },
    pageNumber: {
      type: Number,
      required: true
    },
    pages: {
      type: Number,
      required: true
    },
    url: {
      type: String,
      required: true
    },
  },
  data() {
    return {
      currentPage: this.pageNumber,
      pagesCount: this.pages,
    }
  },
  watch: {
    currentPage() {
      this.getItems();
    },
  },
  mounted() {

  },
  methods: {
    getItems() {
      const params = {page: this.currentPage};
      this.$axios
        .get(this.url, {params})
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error(
              "Ошибка при загрузке"
            );

          return r.data;
        })
        .then(faq => {
          this.items = faq.items;
          this.currentPage = faq.currentPage;
          this.pagesCount = faq.pageCount;
        });
    },
  },
};
</script>

<style lang="scss">
.hm-faq {
  display: flex;
  flex-direction: column;
  margin: 0 !important;
  &__pagination {
    margin-top: 25px;
  }
  &__list-item {
    padding: 25px;
  }
  &__question {
    margin-bottom: 20px;
  }
  &__answer {

  }
}
</style>
