<template>
  <div class="hm-subjects-search">
    <div class="hm-subjects-search__title">
      <svg-icon name="Search" style="width: 20px; height: 20px" color="#1F8EFA" />
      <span>Поиск</span>
    </div>
    <div class="hm-subjects-search__body">
      <search-subject
        @testing="searchText = $event; search(1500)"
        placeholder="Название, описание"
      />
      <div class="hm-subjects-search_classifiers"
           v-if="Object.keys(classifiers).length > 0"
      >
        <div class="hm-subjects-search_classifier"
             v-for="(checkbox, checkboxValue) in classifiers"
             :key="checkboxValue"
        >
          <div class="hm-subjects-search_classifier__label">
            <svg-icon
                    name="classification"
                    color="#1F8EFA"
                    style="margin-right: 10px; width: 20px"
            >
            </svg-icon>
            <span>{{ checkbox.title }}</span>
          </div>
          <template v-for="item in checkbox.items">
            <v-checkbox
              class="hm-subjects-search_checkbox"
              :key="item.id"
              v-model="checkedClassifiers"
              :value="item.id"
              :label="item.name"
              @change="search"
            >
              <div slot="label" style="width: 100%">
                <v-layout justify-space-between align-center>
                  <div class="hm-subjects-search_checkbox__label">{{ item.name }}</div>
                  <v-tooltip bottom>
                    <v-btn
                      slot="activator"
                      text
                      icon
                      :href="item.url"
                      color="primary"
                      @click.stop>
                      <svg-icon
                              name="openNew"
                              color="#bbb"
                              style="margin-righ: 0px; width: 18px"
                              title="Все курсы рубрики"
                       />
                    </v-btn>
                    <span>Перейти к материалам по теме</span>
                  </v-tooltip>
                </v-layout>
              </div>
            </v-checkbox>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import HmText from "../../../../forms/hm-text/index";
import { mapActions } from "vuex";
import SvgIcon from "@/components/icons/svgIcon";
import SearchSubject from "@/components/els/subject/sidebars/hm-subjects-search/searchSubject";
export default {
  components: {SearchSubject, SvgIcon, HmText },
  props: {
    classifiers: {
      type: Array,
      default: () => []
    },
    searchStart: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      textAttribs: {
        label: "Простой поиск...",
        disabled: false,
        errorsData: [],
        appendIcon: "search"
      },
      classifiersAttribs: {
        MultiOptions: this.classifiers
      },
      checkedClassifiers: [],
      searchText: null,
      start: true
    };
  },
  computed: {
    stateSearchStart() {
      return this.$store.getters['subject/GET_SEARCH_QUERY']
    },
    showItems() {
      return this.$store.state.subject.showItems;
    },
    stateFilters() {
      return this.$store.state.subject.searchFilters;
    },
    searchFilters() {
      return this.$store.state.subject.searchFilters;
    },
    searchFiltersWasChanged() {
      return (
        JSON.stringify(this.stateFilters.classifiers) !==
        JSON.stringify(this.checkedClassifiers) ||
        this.stateFilters.search_query !== this.searchText
      );
    }
  },
  watch: {
    showItems(v) {
      if (!v) {
        this.checkedClassifiers = [];
        this.searchText = null;
      }
    },
  },
  methods: {
    ...mapActions("subject", ["setSearchFilters"]),
    search(timeout = 0) {
      if(this.searchText === null) {
      if(this.stateSearchStart !== '' && this.stateSearchStart) {
        this.searchText = this.stateSearchStart
      }
      }
      if (!this.searchFiltersWasChanged) return;
      setTimeout(() => {
        this.setSearchFilters({
          search_query: this.searchText,
          classifiers: this.checkedClassifiers
        });
        this.start = false
      }, timeout);
    },
    initComp() {
     if(this.searchStart !== '') {
        this.$store.dispatch('subject/addSearchQuery', this.searchStart)
     }
     this.classifiers.forEach(el=> {
            el.items.forEach(ch => {
                if(ch.checked) {
                    this.checkedClassifiers.push(ch.id)
                }
            })
        })
      if(this.checkedClassifiers.length > 0) {
          this.$store.dispatch('subject/addClassifiers', this.checkedClassifiers)
      }
    }
  },
  mounted() {
    this.initComp()
  }
};
</script>
<style lang="scss">
.hm-subjects-search {
  box-shadow: none !important;
  max-height: 100%;
  overflow: scroll;

  &__title {
    width: 100%;
    padding: 16px 16px 0 16px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    > svg {
      margin-right: 12px;
    }
    > span {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
  &__body {
    width: 100%;
    padding: 0 16px 0 16px;
    &-title {
      width: 100%;
      height: 24px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      margin: 25px 0 16px 0;
      > svg {
        margin-right: 13px;
      }
      > span {
        font-weight: 500;
        font-size: 16px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #1E1E1E;
      }
    }
  }
  .hm-subjects-search_classifiers {
    margin-top: 24px;
  }
  .hm-subjects-search_classifier {
    &__label {
      margin-bottom: 13px;
      > span {
        font-weight: normal;
        font-size: 16px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #70889E;
      }
    }
    .v-input--checkbox {
      margin-top: 0;
    }
    .v-input__slot {
      margin-bottom: 0;
    }
  }
  .hm-subjects-search_checkbox {
    &__label {
      font-weight: normal;
      font-size: 12px;
      line-height: 18px;
      letter-spacing: 0.15px;
      color: #3E4E6C;
    }
    .v-input__control {
      width: 100%;
      label {
        width: 100%;
      }
    }
  }
}
</style>
