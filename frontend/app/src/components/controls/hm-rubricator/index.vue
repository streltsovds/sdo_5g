<template>
  <div class="hm-rubricator">
    <hm-rubricator-menu-wrapper
      :active-object="valueCurrent"
      :auto-wrap="autoWrap"
    >
      <!--      @update:active="updateActive"-->

      <!--      :active="valueCurrent"-->
      <!--      v-model="tree"-->

      <!--
        https://vuetifyjs.com/en/components/treeview/

        activatable - разрешает измененять активный элемент кликом
        active - активные (подсвеченные) элементы. Почему-то должен быть массивом, в данном случае массивом одного элемента.
        items - все возможные элементы (вся структура, по которой можно кликать)
        open-on-click - раскрывает элемент дерева при клике. Делает невозможным работу с activatable, поэтому не используется
        value - это selection галочек, в данном случае галочек нет, он всегда пустой и не используется
      -->
      <v-treeview
        :active="activeArray"
        :open.sync="open"
        :items="items"
        :load-children="fetch"
        @update:active="activeArrayChanged"
        activatable
        item-text="title"
        item-key="keyTreeview"
        loading-icon="cached"
        open-all
        return-object
      >
        <template slot="prepend" slot-scope="{ item, open }">
          <v-icon v-if="item.isFolder" color="orange">
            {{ open ? "folder_open" : "folder" }}
          </v-icon>
          <v-icon v-else color="grey lighten-1">
            label
          </v-icon>
        </template>
        <template slot="append" v-if="isAdmin" slot-scope="{ item, open }">
          <div v-if="item.key > 0">
            <span class="hm-rubricator__button">
              <v-btn class="hm-rubricator__button-edit" icon elevation="0" :href="'/orgstructure/list/edit/org_id/' + item.key">
                <svg-icon
                  class="icon-link"
                  name="Edit"
                  title="Редактировать подразделение"
                  color="#1E1E1E"
                  width="16px"
                />
              </v-btn>
            </span>
            <span class="hm-rubricator__button">
              <v-btn class="hm-rubricator__button-delete" icon elevation="0" @click="deleteDepartment(item.key)">
                <svg-icon
                  class="icon-link"
                  name="Delete"
                  title="Удалить подразделение"
                  color="#EE423D"
                  width="16px"
                />
              </v-btn>
            </span>
          </div>
        </template>
      </v-treeview>
    </hm-rubricator-menu-wrapper>
  </div>
</template>

<script>
import HmRubricatorMenuWrapper from "./_menuWrapper";
import VueMixinStoreGridGenerator from "@/components/hm-grid/mixins/VueMixinStoreGridGenerator";
import SvgIcon from "@/components/icons/svgIcon";

import { mapActions } from "vuex";
import {HM_GRID_MODULE_NAME_PREFIX} from "@/components/hm-grid/module";
import * as gridMutations from "@/components/hm-grid/module/mutations/types";
import * as gridActions from "@/components/hm-grid/module/actions/actions";

export default {
  name: "HmRubricator",
  components: {
    HmRubricatorMenuWrapper,
    SvgIcon
  },
  mixins: [
    VueMixinStoreGridGenerator({
      moduleNameProperty: "storeGridModuleName"
    }),
  ],
  props: {
    /** Выделенный объект */
    value: {
      type: Object,
      default: null,
    },
    /** свёртывание в раскрывающееся поле на мобильных устройствах */
    autoWrap: {
      type: Boolean,
      default: true,
    },
    gridId: {
      type: String,
      default: ""
    },
    gridUrl: {
      type: String,
      default: null
    },
    itemsData: {
      type: Array,
      required: true
    },
    label: {
      type: String,
      default: "Выберите раздел"
    },
    selectionClearable: {
      type: Boolean,
      default: false,
    },
    isAdmin: {
      type: Boolean,
      default: false,
    },
    url: {
      type: String,
      required: true
    },
  },
  data() {
    return {
      /** чтобы не было запроса сразу после загрузки компонента */
      activeChangeIgnoreNext: true,

      elementWithUnknownParent: null,

      /** в data, а не в computed, чтобы при ajax-догрузке элементов в методе `fetch()` не пересчитывался снова */
      indexedItems: [],

      items: [],
      open: [],
      // tree: [],
      /**
       * Для облегчения нахождения пути к элементу, v-treeview не предоставляет его
       * @see https://github.com/vuetifyjs/vuetify/issues/8499#issuecomment-533484045
       **/
      // menu: false,
      grid: {
        url:
          this.gridUrl && this.gridUrl.length > 0
            ? this.gridUrl
            : `${window.location.href}/parent/`
      },
      valueDirty: null,
    };
  },
  computed: {
    activeArray() {
      return this.valueCurrent ? [this.valueCurrent] : [];
    },
    selectedValueTreePath() {
      // return this.valueCurrent.map((activeItem) => {
      //   return this.getActivePath(activeItem) }
      // );
      return this.getItemPath(this.valueCurrent);
    },
    /**
     * TODO
     *   @see frontend/app/src/components/hm-grid/module/index.js, generateModuleName()
     **/
    storeGridModuleName() {
      return HM_GRID_MODULE_NAME_PREFIX + '-' + this.gridId;
    },
    valueCurrent() {
      return this.valueDirty || this.value || {};
    },
  },
  watch: {
    value() {
      this.valueDirty = null;
    },
    valueCurrent(v, oldV) {
      if (this.activeChangeIgnoreNext) {
        this.activeChangeIgnoreNext = false;
      }

      /** TODO более красиво избавиться от рекурсии */
      if (oldV.key === v.key) {
        return
      }
      if (typeof v !== "object" || !v.hasOwnProperty("key")) return;

      let loadUrl = `${this.grid.url}/keyType/${v.keyType}/key/${v.key}/`;
      // this.setGridDataByGridId({ gridId: this.gridId, loadUrl });

      this.$storeGrid_commit(gridMutations.SET_API_URL, loadUrl);
      // this.$storeGrid_dispatch(gridActions.INIT_LOAD_REQUEST);
      this.$storeGrid_dispatch(gridActions.REQUEST_RELOAD);
    },
    selectedValueTreePath(value) {
      this.$emit('update:selected-value-tree-path', value)
    }
  },
  created() {
    this.items = this.formatData(this.itemsData);
    this.indexedItems = this.indexItems(this.items);
    let { value } = this;

    if (
      value &&
      value.key &&
      !this.indexedItems[value.key] &&
      this.items[0] &&
      this.items[0].children
    ) {
      this.elementWithUnknownParent = value;
      let { elementWithUnknownParent } = this;

      console.error('Выделенный элемент ' + elementWithUnknownParent.key + ' не найден в начальных данных!');
      // elementWithUnknownParent.title = '??? ' + elementWithUnknownParent.title;
      // this.items[0].children.push(elementWithUnknownParent)
      // this.$set(this.indexedItems, elementWithUnknownParent.key, elementWithUnknownParent);
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    ...mapActions("grid", ["setGridDataByGridId"]),
    firstObject(array) {
      return array[0] || {};
    },
    /** Ключ из выделенных элементов (из active array) */
    keyFromActiveArray(value) {
      return this.firstObject(value).key;
    },
    // valueKeyChanged(oldValue, newValue) {
    //   return this.keyFromValue(newValue) != this.keyFromValue(oldValue);
    // },
    fetch(item) {
      // для отладки, this становится undefined!!
      let self = this;
      return this.$axios
        .get(this.url, { params: { key: item.key, keyType: item.keyType } })
        .then(response => {
          if (response.status !== 200 || !response.data)
            throw new Error(
              "Wrong response for fetch method in hm-rubricator component"
            );
          return response.data;
        })
        .then(children => {
          if (children.length == 0) {
            // item.isFolder = false;

            /** скрываем иконку разворачивания */
            this.$delete(item, "children");
            item.expand = false;
            return;
          }

          this.$set(item, "children", this.formatData(children, item.key));
          let childrenIndexed = this.indexItems(item["children"])

          /** На случай, когда в начальных данных не оказалось выделенного элемента, а сейчас он пришёл */
          // if (
          //   this.elementWithUnknownParent &&
          //   childrenIndexed[this.elementWithUnknownParent.key]
          // ) {
          //   this.$delete(this.items[0].children, this.elementWithUnknownParent.key)
          // }

          /** для реактивности создаём новый объект */
          this.indexedItems = Object.assign({}, this.indexedItems, childrenIndexed)
        })
        .catch(error => {
          this.addErrorAlert("Произошла ошибка при загрузке данных!");
          console.error(error);
        });
    },
    formatData(items, parentKey = null) {
      let formattedData = [];

      items.forEach(item => {
        if (!Array.isArray(item)) {
          // item is parent
          if(item.keyType) item.keyTreeview = `${item.key}-${item.keyType}`
          else item.keyTreeview = item.key
          let formattedItem = { ...item };

          if (parentKey) {
            formattedItem.parentKey = parentKey
          }

          // TODO переделать на isLazy. +можем ли заранее узнать, есть ли вложенные элементы?
          if (item.isFolder) {
            // if (item.isLazy) {
            formattedItem["children"] = [];
          }
          formattedData.push(formattedItem);

          // if (item.expand) this.open.push(item.key);
          if (item.expand) {
            this.open.push(formattedItem);
          }
        } else if (item.length > 0) {
          // item is children
          let parentItem = formattedData[formattedData.length - 1];
          parentItem["children"] = this.formatData(item, parentItem.key);
        }
      });

      return formattedData;
    },
    indexItems(items) {
      let result = {}

      items.forEach(item => {
        let itemCopy = {...item}
        delete itemCopy.children
        let key = item.key
        result[key] = itemCopy

        if (item.children) {
          let childrenResult = this.indexItems(item.children)
          Object.assign(result, childrenResult)
        }
      });

      return result;
    },
    /**
     * Возвращает путь - цепочку объектов от корня к (выделенному) листу
     *
     * т. к. v-treeview не предоставляет способа получить путь к элементу
     * @see https://github.com/vuetifyjs/vuetify/issues/8499#issuecomment-533484045
     **/
    getItemPath(item) {
      let MAX_LEVEL = 30;

      let result = [];
      let level = 1;

      let nextParentKey = item.key;

      while (nextParentKey) {
        if (level > MAX_LEVEL) {
          throw "max level exceeded";
        }

        let foundItem = this.indexedItems[nextParentKey]

        if (!foundItem) {
          console.error("HmRubricator.getItemPath(): indexed item with key " + nextParentKey + " not found")
          return [];
        }

        nextParentKey = foundItem.parentKey

        /** добавление в начало */
        result.unshift(foundItem)

        level++;
      }
      return result
    },
    activeArrayChanged(newActiveArray) {
      console.log(newActiveArray)
      if (!this.selectionClearable && newActiveArray.length < 1) {
        return ;
      }
      let oldKey = this.keyFromActiveArray(this.activeArray);
      let newKey = this.keyFromActiveArray(newActiveArray);

      if (oldKey === newKey) {
        return
      }
      let newValue = this.firstObject(newActiveArray);

      this.valueDirty = newValue;

      /**
       * TODO подгрузка и развёртывание элемента при клике
       *   мб лучше наоборот задать open-on-click и сделать управление active по событию onOpen через https://stackoverflow.com/a/57416794
       */
      // if (newValue.isLazy) {
      //   this.fetch(newValue);
      // }

      this.$emit('input', newValue)
    },
    // async updateActive(newValue) {
    //   let oldValue = this.valueCurrent;
    //
    //   let newKey = this.keyFromValue(newValue);
    //   let oldKey = this.keyFromValue(oldValue)
    //
    //   if (oldKey !== newKey) {
    //     // TODO более красивый метод избавления от рекурсии
    //     return;
    //   }
    //
    //   this.valueDirty = newValue;
    //
    //
    //   /** запрещение сброса значения */
    //   if (!newKey && !this.selectionClearable) {
    //     await this.$nextTick()
    //     this.valueDirty = oldValue;
    //   } else {
    //     this.$emit('input', newValue)
    //   }
    // }

    deleteDepartment(id) {
      const conf = confirm("При этом будут удалены все вложенные подразделения и должности. Продолжить?");
      if(conf) {
        window.location.href = `/orgstructure/list/delete/soid/${id}`
      }
    }
  },
  mounted() {
    let sStopHere = 1;
  },
};
</script>
<style lang="scss">
.hm-rubricator,
.hm-rubricator__menu {
  .hm-rubricator__menu-text {
    max-width: 500px;
  }
  &__button {
    margin-left: 5px;
    &-edit:hover {
      path {
        fill: #4A90E2;
      }
    }
    &-delete:hover {
      path {
        fill: #9E1D22;
      }
    }
  }
  .v-treeview-node {
    &__root {
    height: auto;
    min-height: 34px;
    align-items: start;
    cursor: pointer;
  }
    &__content {
    align-items: start;
    flex-shrink: 1;
  }
    &__append{
      margin-left: auto;
      span{
        // margin-left: 20px;
      }
    }
    &__prepend {
      i {
        margin-right: 4px;
        position: relative;
        top: -2px;
      }
    }
    &__label {
    // flex-shrink: 1;
    flex: initial;
    }
  }
}
</style>
