<template>
  <v-card class="hm-kbase-search">
    <div class="hm-kbase-search__title">
      <svg-icon name="Search" style="width: 20px; height: 20px" color="#1F8EFA" />
      <span>Поиск</span>
    </div>
    <div class="hm-kbase-search__body">

      <div id="searchKbase">
        <input
          @input="
            searchText = $event.target.value
            search(1500);
          "
          name="search_query"
          type="text"
          id="searchInput"
          placeholder="Название, содержимое" />
        <div class="icon-search">
          <svg-icon name="Search" stroke-width="3px" style="width: 16px; height: 16px" color="#DADADA" />
        </div>
      </div>

      <div
        v-if="Object.keys(classifiers).length > 0"
        class="hm-kbase-search_classifiers"
      >
        <div
          v-for="(checkbox, checkboxValue) in classifiers"
          :key="checkboxValue"
          class="hm-kbase-search_classifier"
        >
          <div class="hm-kbase-search_classifier__label">
            <svg-icon
                    name="classification"
                    color="#1F8EFA"
                    style="margin-right: 10px; width: 20px"
            >
            </svg-icon>
            <span>{{ checkbox.title }}</span>
          </div>
          <template v-for="item in checkbox.items">
            <v-checkbox class="hm-kbase-search_checkbox"
                        :key="item.id"
                        v-model="checkedClassifiers"
                        :value="item.id"
                        :label="item.name"
                        @change="search">
              <div slot="label" style="width: 100%">
                <v-layout justify-space-between align-center>
                  <div class="hm-kbase-search_checkbox__label">{{ item.name }}</div>
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
                              title="Все ресурсы рубрики"
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
  </v-card>
</template>
<script>

import HmText from "../../../../forms/hm-text/index";
import { mapActions } from "vuex";
  import SvgIcon from "@/components/icons/svgIcon";

export default {
  components: { SvgIcon, HmText },
  props: {
    classifiers: {
      type: Array,
      default: () => []
    },

  },
  data() {
    return {
      textAttribs: {
        label: "Поиск по содержимому",
        disabled: false,
        errorsData: [],
        appendIcon: "search"
      },
      classifiersAttribs: {
        MultiOptions: this.classifiers
      },
      checkedClassifiers: [],
      searchText: null,
    };
  },
  computed: {
    showItems() {
      return this.$store.state.kbase.showItems;
    },
    stateFilters() {
      return this.$store.state.kbase.searchFilters;
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
    }
  },
  methods: {
    ...mapActions("kbase", ["setSearchFilters"]),
    search(timeout = 0) {
      if (!this.searchFiltersWasChanged) return;

      setTimeout(() => {
        this.setSearchFilters({
          search_query: this.searchText,
          classifiers: this.checkedClassifiers
        });
      }, timeout);
    }
  }
};
</script>
<style lang="scss">
.hm-kbase-search {
  box-shadow: none !important;
  overflow: scroll;
  height: 100%;
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
  .hm-kbase-search_classifiers {
    margin-top: 24px;
  }
  .hm-kbase-search_classifier {
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
  .hm-kbase-search_checkbox {
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
#searchKbase {
  width: 100%;
  height: 48px;
  margin: 17px 0 15px 0;
  position: relative;
  > input {
    width: 100%;
    height: 100%;
    background: #FFFFFF;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    padding: 16px 46px 13px 16px;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #70889E;
    &:active {
      border: none;
      outline: none;
    }
    &:focus {
      border: none;
      outline: none;
    }
    &::placeholder {
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #70889E;
    }

  }
  .icon-search {
    position: absolute;
    right: 16px;
    top: 12px;
    cursor: pointer;
  }
}
</style>
