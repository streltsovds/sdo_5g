<template>
  <div class="material-search">
    <div class="material-search__fillters">
      <div class="material-search__fillters-form">
        <div>
          <input
            v-model="searchText"
            type="text"
            placeholder="Поиск по названию.."
          >
          <svg-icon name="search"
                    style="width: 17px; height: 17px"
                    stroke-width="3px"
                    color="#757575"
          />
        </div>
      </div>
      <template v-if="type">
        <div class="material-search__fillters-type-materials">
          <v-menu :close-on-content-click="false" offset-y>
            <template v-slot:activator="{ on }">
              <div class="material-search__fillters-type-materials__title button__material-search__all" v-on="on">
                <span>Тип материала</span>
                <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path d="M16.59 8.29688L12 12.8769L7.41 8.29688L6 9.70687L12 15.7069L18 9.70687L16.59 8.29688Z" fill="black" fill-opacity="0.54" />
                </svg>
              </div>
            </template>
            <div class="material-search__fillters-type-materials__body">
              <hm-checkbox
                      v-for="(option, key) in classifiersAndType.types"
                      :key="key"
                      :name="key"
                      :attribs="{ label: option }"
                      :checked="searchActiveEl(key)"
                      @change="updateSearchTypes(key, $event)"
              />
            </div>
          </v-menu>

          <div v-for="(el, i) in classifiersAndType.classifiers" :key="i">
            <v-menu :close-on-content-click="false" bottom offset-y>
              <template v-slot:activator="{ on }">
                <div class="material-search__fillters-type-materials__title button__material-search__all" v-on="on">
                  <span>{{ el.title }}</span>
                  <svg
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                  >
                    <path d="M16.59 8.29688L12 12.8769L7.41 8.29688L6 9.70687L12 15.7069L18 9.70687L16.59 8.29688Z" fill="black" fill-opacity="0.54" />
                  </svg>
                </div>
              </template>
              <div class="material-search__fillters-type-materials__body">
                <hm-checkbox
                        v-for="(option, key) in el.items"
                        :key="key"
                        :name="key"
                        :attribs="{ label: option }"
                        :checked="searchActiveEl(key)"
                        @change="updateSearchClassifiers(key, $event)"
                />
              </div>
            </v-menu>
          </div>
        </div>
      </template>
    </div>
    <div class="material-search__result">
      <div class="material-search__progress">
        <v-progress-circular
          class="material-search_loader"
          v-if="isLoading"
          indeterminate
          color="primary"
        />
      </div>
      <div class="" v-if="!isLoading && Object.keys(results).length > 0">
        <hm-checkboxs @update="updateChecked" :value="checked" :options="results" name="kb_material_id_type[]" />
      </div>
      <div v-else-if="!isLoading">
        <hm-empty empty-type="full" />
      </div>
      <hm-load-more-btn
        v-if="pagination && pagination.pageCurrent < pagination.pageCount"
        :in-progress="actionsDisabled"
        @click="!actionsDisabled ? addLoadMore() : ''"
        :is-disabled="actionsDisabled"
      />
    </div>
  </div>
</template>
<script>
import HmRadio from "../../../forms/hm-radio/index";
import HmCheckbox from "../../../forms/hm-checkbox/index";
import Vue from           "vue";
import SvgIcon from "@/components/icons/svgIcon";
import HmEmpty from "@/components/helpers/hm-empty/index";
import globalActions from "@/store/modules/global/const/actions"
import HmLoadMoreBtn from "../../../helpers/hm-load-more-btn";
import HmCheckboxs from "../../../forms/hm-checkboxs";

export default {
  name: "HmMaterialSearch",
  components: {SvgIcon, HmRadio, HmCheckbox, HmEmpty, HmLoadMoreBtn, HmCheckboxs},
  props: {
    name: {
      type: String,
      required: true
    },
    classifiers: {
      type: Object,
      default: () => {}
    },
    type: {
      type: Object,
      default: () => {}
    },
    searchField: {
      type: Object,
      default: () => {}
    },
    url: {
      type: String,
      required: true
    },
    classifiersAndType: {
      type: Object,
      default :() => {}
    }
  },
  data() {
    return {
      checked: [],
      actionsDisabled: false,
      searchText: "",
      searchTypes: {},
      searchClassifiers: {},
      results: {},
      isLoading: false,
      pagination: {},
      activeEl: {type:false,classifiers:[]} // объект по всем выпадающим спискам
    };
  },
  computed: {
    newResults() {
      return this.results
    },
  },
  watch: {
    searchText() {
      setTimeout(()=> {
        this.search();
      }, 500)
    },
    searchTypes() {
      this.search();
    },
    searchClassifiers() {
      this.search();
    }
  },
  mounted() {
    this.initStyleComp();
    for(let i in this.classifiersAndType.classifiers) {
      this.activeEl.classifiers.push({type:false, name:this.classifiersAndType.classifiers[i].title})
    }
    this.search();
  },
  methods: {
    updateChecked(value) {
      this.checked = value
    },
    initStyleComp() {
      document.getElementById('fieldset-kbasetab').style.boxShadow = 'none!important;';
    },
    updateSearchText(value) {
      if (value && value.length > 1) this.searchText = value;
    },
    updateSearchTypes(value, checked) {
      if (checked) {
        Vue.set(this.searchTypes, 'key' + value, value);
      } else {
        Vue.delete(this.searchTypes, 'key' + value);
      }
    },
    updateSearchClassifiers(value, checked) {
      if (checked) {
        Vue.set(this.searchClassifiers, 'key' + value, value);
      } else {
        Vue.delete(this.searchClassifiers, 'key' + value);
      }
    },
    search() {
      this.$store.dispatch(globalActions.setLoadingOn, 'materialSearch', { root: true });
      this.isLoading = true;
      let formData = new FormData();
      if(!/\S/.test(this.searchText)) {
        this.searchText = ''
      }
      formData.append("search_query", this.searchText);
      for(let [typeKey, typeValue] of Object.entries(this.searchClassifiers)) {
        formData.append("classifiers[]", typeValue);
      };

      for (let [typeKey, typeValue] of Object.entries(this.searchTypes)) {
        formData.append("types[]", typeValue);
      }
      this.$axios
        .post(this.url, formData)
        .then(r => {
          this.$store.dispatch(globalActions.setLoadingOff, 'materialSearch', { root: true });
          if (r.status === 200 && r.data) {
            this.results = {};
            this.pagination = r.data.pagination;
            r.data.items.forEach(item => {
              let typeEl = '';
              if (item.id && item.title && item.kbase_type) {
                let idTypeKey = item.id + "-" + item.kbase_type;
                this.results[idTypeKey] = {};
                this.results[idTypeKey].title = item.title;
                this.results[idTypeKey].viewUrl = item.viewUrl ? item.viewUrl : false;

                if(item.kbase_type && item.kbase_type === 'resource') {
                  if(item.type && item.type === 'external') {
                    this.results[idTypeKey].type = item.filetype
                  } else {
                    this.results[idTypeKey].type = item.type
                  }
                } else {
                  this.results[idTypeKey].type = item.kbase_type
                }
              }
            });
          }
        })
        .catch(e => console.error(e))
        .finally(() => (this.isLoading = false));
    },
    // метод догрузки еще вариантов
    addLoadMore() {
      this.$store.dispatch(globalActions.setLoadingOn, 'materialSearch', { root: true });
      this.actionsDisabled = true;

      if(this.pagination.pageCurrent < this.pagination.pageCount) {
        this.pagination.pageCurrent++;
        let formData = new FormData();
        formData.append("search_query", this.searchText);

        for (let searchClassifier in this.searchClassifiers) {
          formData.append("classifiers[]", searchClassifier);
        }
        for (let [typeKey, typeValue] of Object.entries(this.searchTypes)) {
          formData.append("types[]", typeValue);
        }
        formData.append("page", this.pagination.pageCurrent);
        this.$axios
          .post(this.url, formData)
          .then(r => {
            this.$store.dispatch(globalActions.setLoadingOff, 'materialSearch', { root: true });
            this.actionsDisabled = false;
            this.pagination = r.data.pagination;
            r.data.items.forEach(item => {
              if (item.id && item.title && item.kbase_type) {
                let idTypeKey = item.id + "-" + item.kbase_type;
                let obj = {type:item.kbase_type,title:item.title };
                Vue.set(this.results, idTypeKey, obj )
              }
            });
          })
          .catch(e => console.error(e))
          .finally(() => (this.isLoading = false));
      }
    },
    activeClassifiers(type, name ='') {
      if(name === '') {
        this.activeEl[type] = !this.activeEl[type];
        this.activeEl['classifiers'].map(el => el.type = false)
      } else {
        this.activeEl['type'] = false;
        this.activeEl['classifiers'].map(el => {
          el.name === name ? el.type = !el.type : el.type = false
        })
      }
    },
    // поиск уже активных элементов фильтра
    searchActiveEl(i) {
      let status = false;
      for(let el in this.searchTypes) {
        if (this.searchTypes[el] === i) {
          status = true;
          break;
        }
      }
      return status
    }
  }
};
</script>
<style lang="scss">
  .material-search {
    display: flex;
    flex-flow: row-reverse;

    &__progress {
      margin: 20px 0;
      text-align: center;
    }
    &__result {
      width: 67%;
      > div {
        .hm-radio {
          margin-top: 0!important;
          > div {
            .v-input__control {
              .v-input__slot {
                > div {
                  .v-radio {
                    margin-bottom: 17px !important;
                    > label {
                      > div {
                        margin-right: 8px;
                        margin-left: 6px;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    &__fillters {
      width: 33%;
      padding: 0 52px;
      border-left: 1px solid #e0e0e0;
      min-height: calc(400px - 48px);
      &-title {
        margin-bottom: 30px;
        > span {
          font-weight: 500;
          font-size: 20px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
      }
      &-form {
        display: flex;
        margin: 32px 0 52px 0;
        width: 100%;
        > div {
          display: flex;
          justify-content: center;
          align-items: center;
          position: relative;
          width: 100%;
          > input {
            width: 100%;
            background: #FFFFFF;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            padding: 12px 40px 12px 16px;
            font-weight: normal;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            font-style: normal;
            color: #70889e;
            &:hover, &:active, &:focus {
              outline: none;
              border: none;
            }
            &::placeholder {
              color: #70889e;
              font-size: 16px;
            }
          }
          > svg {
            position: absolute;
            right: 16px;
          }
        }
      }

      .button__material-search__all {
        border: 1px solid #C4C4C4;
        box-sizing: border-box;
        border-radius: 4px;
        padding: 11px 16px 9px;
        position: relative;
        > svg {
          position: absolute;
          right: 16px;
          transition: .2s ease-in-out;
        }
        > span {
          font-weight: normal;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
      }


      &-type-materials {
        &__title {
          width: 100%;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          margin-bottom: 26px;
          cursor: pointer;
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }
        .hm-form-element.hm-checkbox {
          margin: 0 0 10px 0 !important;
          > div {
            margin: 0 !important;
            padding: 0 !important;
            > div {
              > div {
                margin: 0!important;
                padding: 0!important;
              }
            }
          }
        }
        &__body {
          overflow: hidden;
          background: #ffffff;
          > .hm-form-element {
            margin-top: 0!important;
            &:hover {
              background: rgba(212, 227, 251, 0.31);
            }
            > div {
              width: 100%;
              height: 47px;
              padding-top: 0!important;
              margin: 0!important;
              > div {
                width: 100%;
                height: 100%;
                padding-top: 0!important;
                margin: 0!important;
                > div {
                  width: 100%;
                  height: 100%;
                  padding: 0 26px!important;
                  margin: 0!important;
                  > div {
                    margin-right: 19px;
                  }
                }
              }
            }
          }
        }
      }
    }

    .search-classifiers-enter-active {
      transition: all .3s ease ;
    }
    .search-classifiers-leave-active {
      transition: all .3s ease-in-out;
    }

    .search-classifiers-enter, .search-classifiers-leave-to {
      opacity: 0;
    }


  }
  .v-menu__content {
    margin-top: 5px !important;
    .v-list {
      min-width: auto;
    }
  }

  /* Перезапись стилей  */
  .hm-tabs {
    min-height: 400px;
    background: #FFFFFF;
    box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
    border-radius: 4px;
  }


  .v-window {
    min-height: calc(400px - 48px);
    &__container {
      .v-window-item {
        > span {
          .flex {

            #fieldset-kbasetab.v-card {
              box-shadow: none !important;
            }
          }
        }
      }
    }
  }

  .hm-disabled-actions {
    position: relative;
    &:after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 5000000;
      background: rgba(128, 128, 128, 0.16);
      cursor: default;
    }
  }
</style>
